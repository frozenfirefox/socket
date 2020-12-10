# socket
socket  ip:9508
websocket ip:9509

# description 
看样例
 - ws_client.js websocket的js的client代码
 - ws_client_php.php socket的php的client代码

#修改服务端需要重新进入docker
 - docker exec -it swoole sh

#服务端启动方式

 - ps -ef 找到php ws_server.php 删除掉 
 - nohup php ws_server.php 1>>ws_server.log 
 -------------------------
 - ps -ef 找到php server.php 删除掉 
 - nohup php server.php 1>>socket.log



