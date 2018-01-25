#!/bin/bash
cd /webroot/api.jianji.myexist.cn/
git pull origin master
chown -R nginx:nginx /webroot/api.jianji.myexist.cn/
chmod -R 755 /webroot/api.jianji.myexist.cn/
echo 'travis build done!'