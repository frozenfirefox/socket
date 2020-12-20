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
 
#socket 

````
    HOST:47.94.167.205
    PORT:9508
````

#api
````
    /**
     * @1001
     * socket login
     * @params {"service":"socket_login","user_id":1001,"phone" :"13312062676"}
     * 连接socket接口
     */
    public const SOCKET_LOGIN = 'socket_login';

    /**
     * @1002
     * @health`
     * @params {"service":"socket_health","user_id":1001}
     * socket service
     */
    public const SOCKET_HEALTH = 'socket_health';

    /**
     * @1003
     * @回传话单（工号、通话id，客户id，被叫号码，通话结果、通话时长）
     * @params {"service":"socket_upload","user_id":1001,"record":{"user_id":1001,"call_id":232,"consumer_id":555,"call_phone":159232424524,"time":232323,"record":{"user_id":1001,"call_id":232,"file":"sdasdsadasdsa"}}}
     * socket_upload
     */
    public const SOCKET_UPLOAD = 'socket_upload';
  
    /**
     * @3003
     * 主动模拟
     * @params {"service":"socket_call","user_id":1001, "call_id": "234232", consumer_id":2323,"call_phone":13312062424,"domain":"https:\/\/www.baidu.com"}
     * @呼叫 （工号 通话id 客户端id 被叫号码）
     */
    public const SOCKET_CALL = 'socket_call';

    /**
     * @2003
     * 后台websocket请求
     * @params {"service":"socket_request","user_id":1001,"consumer_id":2323,"call_phone":13312062424,"domain":"https:\/\/www.baidu.com"}
     * @系统客户端向服务端发送消息
     */
    public const SOCKET_REQUEST = 'socket_request';
 ````
   
#web-socket 

````
    HOST:47.94.167.205
    PORT:9509
````

#api
````
    /**
     * @1001
     * socket login
     * @params {"service":"socket_login","user_id":1001,"phone" :"13312062676"}
     * 连接socket接口
     */
    public const SOCKET_LOGIN = 'socket_login';

    /**
     * @1002
     * @health`
     * @params {"service":"socket_health","user_id":1001}
     * socket service
     */
    public const SOCKET_HEALTH = 'socket_health';

    /**
     * @2003
     * 后台websocket请求
     * @params {"service":"socket_request","user_id":1001,"consumer_id":2323,"call_phone":13312062424,"domain":"https:\/\/www.baidu.com"}
     * @系统客户端向服务端发送消息
     */
    public const SOCKET_REQUEST = 'socket_request';
```