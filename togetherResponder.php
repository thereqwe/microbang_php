<?php
ini_set("display_errors", "On");
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL &~E_NOTICE &~E_DEPRECATED);
session_start();
require_once "./Qiniu/Auth.php";
require_once "TopSdk.php";
date_default_timezone_set('Asia/Shanghai');
$register_time = $time = time();
require_once  "Mysql.php";
 global $db;
 $db = new MbMysql($DB_IP,$DB_USER_NAME,$DB_PASSWORD,$DB_NAME,"","UTF8");
 $now = now();
 $action = p("action");
 $_SESSION["mid"]= $mid = p("mid");
 if($action==""){
    die(j("001","action can not be empty"));
 }

if ($action == "get_notification"){
    $db->select("SYLNotification","*","to_mid='$mid' and is_handled=0 and is_deleted=0");
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
            "notification_idx" => $row["notification_idx"],
            "title" => $row["title"],
            "json_data"=>$row["json_data"],
            "text_content"=>$row["text_content"],
            "type"=>$row["type"],
            "create_time"=>$row["create_time"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action=="get_qiniu_upload_token") {
    $auth = new \Qiniu\Auth("6YaKdLne-f_ayq2rpbObKfTOx60dWPuneh8BcnY4","xYXjYRq6j5LgJZvkRSB9btf-cVeLuUXk7QtxSYbF");
    $data = array(
        "data"=>$auth->uploadToken("together"),
    );
    echo  j("000","succ",$data);
}else if($action=="set_avatar_url") {
    $avatar_url = p("avatar_url");
    $db->query("update SYLMember set avatar_url='$avatar_url' where mid = $mid");
    echo  j("000","succ");
}else if($action=="get_nick_name") {
    $sql = "select * from SYLMember where mid=$mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
            "nick_name"=>$row["nick_name"]
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
}else if($action=="save_height") {
    $height = p("height");
    $db->update("SYLMember","height='$height'","mid=$mid");
    echo  j("000","succ");
}else if($action=="save_sex") {
    $sex = p("sex");
    $db->update("SYLMember","sex='$sex'","mid=$mid");
    echo  j("000","succ");
}else if($action=="save_nick_name") {
    $nick_name = p("nick_name");
    $db->update("SYLMember","nick_name='$nick_name'","mid=$mid");
    echo  j("000","succ");
}else if($action=="save_personality") {
    $personality_idx = p("personality_idx");
    $db->update("SYLMember","personality_idx='$personality_idx'","mid=$mid");
    echo  j("000","succ");
}else if($action=="save_age") {
    $age = p("age");
    $db->update("SYLMember","age='$age'","mid=$mid");
    echo  j("000","succ");
}else if($action=="save_slogan") {
    $slogan = p("slogan");
    $db->update("SYLMember","slogan='$slogan'","mid=$mid");
    echo  j("000","succ");
}else if($action=="get_slogan") {
    $sql = "select * from SYLMember where mid=$mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
            "slogan"=>$row["slogan"]
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
}else if($action=="get_member_show") {
    $see_mid = p("see_mid");
    $sql = "select * from SYLMember as t1 LEFT  JOIN
SYLPersonality as t2 on t1.personality_idx=t2.personality_idx
 where t1.mid=$see_mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
            "avatar_url" => $row["avatar_url"],
            "sex" => $row["sex"],
            "personality_text"=>$row["personality_text"],
            "height"=>$row["height"],
            "slogan"=>$row["slogan"],
            "age"=>$row["age"],
            "nick_name"=>$row["nick_name"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action=="get_profile") {
    $sql = "select * from SYLMember as t1 LEFT  JOIN SYLPersonality as t2 on t1.personality_idx=t2.personality_idx
 where t1.mid=$mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        $json_hobby = json_decode($row["json_hobby"],true);
//        var_dump($json_hobby);
        $i = 0;
        foreach ($json_hobby as $item) {
            if($item["value"]!=""){
                $i++;
            }
        }

        $hobby_percent = "完成度".(int)($i/count($json_hobby)*100)."%";
        array_push_ex($arr, array(
            "avatar_url" => $row["avatar_url"],
            "sex" => $row["sex"],
            "personality_text"=>$row["personality_text"],
            "height"=>$row["height"],
            "slogan"=>$row["slogan"],
            "age"=>$row["age"],
            "nick_name"=>$row["nick_name"],
            "hobby_percent"=>$hobby_percent,
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action=="send_sms_captcha") {
    $mobile = p("mobile");
    if($mobile==""){
        echo  j("003","no mobile");
        return;
    }
    createCaptcha();
    $c = new TopClient;
    $c->appkey = "23651390";
    $c->secretKey = "3b54a53e6355ee0f5ae4d8eac93f36e1";
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend("123456");
    $req->setSmsType("normal");
    $req->setSmsFreeSignName("聚一起");
    $req->setSmsParam("{\"code\":\"{$_SESSION['captcha']}\"}");
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode("SMS_49355019");
    $resp = $c->execute($req);
    echo  j("000","{$_SESSION['captcha']}",array("captcha0"=>$_SESSION['captcha'],"resp"=>$resp));
    return;
}else if($action=="get_friend_list"){
    $arr = array();
    $db->query("select * from SYLFriend as t1 LEFT JOIN  SYLMember as t2 on t1.from_mid=t2.mid where t1.to_mid = $mid
UNION
    select * from SYLFriend as t1 LEFT JOIN  SYLMember as t2 on t1.to_mid=t2.mid where t1.from_mid = $mid
     order by nick_name ");
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
            "nick_name" => $row["nick_name"],
            "email" => $row["email"],
            "mid" => $row["mid"],
            "avatar_url"=>$row["avatar_url"],
            "mobile"=>$row["mobile"],
        ));
    }

    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action=="wantTobeFriend"){
     $from_mid = p("mid");
     $to_nick_name = p("to_nick_name");
     $type = 2;
    if($to_nick_name == ""){
        echo  j("139","请输入对方的昵称",$data);
        return;
    }
     if($to_mid == $mid){
         echo  j("129","不能添加自己为好友",$data);
         return;
     }
     //请求者昵称
     $db->query("select nick_name from SYLMember where mid=$mid");
     while($row = $db->fetch_array()){
         $from_nick_name = $row["nick_name"];
     }
     //对方的 mid
     $db->select("SYLMember","mid","nick_name = '$to_nick_name' limit 1");
     while($row=$db->fetch_array()){
         $to_mid = $row["mid"];
     }
     $json_data = json_encode(array("from_mid"=>$from_mid,"to_mid"=>$to_mid,
         "from_nick_name"=>$from_nick_name,"to_nick_name"=>$to_nick_name));
     $create_time = time();
     if($to_mid==''){
         echo  j("109","此用户不存在",$data);
         return;
     }else {
         $db->query("select * FROM  SYLFriend where ((from_mid = '$from_mid' and to_mid='$to_mid') or (from_mid = '$to_mid' and to_mid='$from_mid'))");
         if($db->fetch_array()!==false){
             echo  j("003","你们已经是好友了");
             return;
         }
         $db->query("select * FROM  SYLNotification where  json_data = '$json_data'");
         if($db->fetch_array()!==false){
             echo  j("004","你已发送过请求");
             return;
         }
         $db->insert("SYLNotification", "title,text_content,json_data,type,create_time,to_mid",
             "'好友申请','{$from_nick_name}想成为你的好友','$json_data','$type','$create_time','$to_mid'");
         echo  j("000","succ",$data);
         return;
     }
     return;
}else if($action == "delete_notification") {
    $notification_idx  = p("notification_idx");
    $db->query("update SYLNotification set is_deleted = 1 where notification_idx = $notification_idx");
    echo  j("000","succ",$data);
    return;
}else if($action == "handle_notification") {
    $notification_idx  = p("notification_idx");
    $db->query("update SYLNotification set is_handled = 1 where notification_idx = $notification_idx");
    echo  j("000","succ",$data);
    return;
}else if($action == "create_friendship") {
    $json_data =  (p("json_data"));
    $json_data = str_replace("\\",'',$json_data);
    $arr_data = json_decode($json_data,true);
    $from_mid = $arr_data["from_mid"];
    $to_mid = $arr_data["to_mid"];
    $db->insert("SYLFriend", "from_mid,to_mid,create_time",
        "'$from_mid','$to_mid','$time'");
    echo  j("000","succ",$data);
    return;
}elseif($action == "login"){
    $mobile = p("mobile");
    $pwd = md5(p("pwd"));
    $uuid = p("uuid");
    $arr = array();
    $db->query("select * from SYLMember where pwd ='$pwd' and mobile = '$mobile' limit 1");
    //echo "select * from SYLMember where pwd ='$pwd' and mobile = '$mobile' limit 1";
    while($row=$db->fetch_array()) {
        array_push_ex($arr, array(
             "nick_name" => $row["nick_name"],
             "email" => $row["email"],
             "mid" => $row["mid"],
             "avatar_url"=>$row["avatar_url"],
             "mobile"=>$row["mobile"],
         ));
         //更新uuid
         $db->query("update SYLMember set uuid='$uuid' where mid = {$row['mid']}");
     }
     $data = array(
         "data"=>$arr
     );
     echo  j("000","succ",$data);
     return;
 }else if($action == "get_version"){
     echo j("000", "succ",array("version"=>"1.0.0"));
     return;
}else if($action == "create_member"){
    $captcha = p("captcha");
    if($captcha != $_SESSION["captcha"]){
    echo j("023", "验证码错误 --->$captcha<--- {$_SESSION['captcha']}");
        return;
    }
    $nick_name = p("nick_name");
    $pwd = md5(p("pwd"));
    $email = p("email");
    $mobile = p("mobile");
    $db->select("SYLMember","mid","nick_name='$nick_name'");
    $i=0;
    while($row=$db->fetch_array()){
        $i++;
    }
    if($i>0){
        echo j("043", "昵称已经被使用");
        return;
    }
    $db->select("SYLMember","mid","email='$email'");
    $i=0;
    while($row=$db->fetch_array()){
        $i++;
    }
    if($i>0){
        echo j("053", "邮箱已经被使用");
        return;
    }
    $db->select("SYLMember","mid","mobile='$mobile'");
    $i=0;
    while($row=$db->fetch_array()){
        $i++;
    }
    if($i>0){
        echo j("083", "手机已经被使用");
       return;
    }
    $db->insert("SYLMember","nick_name,pwd,register_time,email,mobile","'$nick_name','$pwd','$register_time','$email','$mobile'");
     echo j("000", "succ",array(
         "captcha"=>$_SESSION["captcha"],
         "mid"=>$db->insert_id(),
     ));
     return;
 }else if($action == "get_captcha") {
    echo j("000", "succ",array("captcha"=>$_SESSION["captcha"]));
    return;
 }else if($action == "create_activity") {
     $activity_title = p("activity_title");
     $start_time= p("start_time");
     $info= (p("info"));
     $meet_lat= p("meet_lat");
     $meet_lng= p("meet_lng");
     $category_idx= p("category_idx");
     $meet_address = p("meet_address");
     $activity_address = p("activity_address");
     $cover_url = p("cover_url");
     $db->insert("SYLActivity", "activity_title,start_time,info,mid,meet_lat,meet_lng,category_idx,meet_address,
          activity_address,cover_url",
         "'$activity_title','$start_time','$info','$mid','$meet_lat','$meet_lng','$category_idx','$meet_address',
         '$activity_address','$cover_url'");
     echo j("000", "succ");
     return;
 }else if($action == "get_category"){
    $rst =$db->select("SYLCategory");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "idx"=>$row["category_idx"],
            "title"=>$row["category_title"],
            "category_level"=>$row["category_level"],
            "parent_idx"=>$row["parent_idx"]
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
         return;
 }else if($action == "get_signed_up_list"){//我报名的活动
    $rst =$db->query("select * from  SYLSignUp as t1
      LEFT  JOIN SYLActivity as t2
      on t1.activity_idx=t2.activity_idx
      LEFT JOIN  SYLCategory as t3 on t2.category_idx=t3.category_idx
      where t1.mid=$mid");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "activity_title"=>$row["activity_title"],
            "meet_address"=>$row["meet_address"],
            "activity_address"=>$row["activity_address"],
            "activity_idx"=>$row["activity_idx"],
            "category_title"=>$row["category_title"],
            "start_time"=>$row["start_time"],
            "cover_url"=>$row["cover_url"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
 }else if($action == "get_my_activity_list"){//我发起的活动
    $rst =$db->query("select * from  SYLActivity as t1
      LEFT  JOIN SYLCategory as t2
      on t1.category_idx=t2.category_idx where is_deleted!=1 and mid ='$mid' ");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "activity_title"=>$row["activity_title"],
            "meet_address"=>$row["meet_address"],
            "activity_address"=>$row["activity_address"],
            "activity_idx"=>$row["activity_idx"],
            "category_title"=>$row["category_title"],
            "start_time"=>$row["start_time"],
            "cover_url"=>$row["cover_url"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "delete_activity"){
    $activity_idx = p("activity_idx");
    $db->query("update SYLActivity set is_deleted=1 where activity_idx=$activity_idx");
    echo  j("000","succ");
    return;
}else if($action == "save_hobby"){
    $json_hobby = p("json_hobby");
    $db->query("update SYLMember set json_hobby = '$json_hobby' where mid = $mid ");
    echo  j("000","succ",$data);
    return;
}else if($action == "get_user_hobby"){
    $db->query("select * from SYLMember where mid = $mid");
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr,array(
            "json_hobby"=>$row["json_hobby"]
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "get_sign_up_member"){
    $activity_idx = p("activity_idx");
    $mid = p("mid");
    $db->query("select * from SYLSignUp as t1 left join SYLMember as t2 on t1.mid=t2.mid
where t1.activity_idx=$activity_idx");
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push_ex($arr,array(
            "nick_name"=>$row["nick_name"],
            "mid"=>$row["mid"],
            "reason"=>($row["reason"]),
            "avatar_url"=>$row["avatar_url"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "check_uuid"){
    $db->query("select uuid from SYLMember where mid = '$mid' limit 1");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "uuid"=>$row["uuid"],
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "sign_up_activity"){
    $activity_idx = p("activity_idx");
    $reason= (p("reason"));
    $mid = p("mid");
    //check if has been enrolled
    $db->select("SYLSignUp","*","mid='$mid' and activity_idx='$activity_idx'");
    if($db->fetch_array()!=false) {
        echo j("002", "您已经报过");
        return;
    }
    //sign up
    $db->insert("SYLSignUp","activity_idx,mid,reason,create_time",
        "'$activity_idx','$mid','$reason','$time'");
    echo  j("000","succ");
    return;
}else if($action == "get_activity_detail"){
    $activity_idx = p("activity_idx");
    $rst =$db->query("SELECT * from SYLSignUp  where activity_idx = '$activity_idx' and mid='$mid' limit 1");
    if($db->fetch_array()==false){
        $has_sign_up = "0";
    }else{
        $has_sign_up = "1";
    }
    $rst =$db->query("SELECT * from SYLActivity as t1 left join SYLMember as t2 on t1.mid=t2.mid LEFT join
SYLCategory as t3 on t3.category_idx=t1.category_idx WHERE activity_idx = '$activity_idx'");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "has_sign_up"=>$has_sign_up,
            "activity_title"=>$row["activity_title"],
            "activity_address"=>$row["activity_address"],
            "activity_idx"=>$row["activity_idx"],
            "category_idx"=>$row["category_idx"],
            "category_title"=>$row["category_title"],
            "start_time"=>$row["start_time"],
            "meet_address"=>$row["meet_address"],
            "meet_lat"=>$row["meet_lat"],
            "meet_lng"=>$row["meet_lng"],
            "nick_name"=>$row["nick_name"],
            "sex"=>$row["sex"],
            "age"=>$row["age"],
            "info"=>($row["info"]),
            "mobile"=>$row["mobile"],
            "mid"=>$row["mid"],
            "cover_url"=>$row["cover_url"],
            "avatar_url"=>$row["avatar_url"],
        ));
    }

    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "get_near_activity_coordinate"){
    $lat = p("lat");
    $lng = p("lng");
    $range_max_age =    p("range_max_age");
    $range_min_age =    p("range_min_age");
    $range_start_day =  p("range_start_day");
    $range_distance =   p("range_distance");
    $range_sex =        p("range_sex");
    $range_category_idx = p("range_category_idx");
    $search_key_word = p("search_key_word");

    //开始拼过滤条件
    $condition = "1=1";
    if($search_key_word!="") {
        $condition .=" and activity_title like '%$search_key_word%'
        or info like '%$search_key_word%'";
    }

    if($range_max_age!=""&&$range_min_age!=""){
        $condition .=" and t3.age>'$range_min_age' and t3.age<'$range_max_age'";
    }
    if($range_sex!=""){
        $condition .=" and t3.sex='$range_sex'";
    }
    if($range_distance!=""){
        $condition .=" and (POWER(MOD(ABS(meet_lng - $lng),360),2) + POWER(ABS(meet_lat - $lat),2))<='$range_distance'";
    }
    if($range_category_idx!=""){
        $condition .=" and t1.category_idx='$range_category_idx'";
    }
    if($range_start_day!=""){
        if($range_start_day=="1"){
            $sTime = strtotime('today');
            $eTime = strtotime('today')+3600*24;
            $condition  .=" and t1.start_time between $sTime and $eTime";
        }else  if($range_start_day=="2"){
            $sTime = strtotime('tomorrow');
            $eTime = strtotime('tomorrow')+3600*24;
            $condition  .=" and t1.start_time between $sTime and $eTime";
        }else  if($range_start_day=="3"){
            $sTime = strtotime('tomorrow');
            $eTime = strtotime('tomorrow')+3600*24*2;
            $condition  .=" and t1.start_time between $sTime and $eTime";
        }else  if($range_start_day=="4"){
            $sTime = strtotime('tomorrow');
            $eTime = strtotime('tomorrow')+3600*24*3;
            $condition  .=" and t1.start_time between $sTime and $eTime";
        }else if($range_start_day == "0"){//全部
            //nothing
        }

    }
    //echo $condition;
    //开始搜索
    $rst =$db->query("SELECT *,
        (POWER(MOD(ABS(meet_lng - $lng),360),2) + POWER(ABS(meet_lat - $lat),2)) AS distance
        FROM SYLActivity as t1 left JOIN  SYLCategory as t2 on t1.category_idx=t2.category_idx
        LEFT  join SYLMember as t3 on t1.mid=t3.mid where $condition
        ORDER BY distance LIMIT 100");
//    echo "SELECT *,
//        (POWER(MOD(ABS(meet_lng - $lng),360),2) + POWER(ABS(meet_lat - $lat),2)) AS distance
//        FROM SYLActivity as t1 left JOIN  SYLCategory as t2 on t1.category_idx=t2.category_idx
//        LEFT  join SYLMember as t3 on t1.mid=t3.mid where $condition
//        ORDER BY distance LIMIT 100";
    $arr = array();
    while($row=$db->fetch_array()){
        array_push_ex($arr,array(
            "activity_title"=>$row["activity_title"],
            "activity_address"=>$row["activity_address"],
            "activity_idx"=>$row["activity_idx"],
            "category_idx"=>$row["category_idx"],
            "category_title"=>$row["category_title"],
            "start_time"=>$row["start_time"],
            "meet_address"=>$row["meet_address"],
            "meet_lat"=>$row["meet_lat"],
            "meet_lng"=>$row["meet_lng"],
            "nick_name"=>$row["nick_name"],
            "avatar_url"=>$row["avatar_url"],
            "cover_url"=>$row["cover_url"],
        ));
    }

    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else {
     die(j("002","wrong action"));
 }
/////////////////////////////////////////////////////
//get or post param
function array_push_ex(array &$arr1,$arr2) {
    foreach ($arr2 as $key => $value) {
        $arr2[$key] = ($value);
    }
    array_push($arr1,$arr2);
}

function p($key) {
  $param=$_REQUEST[$key];
  if($param==''){
   $param=$_GET[$key];
  }
  $param = safe($param);
  return ($param);
}

function createCaptcha() {
    $str = "2,3,4,5,6,7,8,9";      //要显示的字符，可自己进行增删
    $list = explode(",", $str);
    $cmax = count($list) - 1;
    $verifyCode = '';
    for ( $i=0; $i < 4; $i++ ){
        $randnum = mt_rand(0, $cmax);
        $verifyCode .= $list[$randnum];           //取出字符，组合成为我们要的验证码字符
    }
    $_SESSION['captcha'] = $verifyCode;        //将字符放入SESSION中
}

function safe($s){ //安全过滤函数
 if(get_magic_quotes_gpc()){ $s=stripslashes($s); }
 $s=mysql_real_escape_string($s);
 return $s;
}

function now()
{
  return  date('y-m-d h:i:s',time());
}

///json return

function j($errCode="000",$msg="成功",$arr=array()) {
    $arr2 =  array(
        "errCode"=>$errCode,
        "msg"=>$msg,
    );
    if($arr==null){
        $arr = array();
    }
    $arr3=array_merge($arr,$arr2);
    return json_encode($arr3);
}