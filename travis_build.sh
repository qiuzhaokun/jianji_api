#!/bin/bash
cd /webroot/api.jianji.myexist.cn/
git pull origin master
chown -R nginx:nginx /webroot/api.jianji.myexist.cn/
chmod -R 755 /webroot/api.jianji.myexist.cn/
mv /webroot/api.jianji.myexist.cn/.env_prod /webroot/api.jianji.myexist.cn/.env
rm -rf /webroot/api.jianji.myexist.cn/config
cp /webroot/config/jianji_config /webroot/api.jianji.myexist.cn/config
echo 'travis build done!'