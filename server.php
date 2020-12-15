<?php

require_once "./function.php";
require_once "./router.php";

//创建Server对象，监听 127.0.0.1:9508 端口
$server = new Swoole\Server('0.0.0.0', 9508);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "[info]".$fd."-".date('y-m-d H:i:s')."-Client: Connect.\n";
});

$func=function ($server, $fd, $from_id, $message) {
    if(!is_json($message)){
        $reData = re_json(500, '请求参数错误');
        $server->send($fd, $reData);
    }else{
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $auth = $redis->auth('123456');
        $redis->sAdd('work_info',$message);
        $list = $redis->sMembers('work_info');
        $reData = re_json(200, '操作成功', $list);
        $server->send($fd, "Server嘿嘿-socket: " . $message);
    }
};

//监听数据接收事件
$server->on('Receive', $func);

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "[info]".$fd."-".date('Y-m-d H:i:s')."-Client: Closed the connection!.\n";
});

//启动服务器
$server->start(); 

