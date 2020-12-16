<?php

/**
 * socket const
 * Class SocketConst
 */
class SocketConst
{
    /**
     * @1001
     * socket login
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
     * @回传话单
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
}