<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IM</title>
    <link rel="stylesheet" type="text/css" href="loading.css" />
    <style type="text/css">
        *{margin: 0; padding: 0;}
        div{font-size: 12px;}
        .msg_head{color: #999;}
        .msg_body{padding: 6px 12px 12px 12px;}
        .page_break { margin-top: 30px;}
        .page_num { display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center;}
        .pointer {cursor: pointer}
        .pointer a:hover {font-size: 20px; color: red}
    </style>
</head>
<body>
<?php
session_start();
if (empty($_SESSION['key']) && !empty($_POST['key'])) {
    if (md5($_POST['key']) === '469cee94c22059a4d0c2baa96491bb23') {
        $_SESSION['key'] = $_POST['key'];
    }
}
if (empty($_SESSION['key'])) {
    header("Location:login.html");
}
?>
<div id="app">
    <div>
        <div id="toolbar"></div>
        <div id="send_text" style="width: 100%;">
        </div>
        <button @click="sendMsg" style="float: right; width: 200px; line-height: 32px;margin-right: 20px;">发送</button>
        <button @click="jumpPage(1)" style="float: right; width: 80px; line-height: 32px; margin-right: 20px;">刷新</button>
        <br>
        <br>
    </div>
    <div class="loader" v-show="show_loading">
        <div>L</div>
        <div>O</div>
        <div>A</div>
        <div>D</div>
        <div>I</div>
        <div>N</div>
        <div>G</div>
    </div>
    <div id="show_text">
        <div v-for="item in data_list">
            <p class="msg_head">[{{ item.create_time }}][{{ item.user }}]</p><p class="msg_body" v-html="item.content"></p>
        </div>
        <div class="page_break" >
            <span class="page_num">{{page}} / {{total_page}}</span>
            <template v-if="total_page > 1">
                <span v-for="index in pages" class="page_num pointer">
                    <template v-if="index == page">{{index}}</template>
                    <template v-else><a @click="jumpPage(index)">{{index}}</a></template>
                </span>
            </template>
            <span class="page_num pointer"><a @click="jumpPage(page)">Refresh</a></span>
        </div>
    </div>
</div>

<!--<script type="text/javascript" src="//unpkg.com/wangeditor/release/wangEditor.min.js"></script>-->
<script type="text/javascript" src="wangEditor.min.js"></script>
<!--<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/vue"></script>
<!--<script src="https://unpkg.com/axios/dist/axios.min.js"></script>-->
<script src="axios.min.js"></script>
<script type="application/javascript">
    var app = new Vue({
        el: '#app',
        data: {
            editor: null,
            data_list:null,
            page:1,
            total_page:0,
            total_num:0,
            start_page:0,
            end_page:0,
            pages: [],
            show_loading:false
        },
        mounted: function() {
            var E = window.wangEditor;
            this.editor = new E('#toolbar','#send_text');
            this.editor.customConfig.uploadImgShowBase64 = true;
            this.editor.customConfig.uploadImgServer = 'upload.php';
            this.editor.customConfig.uploadImgMaxSize = 10000 * 1024 * 1024; // max 10GB
            this.editor.customConfig.uploadImgMaxLength = 1;
            this.editor.customConfig.uploadFileName = 'upload_file';
            this.editor.customConfig.uploadImgHooks = {
                customInsert: function (insertImg, result, editor) {
                    console.log(result)
                    if (result.type == 'file'){
                        editor.cmd.do('insertHTML', '<a href="'+result.data+'">'+result.file_name+'</a>');
                    } else {
                        insertImg(result.data)
                    }
                }
            };
            this.editor.create();
            this.getData(this.page);
        },
        methods: {
            sendMsg: function() {
                var _this = this;
                this.ajaxRequest({act : "send", content: this.editor.txt.html()}, function (response) {
                    _this.page = 1;
                    _this.getData(_this.page);
                });
            },
            getData: function (page) {
                var _this = this;
                this.ajaxRequest({act : "query", page: page}, function (response) {
                    _this.data_list = response.data.result.list;
                    _this.total_num = response.data.result.page_break[1];
                    _this.total_page = response.data.result.page_break[2];
                    _this.start_page = response.data.result.page_break[3];
                    _this.end_page = response.data.result.page_break[4];
                    _this.pages = [];
                    for (var i = _this.start_page; i <= _this.end_page; i++)
                    {
                        _this.pages.push(i);
                    }
                });
            },
            jumpPage: function(page) {
                this.page = page;
                this.getData(page);
            },
            ajaxRequest: function (data, callback) {
                this.show_loading = true;
                var _this = this;
                axios({
                    method: 'post',
                    url: 'deal.php',
                    data: data,
                    responseType: 'jsonstream'
                }).then(function (response) {
                    callback(response)
                    _this.show_loading = false;
                });
            }
        }
    })
</script>
</body>
</html>
