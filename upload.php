<?php

function getSize($filesize) {
    if($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
    } elseif($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
    } elseif($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
    } else {
        $filesize = $filesize . ' byte';
    }
    return $filesize;
}

$response_json = ['errno' => 0, 'data' => [], 'type' => 'file'];

$allowedExts = array("gif", "jpeg", "jpg", "png","bmp");
$temp = explode(".", $_FILES["upload_file"]["name"]);
$extension = end($temp);        // 获取图片后缀名
if (in_array(strtolower($extension), $allowedExts)){
    $response_json['type'] = 'image';
}

if ($_FILES["upload_file"]["error"] > 0) {
    $response_json = ['errno' => 500, 'data' => [], 'msg' => $_FILES["upload_file"]["error"]];
} else {
    $file_name = str_replace(' ', '', $_FILES["upload_file"]["name"]);
    if (!file_exists("upload/" . $_FILES["upload_file"]["name"])) {
        move_uploaded_file($_FILES["upload_file"]["tmp_name"], "upload/" . $file_name);
    }
    $response_json['data'] = 'upload/' . $file_name;
    $response_json['file_name'] = $_FILES["upload_file"]["name"] . '(' .getSize($_FILES["upload_file"]["size"]). ')';
}
header('Content-Type:application/json');
echo json_encode($response_json);