<?php
date_default_timezone_set("Asia/Shanghai");

require_once "./lib/function.php";
require_once "./lib/SocketConst.php";

//创建WebSocket Server对象，监听0.0.0.0:9509端口
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9509);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    $info = "[info]".$request->fd."-".date('y-m-d H:i:s')."-Client: Connect.\n".$request->server;
    echo $info;
    $ws->push($request->fd, $info);
});

$func=function ($ws, $frame) {
    $server = $ws;
    $message = $frame->data;
    $fd = $frame->fd;
    echo "[info-request]:".$message.PHP_EOL;
    if(is_json($message) === false){
        $reData = re_json(500, '请求参数错误');
        $server->push($fd, $reData);
    }else{
        $params = json_decode($message, true);
        $service = isset($params['service'])?$params['service']:'default';
        $user_id = isset($params['user_id'])?$params['user_id']:9999;
        $prefix = isset($params['prefix'])?$params['prefix']:'server_info_';
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
                $server->push($fd,  $reData);
                //登陆接口
                break;
            case SocketConst::SOCKET_HEALTH:
                if(!$redis->get($user_key)){
                    $reData = re_json(502, '未找到注册工号');
                    $server->push($fd, $reData);
                    return;
                }
                $userInfo = json_decode($redis->get($user_key), true);
                $userInfo['last_time'] = date('Y-m-d H:i:s', time());
                $redis->set($user_key, json_encode($userInfo));
                $reData = re_json(200, '心跳检测成功', $user_key);
                $server->push($fd,  $reData);
                //心跳检查
                break;
            case SocketConst::SOCKET_REQUEST:
                //呼叫请求 - 并且返回结果
                //先遍历连接池，删除心跳超时的连接
                //然后按工号查询手机客户端连接池，如果连接池中没有该工号手机客户端，则返回失败（不记录通话）
                //否则记录话单，并以话单ID向手机客户端发起呼叫（话单id以soap请求业务服创建后获得）
                $work_info = $redis->get('work_info_'.$params['user_id']);
                $work_info = $work_info?json_decode($work_info, true):'';
                if(!$work_info || !$work_info['fd']){
                    $reData = re_json(502, '未找到注册工号');
                    $server->push($fd, $reData);
                    return;
                }

                $result = create_call_id($params['call_phone'], $params['consumer_id'], $params['user_id']);
//                $result = json_encode($result, true);
                if(!($result['res']??'')){
                    $reData = re_json(503, '话单创建失败');
                    $server->push($fd, $reData);
                }
                $call_id = $result['call_id']??'';
                $data = '{"service":"socket_call","user_id":'.$params['user_id'].', "call_id": '.$call_id.', "consumer_id":2323, "call_phone":'.$params['call_phone'].',"domain":"https:\/\/www.baidu.com", "fd": '.$work_info['fd'].'}\r\n\r\n';
                echo '[call]:'.$data;
                $re = socket_client($data);
                $re = json_decode($re, true);
                if($re['status'] <> 200){
                    $reData = re_json(504, '发送通话失败');
                    $server->push($fd, $reData);
                }

                $reData = re_json(200, '呼叫请求 - 并且返回结果', $data);
                $server->push($fd,  $reData);
                break;
            default:
                //default
                $reData = re_json(501, '请求接口错误');
                $server->push($fd, $reData);
                break;
        }
    }
};

//监听WebSocket消息事件
$ws->on('message', $func);

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $auth = $redis->auth('123456');
    $keys = $redis->keys('server_info*');
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

$ws->start();

echo "server start success!!\n";

