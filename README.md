# 前端演示(前端仅用于编辑、新增、删除 实例配置文件)

<img width="2523" height="1244" alt="image" src="https://github.com/user-attachments/assets/a438417d-8cf1-4f74-a51a-693d09eba866" />

<img width="1736" height="521" alt="image" src="https://github.com/user-attachments/assets/2ebc0f18-843c-4338-ae5c-ae37986eb349" />
# 后端手动执行演示

<img width="843" height="137" alt="image" src="https://github.com/user-attachments/assets/c86e5015-5eb6-46df-9944-38a335cf9a2e" />


# 环境准备
python3
python3-pip
安装方式 apt install python3-pip 或者是 yum install python3-pip(请注意你的源是否有这个包)

pip依赖 boto3 
安装方式 pip install boto3



## 教程一
部署到宝塔面板 具有网页编辑AWS实例的功能
下载前端文件和后端Python脚本
https://github.com/LIULIWANJIA/aws_auto_gfw/releases/download/v1.0/aws_auto.zip

解压上传到网站根目录 请注意 网站目录为/www/wwwroot/aws_auto
不要建错目录
网站结构为/www/wwwroot/aws_auto/index.php

登录密码保存在config.php中
默认123 自行修改

# 伪静态
```bash
location ~* /(config\.php|instances\.json) {
    deny all;
    return 403;
}

location ~* /(save_config\.php|get_log\.php) {
    try_files $uri =404;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

此时已经可以在浏览器中访问网站了

请先进行一个测试实例的添加
ec2实例的名为i-xxxxxxxxxx,lightsail的实例名为实例名

添加完成后
进入SSH命令行
```bash
赋予后端检测脚本777可执行root权限
chmod 777 /www/wwwroot/aws_auto/aws_autochangeip.py
测试检测功能
/usr/bin/python3 /www/wwwroot/aws_auto/aws_autochangeip.py
```

如无问题会提示
开始检测x个实例...
[xxxx] 端口443正常，无需操作
[xxxx] 端口443正常，无需操作
[xxxx] 端口443正常，无需操作
所有实例检测完成

关于检测的目标端口
```bash
文件 /www/wwwroot/aws_auto/aws_autochangeip.py 第38行 port=443
```
可自行修改 (修改后控制台输出不会自动改变 因为没用变量 懒得改了 我自己就用443)
你要是没有443 可以装个web服务的嘛 装docker web ,looking glass之类的，映射到443也行
把443搞通就行


最后一步 配置定时任务
清除之前可能安装过的旧任务
```bash
sed -i '/aws_autochangeip/d' /etc/crontab
```

配置30分钟一次的定时任务
(请注意 30分钟已经很短了  不知道咋回事，检测的频繁了
怎么换IP他都是不通的 但是放个十几分钟他又自己通了 AWS有自己的规则)

```bash
echo "*/30 * * * * root /usr/bin/python3 /www/wwwroot/aws_auto/aws_autochangeip.py" >>/etc/crontab
```

重启定时任务服务

```bash
service crond restart && service cron restart
```


运行日志可以在前端查看 没有日志就是还没到运行时间或者是还没有换IP的记录
自动运行时间为准点和每个30分钟，也就是一小时两次

卸载方式
清除定时任务
```bash
sed -i '/aws_autochangeip/d' /etc/crontab
```
重启定时任务服务
```bash
service crond restart && service cron restart
```

删除网站目录

结束 这就删掉了，没啥东西，一个小玩意


## 教程二

直接部署 不使用前端
#清除之前可能安装过的残留
```bash
rm -rf /root/aws_auto
sed -i '/aws_autochangeip/d' /etc/crontab
service crond restart && service cron restart
```

进行文件夹创建 + 文件下载(下载不下来，自己去对着文件名从github上下)

```bash
mkdir /root/aws_auto && mkdir /root/aws_auto/Logs
wget -O /root/aws_auto/aws_autochangeip.py https://github.com/LIULIWANJIA/aws_auto_gfw/blob/main/aws_autochangeip.py
wget -O /root/aws_auto/instances.json https://github.com/LIULIWANJIA/aws_auto_gfw/blob/main/instances.json
```

权限赋予

```bash
chmod 777 /root/aws_auto/aws_autochangeip.py
```

下载完成后 编辑文件 instances.json
格式为
```bash
[
    {
        "AWS_ACCESS_KEY_ID": "AKIAXXXXXXXXXXXXXXXx",
        "AWS_SECRET_ACCESS_KEY": "xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "AWS_DEFAULT_REGION": "ap-northeast-1",
        "INSTANCE_ID": "Ubuntu-1",
        "INSTANCE_TYPE": "lightsail"
    },
    {
        "AWS_ACCESS_KEY_ID": "AKIAXXXXXXXXXXXXXXXx",
        "AWS_SECRET_ACCESS_KEY": "xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "AWS_DEFAULT_REGION": "ap-northeast-1",
        "INSTANCE_ID": "Ubuntu-2",
        "INSTANCE_TYPE": "lightsail"
    },
    {
        "AWS_ACCESS_KEY_ID": "AKIAXXXXXXXXXXXXXXXx",
        "AWS_SECRET_ACCESS_KEY": "xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "AWS_DEFAULT_REGION": "ap-northeast-1",
        "INSTANCE_ID": "i-068adfecxxxx8c3eexxxc1be",
        "INSTANCE_TYPE": "ec2"
    }
]
```

测试执行
```bash
/usr/bin/python3 /root/aws_auto/aws_autochangeip.py
```

配置定时任务
```bash
echo "*/30 * * * * root /usr/bin/python3 /root/aws_auto/aws_autochangeip.py" >>/etc/crontab
service crond restart && service cron restart
```

卸载方式
```bash
rm -rf /root/aws_auto
sed -i '/aws_autochangeip/d' /etc/crontab
service crond restart && service cron restart
```

结束









