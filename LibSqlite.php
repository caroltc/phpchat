<?php
/**
 * Created by PhpStorm.
 * User: caroltc
 * Date: 19-7-16
 * Time: 上午10:17
 */

// sqlite分页类
class LibSqlite {
    private $db;
    private $tab_name = 'im';
    public function __construct($db_name){
        // 初始化数据库，并且连接数据库 数据库配置
        $this->db = new PDO('sqlite:' . $db_name);
        $this->tab_init();
    }
    public function tab_init()
    {
        # 表初始化,创建表
        $this->db->exec('CREATE TABLE im(id integer PRIMARY KEY autoincrement,content text, user varchar(16), create_time varchar(16) )');
    }
    public function insert($content, $user)
    {
        $result=$this->db->exec("INSERT INTO im (content,user, create_time) values('{$content}', '" . $user . "' , '" . date('Y-m-d H:i:s') ."')");
        if (!$result) {
            return false;
        }
        return $result;
    }

    public function getPageBreakParam($page = 1, $limit = 2)
    {
        $sth = $this->db->prepare('SELECT count(id) as c FROM '.$this->tab_name);
        $sth->execute();
        $result = $sth->fetchAll();
        $total_num = $result[0]['c'];
        $total_page = intval($total_num / $limit);
        $total_page = ($total_page * $limit) < $total_num ? $total_page + 1 : $total_page;
        $page = $page > $total_page ? $total_page : $page;
        list($start_page, $end_page) = $this->getShowPage($page, $total_page);
        return [$page, $total_num, $total_page, $start_page, $end_page];
    }

    private function getShowPage($current_page, $total_page, $show_page = 10)
    {
        $left_side_show = $right_side_show = intval($show_page/2);
        $start = $current_page - $left_side_show;
        if ($start < 1) {
            $start = 1;
            $right_side_show += ($left_side_show - $current_page + 1);
        }
        $end = $current_page + $right_side_show;
        if ($end > $total_page) {
            $end = $total_page;
            $offset = $current_page - $total_page + $right_side_show;
            if ($start - $offset < 1) {
                $start = 1;
            } else {
                $start -= $offset;
            }
        }
        return [$start, $end];
    }

    public function query($page = 1, $limit = 2)//表名称和条件
    {
        $start = $limit * ($page - 1);
        $end = $start + $limit;
        $sth = $this->db->prepare('SELECT * FROM '.$this->tab_name.' order by id desc limit ' . $start . ',' . $end);
        $sth->execute();
        $result = $sth->fetchAll();
        return $result;
    }
}