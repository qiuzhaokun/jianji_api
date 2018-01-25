#!/bin/bash
cd /webroot/api.jianji.myexist.cn/
git pull origin master
chown -R nginx:nginx /webroot/api.jianji.myexist.cn/
chmod -R 755 /webroot/api.jianji.myexist.cn/
mv /webroot/api.jianji.myexist.cn/.env_prod /webroot/api.jianji.myexist.cn/.env
mv /webroot/api.jianji.myexist.cn/config_prd /webroot/api.jianji.myexist.cn/config
echo 'travis build done!'