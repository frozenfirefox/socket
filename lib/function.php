<?php

/**
 * 判断是否是json
 * @param string $str
 * @return bool
 */
function is_json(string $str): bool
{
    json_decode($str);

    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * @param int $code
 * @param string $message
 * @param $arr
 * @return false|string
 */
function re_json($code = 200, $message = 'ok', $arr = []){
    $reData = [
        'status' => $code,
        'message' => $message,
        'result' => $arr
    ];
    return json_encode($reData);
}

/**
 * 用来发送消息socket
 * @param array $message
 * @param string $host
 * @param int $port
 */
function socket_client($message = [], $host = '47.94.167.205', $port = 9508){
    //创建一个socket套接流
    $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
    /****************设置socket连接选项，这两个步骤你可以省略*************/
    //接收套接流的最大超时时间1秒，后面是微秒单位超时时间，设置为零，表示不管它
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
    //发送套接流的最大超时时间为6秒
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 6, "usec" => 0));
    /****************设置socket连接选项，这两个步骤你可以省略*************/

//连接服务端的套接流，这一步就是使客户端与服务器端的套接流建立联系
    if(socket_connect($socket,$host,$port) == false){
        echo 'connect fail massege:'.socket_strerror(socket_last_error());
    }else{
        //登陆
        $message = json_encode($message);

        //转为GBK编码，处理乱码问题，这要看你的编码情况而定，每个人的编码都不同
        $message = mb_convert_encoding($message,'GBK','UTF-8');
        //向服务端写入字符串信息

        if(socket_write($socket,$message,strlen($message)) == false){
            echo 'fail to write'.socket_strerror(socket_last_error());

        }else{
            echo 'client write success'.PHP_EOL;
            //读取服务端返回来的套接流信息
            while($callback = socket_read($socket,1024)){
                echo '[info] params: '.$message.'--server return message is:'.PHP_EOL.$callback;
            }
        }
    }
    socket_close($socket);//工作完毕，关闭套接流
}

/**
 * 创建话单
 * @param $call_phone
 * @param $consumer_id
 * @param $user_id
 * @return mixed
 * @throws SoapFault
 */
function create_call_id($call_phone, $consumer_id, $user_id){
    header("Content-type:text/html;charset=utf-8");
    $LIB = new SoapClient(null, array(
        'location'=>'http://sryd-mrt.bj01.bdysite.com/s_app_call.php',
        'uri'=>'sryd'
    ));

    //电话号码，客户ID，工号
    return $LIB->Insert_App_Call($call_phone,$consumer_id, $user_id);
}