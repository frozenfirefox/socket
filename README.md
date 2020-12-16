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



#api
````
    /**
     * @1001
     * socket login
     * 连接socket接口
     */
    public const SOCKET_LOGIN = 'socket_login';

    /**
     * @1002
     * @health
     * socket service
     */
    public const SOCKET_HEALTH = 'socket_health';

    /**
     * @1003
     * @回传话单（工号、通话id，客户id，被叫号码，通话结果、通话时长）
     * socket_upload
     */
    public const SOCKET_UPLOAD = 'socket_upload';

    /**
     * @1004
     * @回传录音
     * socket_record
     */
    public const SOCKET_RECORD = 'socket_record';

    /**
     * @3001
     * @向手机客户端发送回复连接成功
     */
    public const SOCKET_SEND = 'socket_send';

    /**
     * @3003
     * @呼叫 （工号 通话id 客户端id 被叫号码）
     */
    public const SOCKET_CALL = 'socket_call';

    /**
     * @2003
     * @系统客户端向服务端发送消息
     */
    public const SOCKET_REQUEST = 'socket_request';

    /**
     * @4003
     * @呼叫请求结果（工号、结果）
     */
    public const SOCKET_RESULT = 'socket_result';