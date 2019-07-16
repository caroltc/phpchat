<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IM</title>
    <style type="text/css">
        *{margin: 0; padding: 0;}
        div{font-size: 12px;}
        .msg_head{color: #999;}
        .msg_body{padding: 6px 12px 12px 12px;}
        .page_break { margin-top: 30px;}
        .page_num { display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center;}
    </style>
</head>
<body>
<div>
    <div id="toolbar"></div>
    <div id="send_text" style="width: 100%;">
    </div>
    <button onclick="sendMsg()" style="float: right; width: 200px; line-height: 32px;">发送</button>
    <br>
    <br>
</div>
<div id="show_text">
    <?php
    $act = $_GET['act'];
    $page = intval($_GET['page']);
    $page = $page < 1 ? 1 : $page;
    require 'LibSqlite.php';

    function getClientIp() {
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

    $db = new LibSqlite("data/im.db");
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $act == 'send' && !empty($_POST['content'])) {
        $content = $_POST['content'];
        $db->insert($content, getClientIp());
    }

    //获取结果
    $list = $db->query($page);
    foreach ($list as $item) {
        echo '<div class="msg"><p class="msg_head">['.$item['create_time'].'][' . $item['user'] . ']</p><p class="msg_body">' . $item['content'] . '</p></div>';
    }

    // 分页
    list($page, $total_num, $total_page, $start_page, $end_page) = $db->getPageBreakParam($page);
    if ($page > 0) {
        echo '<p class="page_break"><span class="page_num">' . $page . '/' . $total_page . '</span>';
        if ($total_page > 1) {
            for ($i = $start_page; $i <= $end_page; $i++) {
                if ($page == $i) {
                    echo '<span class="page_num current_page">' . $i . '</span>';
                } else {
                    echo '<span class="page_num"><a href="index.php?page=' . $i . '">' . $i . '</a></span>';
                }
            }
        }
        echo '<span class="page_num"><a href="index.php">Refresh</a></span>';
        echo '</p>';
    }
    ?>
</div>
<script type="text/javascript" src="//unpkg.com/wangeditor/release/wangEditor.min.js"></script>
<script type="application/javascript">
    var E = window.wangEditor;
    var editor2 = new E('#toolbar','#send_text');
    editor2.customConfig.uploadImgShowBase64 = true;
    editor2.create();

    function sendMsg() {
        postSend('index.php?act=send', editor2.txt.html());
    }

    function postSend(URL, content) {
        var temp = document.createElement("form");
        temp.action = URL;
        temp.method = "post";
        temp.style.display = "none";
        var opt = document.createElement("textarea");
        opt.name = 'content';
        opt.value = content;
        temp.appendChild(opt);
        document.body.appendChild(temp);
        temp.submit();
        return temp;
    }
</script>
</body>
</html>
