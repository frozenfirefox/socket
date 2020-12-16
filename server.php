<?php

require_once "./lib/function.php";
require_once "./lib/SocketConst.php";

//创建Server对象，监听 127.0.0.1:9508 端口
$server = new Swoole\Server('0.0.0.0', 9508);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "[info]".$fd."-".date('y-m-d H:i:s')."-Client: Connect.\n";
});

$func=function ($server, $fd, $from_id, $message) {
    if(is_json($message) === false){
        $reData = re_json(500, '请求参数错误');
        $server->send($fd, $reData);
    }else{
        $params = json_decode($message, true);
        $service = isset($params['service'])?$params['service']:'default';
        $user_id = isset($params['user_id'])?$params['user_id']:9999;
        $user_key = 'work_info_'.$user_id;
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $auth = $redis->auth('123456');
        switch ($service){
            case SocketConst::SOCKET_LOGIN:
                $userInfo = $redis->get($user_key);
                if(!$userInfo) {
                    $userInfo = $params;
                }
                $userInfo['last_time'] = date('Y-m-d H:i:s');
                $userInfo['fd'] = $fd;
                $userInfo['alive'] = true;
                $redis->set($user_key, json_encode($userInfo));
                $list = json_decode($redis->get($user_key), true);
                $reData = re_json(200, '注册成功', $list);
                $server->send($fd,  $reData);
                //登陆接口
                break;
            case SocketConst::SOCKET_HEALTH:
                if(!$redis->get($user_key)){
                    $reData = re_json(502, '未找到注册工号');
                    $server->send($fd, $reData);
                    return;
                }
                $userInfo = json_decode($redis->get($user_key), true);
                $userInfo['last_time'] = date('Y-m-d H:i:s');
                $redis->set($user_key, json_encode($userInfo));
                $reData = re_json(200, '心跳检测成功', $user_key);
                $server->send($fd,  $reData);
                //心跳检查
                break;
            case SocketConst::SOCKET_UPLOAD:
                //回传话单
                if(!$redis->get($user_key)){
                    $reData = re_json(502, '未找到注册工号');
                    $server->send($fd, $reData);
                    return;
                }
                if(!(isset($params['record'])?:'')){
                    $reData = re_json(503, '请上传回传话单信息');
                    $server->send($fd, $reData);
                    return;
                }
                if(!(isset($params['record']['record'])?:'')){
                    $reData = re_json(503, '请上传回传录音信息');
                    $server->send($fd, $reData);
                    return;
                }
                $userInfo = json_decode($redis->get($user_key), true);
                $userInfo['records'][] = $params['record'];
                $redis->set($user_key, json_encode($userInfo));
                $reData = re_json(200, '回传话单成功', $user_key);
                $server->send($fd,  $reData);
                break;
            default:
                //default
                $reData = re_json(501, '请求接口错误');
                $server->send($fd, $reData);
                break;
        }

    }
};

//监听数据接收事件
$server->on('Receive', $func);

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $auth = $redis->auth('123456');
    $keys = $redis->keys('work_info*');
    foreach ($keys as $key){
        $userInfo = json_decode($redis->get($key), true);
        $ufd = isset($userInfo['fd'])?$userInfo['fd']:'';
        if($ufd === $fd){
            $userInfo['fd'] = '';
            $redis->set($key, json_encode($userInfo));
            break;
        }
    }
    echo "[info]".$fd."-".date('Y-m-d H:i:s')."-Client: Closed the connection!.\n";
});

//启动服务器
$server->start(); 

