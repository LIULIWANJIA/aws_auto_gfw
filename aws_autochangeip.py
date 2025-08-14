import os
import json
import time
import socket
import concurrent.futures
import logging
import boto3
from botocore.exceptions import ClientError

# 配置日志目录
LOG_DIR = "/www/wwwroot/aws_auto/Logs"
os.makedirs(LOG_DIR, exist_ok=True)

# 环境变量默认值
DEFAULT_REGION = "ap-northeast-1"
DEFAULT_INSTANCE_TYPE = "lightsail"

def setup_logger(instance_id):
    """为每个实例创建中文日志记录器"""
    logger = logging.getLogger(f"instance_{instance_id}")
    logger.setLevel(logging.INFO)
    
    # 创建文件处理器（UTF-8编码支持中文）
    log_file = os.path.join(LOG_DIR, f"{instance_id}.log")
    file_handler = logging.FileHandler(log_file, encoding='utf-8')
    file_formatter = logging.Formatter('%(asctime)s - %(message)s')
    file_handler.setFormatter(file_formatter)
    
    # 创建控制台处理器（简洁格式）
    console_handler = logging.StreamHandler()
    console_formatter = logging.Formatter('%(message)s')
    console_handler.setFormatter(console_formatter)
    
    logger.addHandler(file_handler)
    logger.addHandler(console_handler)
    return logger

def check_port(ip, port=443):
    """检查指定IP的端口连通性"""
    for i in range(3):  # 单次检测3次
        try:
            with socket.create_connection((ip, port), timeout=3):
                return True
        except (socket.timeout, ConnectionRefusedError):
            if i < 2:  # 不是最后一次尝试则等待
                time.sleep(2)
    return False

def change_ip(instance_config):
    """更换实例的IP地址"""
    # 从配置中获取参数
    instance_id = instance_config["INSTANCE_ID"]
    instance_type = instance_config.get("INSTANCE_TYPE", DEFAULT_INSTANCE_TYPE)
    region = instance_config.get("AWS_DEFAULT_REGION", DEFAULT_REGION)
    access_key = instance_config["AWS_ACCESS_KEY_ID"]
    secret_key = instance_config["AWS_SECRET_ACCESS_KEY"]
    
    # 设置日志记录器
    logger = setup_logger(instance_id)
    
    try:
        # 获取客户端和当前IP
        if instance_type == "lightsail":
            client = boto3.client("lightsail", region_name=region,
                                  aws_access_key_id=access_key,
                                  aws_secret_access_key=secret_key)
            instance_info = client.get_instance(instanceName=instance_id)
            public_ip = instance_info["instance"]["publicIpAddress"]
        else:  # EC2
            ec2 = boto3.client("ec2", region_name=region,
                               aws_access_key_id=access_key,
                               aws_secret_access_key=secret_key)
            response = ec2.describe_instances(InstanceIds=[instance_id])
            public_ip = response["Reservations"][0]["Instances"][0]["PublicIpAddress"]
        
        # 主检测循环
        for attempt in range(6):
            if check_port(public_ip):
                print(f"[{instance_id}] 端口443正常，无需操作")
                return
            
            logger.info(f"[{instance_id}] 端口443不可达 (第{attempt+1}/6次检测)")
            if attempt < 5:  # 不是最后一次检测则等待
                time.sleep(10)
        
        # 所有检测失败，执行IP更换
        logger.info(f"[{instance_id}] 开始更换IP... (原IP: {public_ip})")
        
        if instance_type == "lightsail":
            # Lightsail: 停止并启动实例
            client.stop_instance(instanceName=instance_id)
            logger.info(f"[{instance_id}] 实例停止中...")
            while True:
                status = client.get_instance(instanceName=instance_id)["instance"]["state"]["name"]
                if status == "stopped":
                    break
                time.sleep(5)
            
            client.start_instance(instanceName=instance_id)
            logger.info(f"[{instance_id}] 实例启动中...")
            while True:
                status = client.get_instance(instanceName=instance_id)["instance"]["state"]["name"]
                if status == "running":
                    break
                time.sleep(5)
            
            new_ip = client.get_instance(instanceName=instance_id)["instance"]["publicIpAddress"]
        else:
            # EC2: 停止并启动实例
            ec2.stop_instances(InstanceIds=[instance_id])
            logger.info(f"[{instance_id}] 实例停止中...")
            waiter = ec2.get_waiter("instance_stopped")
            waiter.wait(InstanceIds=[instance_id])
            
            ec2.start_instances(InstanceIds=[instance_id])
            logger.info(f"[{instance_id}] 实例启动中...")
            waiter = ec2.get_waiter("instance_running")
            waiter.wait(InstanceIds=[instance_id])
            
            response = ec2.describe_instances(InstanceIds=[instance_id])
            new_ip = response["Reservations"][0]["Instances"][0]["PublicIpAddress"]
        
        logger.info(f"[{instance_id}] IP更换成功! 原IP: {public_ip}, 新IP: {new_ip}")
        print(f"[{instance_id}] IP已更新: {new_ip}")
    
    except ClientError as e:
        error_msg = e.response['Error']['Message']
        logger.error(f"[{instance_id}] AWS API错误: {error_msg}")
        print(f"[{instance_id}] 操作失败: {error_msg}")
    except Exception as e:
        logger.error(f"[{instance_id}] 意外错误: {str(e)}")
        print(f"[{instance_id}] 发生错误: {str(e)}")

def main():
    """主函数：读取配置并处理所有实例"""
    try:
        with open("/www/wwwroot/aws_auto/instances.json", "r", encoding='utf-8') as f:
            instances_config = json.load(f)
    except FileNotFoundError:
        print("错误: 找不到instances.json文件")
        return
    except json.JSONDecodeError:
        print("错误: instances.json格式无效")
        return
    
    print(f"开始检测{len(instances_config)}个实例...")
    # 使用线程池异步处理所有实例
    with concurrent.futures.ThreadPoolExecutor() as executor:
        futures = [executor.submit(change_ip, config) for config in instances_config]
        for future in concurrent.futures.as_completed(futures):
            future.result()  # 获取结果以处理异常
    print("所有实例检测完成")

if __name__ == "__main__":
    main()