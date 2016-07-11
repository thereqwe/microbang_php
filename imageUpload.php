<?php
require_once "Mysql.php";
$mid = $_REQUEST["mid"];
if($_FILES['file']['error'] > 0){
    echo '!problem:';
    switch($_FILES['file']['error'])
    {
        case 1: echo 'size';
            break;
        case 2: echo 'size！';
            break;
        case 3: echo 'size';
            break;
        case 4: echo 'size';
            break;
    }

    exit;
}
if($_FILES['file']['size'] > 1000000){
    echo 'size！';
    exit;
}
if($_FILES['file']['type']!='image/jpeg' && $_FILES['file']['type']!='image/gif' && $_FILES['file']['type']!='image/png'){
    echo 'JPG or GIF,png only';
    exit;
}
$today = date("YmdHis");
$filetype = $_FILES['file']['type'];
if($filetype == 'image/jpeg'){
    $type = '.jpg';
}
if($filetype == 'image/gif'){
    $type = '.gif';
}
if($filetype == 'image/png'){
    $type = '.png';
}
$upfile = './upfile/' . $today . $type;
if(is_uploaded_file($_FILES['file']['tmp_name']))
{
    if(!move_uploaded_file($_FILES['file']['tmp_name'], $upfile))
    {
        echo 'move failed';
        exit;
    }
} else {
    echo 'problem';
    exit;
}
$db = new MbMysql("127.0.0.1","root","123","micro_bang","","UTF8");
$db->update("mb_member","avatar_url='/upfile/{$today}{$type}'","mid=$mid");
$arr = array("errCode"=>"000","msg"=>"succ","avatar_url"=>"/upfile/{$today}{$type}");
echo  json_encode($arr);
closedir($dir);
