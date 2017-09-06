<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-9-6
 * Time: 上午11:04
 */

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);
//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    if ($request->fd > 10) {
        echo "can not connect client {$request->fd}";
        $ws->push($request->fd, "[系统]人数超过上限，无法接入");
    } else {
        echo "connect client {$request->fd}";
        addClient($request->fd);
        sendMsg($ws, "[系统]用户{$request->fd}进入");
    }
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    sendMsg($ws, "[用户{$frame->fd}]" . $frame->data);
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "close client {$fd}";
    removeClient($fd);
    sendMsg($ws, "[系统]用户{$fd}离开");
});

$ws->start();

function sendMsg($ws, $data)
{
    $clients = getClients();
    if (!empty($clients)) {
        foreach($clients as $fd) {
            @$ws->push($fd, "[".date('Y-m-d H:i:s')."]{$data}");
        }
    }
}

function addClient($fd)
{
    if (file_exists(__DIR__.'/clients.json')) {
        $data = file_get_contents(__DIR__.'/clients.json');
        $all_clients = json_decode($data, true);
        if (!in_array($fd, $all_clients)) {
            $all_clients[] = $fd;
            file_put_contents(__DIR__.'/clients.json', json_encode($all_clients));
        }
    }
}

function removeClient($fd)
{
    if (file_exists(__DIR__.'/clients.json')) {
        $data = file_get_contents(__DIR__.'/clients.json');
        $all_clients = json_decode($data, true);
        if (in_array($fd, $all_clients)) {
            unset($all_clients[array_search($fd, $all_clients)]);
            file_put_contents(__DIR__.'/clients.json', json_encode($all_clients));
        }
    }
}

function getClients()
{
    if (file_exists(__DIR__.'/clients.json')) {
        $data = file_get_contents(__DIR__.'/clients.json');
        return json_decode($data, true);
    }
    return [];
}