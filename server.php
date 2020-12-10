<?php

//创建Server对象，监听 127.0.0.1:9508 端口
$server = new Swoole\Server('0.0.0.0', 9508);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});



$func=function ($server, $fd, $from_id, $message) {

    $redis = new Redis();
	$redis->connect('47.94.167.205', 9510);
	$auth = $redis->auth('123456');
	$redis->sAdd('set',$message);

	$list = $redis->sMembers('set');

	var_dump($list);
	echo PHP_EOL;
    $server->send($fd, "Server嘿嘿: " . $message);
};



//监听数据接收事件
$server->on('Receive', $func);

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "客户端关了Client: Close.\n";
});

//启动服务器
$server->start(); 

