#! /bin/bash #employ bash shell

#kill redis
ps -ef |grep redis |awk '{print $1}'|xargs kill -9

#kill server
ps -ef |grep server |awk '{print $1}'|xargs kill -9

#kill ws_server
ps -ef |grep ws_server |awk '{print $1}'|xargs kill -9

#start redis-server
redis-server /etc/redis.conf &
echo "start redis success!"

#start server 
nohup php server.php >> server.log &
echo "start redis server.php success!"

#start ws_server 
nohup php ws_server.php >> ws_server.log &
echo "start redis ws_server.php success!"


echo "success !!!!!!!!!!!!!!!!!!!!!!!!!";


