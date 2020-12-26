<?php
date_default_timezone_set("Asia/Shanghai");

require_once "./lib/function.php";
require_once "./lib/SocketConst.php";

//创建Server对象，监听 127.0.0.1:9508 端口
$server = new Swoole\Server('0.0.0.0', 9508);

//$server->set([
//    'open_length_check' => true,
//    'package_max_length' => 50 * 1024 * 1024,
//    'package_length_type' => 'N',
//    'package_length_offset' => 0,
//    'package_body_offset' => 4,
//]);

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "[info]".$fd."-".date('y-m-d H:i:s')."-Client: Connect.\n";
});

//监听数据接收事件
$context = '';

$func=function ($server, $fd, $from_id, $message) use (&$context){
    $context .= $message;
    echo '[test]:'.$context;
    if(($pos = strpos($context, "\r\n\r\n")) !== false || ($pos = strpos($context, '\r\n\r\n')) !== false){
        echo '[pos]:'.$context;
        $message = str_replace('\r\n\r\n', '', $context);
        $context = '';
    }else{
        return;
    }
    
    echo '[sell]:'.is_json($message);
    echo "[info-request]:".$message.PHP_EOL;
    if(is_json($message) === false){
        $reData = re_json(500, '请求参数错误');
        $server->send($fd, $reData);
    }else{
        $params = json_decode($message, true);
        $service = isset($params['service'])?$params['service']:'default';
        $user_id = isset($params['user_id'])?$params['user_id']:9999;
        $prefix = isset($params['prefix'])?$params['prefix']:'work_info_';
        $user_key = $prefix.$user_id;
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $auth = $redis->auth('123456');
        switch ($service){
            case SocketConst::SOCKET_LOGIN:
                $userInfo = $redis->get($user_key);
                if(!$userInfo) {
                    $userInfo = $params;
                }else{
                    $userInfo = json_decode($userInfo, true);
                }
                $userInfo['last_time'] = date('Y-m-d H:i:s', time());
                $userInfo['fd'] = $fd;
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
                $userInfo['last_time'] = date('Y-m-d H:i:s', time());
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
            case SocketConst::SOCKET_CALL:
                //模拟发送任务
                $data = '{"service":"socket_call","user_id":1002, "call_id": "234232", consumer_id":2323, "call_phone":13312062424,"domain":"https:\/\/www.baidu.com"}';
                $data = $params;
                $fd = $data['fd'];
                unset($data['fd']);
                echo '[call]:'$fd.var_export($data);
                $reData = re_json(200, '呼叫请求 - 并且返回结果', $data);
                $server->send($fd,  $reData);
                break;
            case SocketConst::SOCKET_REQUEST:
                //呼叫请求 - 并且返回结果
                //先遍历连接池，删除心跳超时的连接
                //然后按工号查询手机客户端连接池，如果连接池中没有该工号手机客户端，则返回失败（不记录通话）
                //否则记录话单，并以话单ID向手机客户端发起呼叫（话单id以soap请求业务服创建后获得）
                $work_info = $redis->get('work_info_'.$params['user_id']);
                if(strtotime($work_info['last_time']) < (time() - 6) || !$work_info['fd']){
                    $reData = re_json(502, '未找到注册工号');
                    $server->send($fd, $reData);
                    return;
                }

                //这里获取话单id
                $call_id = 1;
                $data = '{"service":"socket_call","user_id":'.$params['user_id'].', "call_id": '.$call_id.', consumer_id":2323, "call_phone":'.$params['call_phone'].',"domain":"https:\/\/www.baidu.com"}';
                $reData = re_json(200, '呼叫请求 - 并且返回结果', $data);
                $server->send($work_info['fd'],  $reData);
                break;
            case SocketConst::SOCKET_RESULT:
                //呼叫请求 - 并且返回结果
                //先遍历连接池，删除心跳超时的连接
                //然后按工号查询手机客户端连接池，如果连接池中没有该工号手机客户端，则返回失败（不记录通话）
                //否则记录话单，并以话单ID向手机客户端发起呼叫（话单id以soap请求业务服创建后获得）
                $reData = re_json(200, '先遍历连接池，删除心跳超时的连接,然后按工号查询手机客户端连接池，如果连接池中没有该工号手机客户端，则返回失败（不记录通话）,否则记录话单，并以话单ID向手机客户端发起呼叫（话单id以soap请求业务服创建后获得）', $user_key);
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

