<?php
require 'LibSqlite.php';
class Deal
{

    private $db;

    public function __construct()
    {
        $this->db = new LibSqlite('data/im.db');
    }

    public function getClientIp() {
        $ip = 'unknown';
        $unknown = 'unknown';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            // 使用透明代理、欺骗性代理的情况
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            // 没有代理、使用普通匿名代理和高匿代理的情况
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // 处理多层代理的情况
        if (strpos($ip, ',') !== false) {
            // 输出第一个IP
            $ip = reset(explode(',', $ip));
        }

        return $ip;
    }

    public function addData($content)
    {
        return $this->db->insert(base64_encode($content), $this->getClientIp());
    }

    public function queryData($page)
    {
        $list = $this->db->query($page);
        return array_map(function ($item) {
            $item['content'] = base64_decode($item['content']) ? base64_decode($item['content']) : $item['content'];
            return $item;
        }, $list);
    }

    public function getPageBreak($page)
    {
       return $this->db->getPageBreakParam($page);
    }

    public function sendAjax($data)
    {
        header("Content-Type:application/json");
        echo json_encode($data);
        exit;
    }
}

$data = file_get_contents('php://input');
$data = json_decode($data, true);
$act = $data['act'];
$deal = new Deal();
switch ($act) {
    case 'query':
        $page = $data['page'];
        $list = $deal->queryData($page);
        $page_break = $deal->getPageBreak($page);
        $deal->sendAjax(['status' => 'ok', 'result' => ['list' => $list, 'page_break' => $page_break]]);
        break;
    case 'send':
        $content = $data['content'];
        $deal->addData($content);
        $deal->sendAjax(['status' => 'ok']);
        break;
}
