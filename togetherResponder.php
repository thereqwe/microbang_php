<?
session_start();
include "TopSdk.php";
date_default_timezone_set('Asia/Shanghai');
$register_time = $time = time();
require_once  "Mysql.php";
 $db = new MbMysql("127.0.0.1","root","123","SYLTogether","","UTF8");
 $now = now();
 $action = p("action");
 $mid = p("mid");
 if($action==""){
    die(j("001","action can not be empty"));
 }



 /***********************************/
if ($action == "get_notification"){
   // echo ">>>>$mid<<<";
    $db->select("SYLNotification","*","to_mid='$mid' and is_handled=0");
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push($arr, array(
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
}else if($action=="get_nick_name") {
    $sql = "select * from SYLMember where mid=$mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push($arr, array(
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
        array_push($arr, array(
            "slogan"=>$row["slogan"]
        ));
    }
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
}else if($action=="get_profile") {
    $sql = "select * from SYLMember as t1 LEFT  JOIN SYLPersonality as t2 on t1.personality_idx=t2.personality_idx
 where t1.mid=$mid";
    $db->query($sql);
    $arr = array();
    while($row=$db->fetch_array()) {
        array_push($arr, array(
            "avatar_url" => $row["avatar_url"],
            "sex" => $row["sex"],
            "personality_text"=>$row["personality_text"],
            "height"=>$row["height"],
            "slogan"=>$row["slogan"],
            "age"=>$row["age"],
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
    //$resp = $c->execute($req);
    echo  j("000","{$_SESSION['captcha']}",array("captcha0"=>$_SESSION['captcha'],"resp"=>$resp));
    return;
}else if($action=="get_friend_list"){
    $arr = array();
    $db->query("select * from SYLFriend as t1 LEFT JOIN  SYLMember as t2 on t1.from_mid=t2.mid where t1.to_mid = $mid
UNION
    select * from SYLFriend as t1 LEFT JOIN  SYLMember as t2 on t1.to_mid=t2.mid where t1.from_mid = $mid
     order by nick_name");
    while($row=$db->fetch_array()) {
        array_push($arr, array(
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
     $from_nick_name = p("from_nick_name");
     $to_nick_name = p("to_nick_name");
     $db->select("SYLMember","mid","nick_name = '$to_nick_name' limit 1");
     while($row=$db->fetch_array()) {
         $to_mid = $row["mid"];
     }
     $type = 2;
//        echo "--->$to_mid<--";
     $json_data = urlencode(json_encode(array("from_mid"=>$from_mid,"to_mid"=>$to_mid,
         "from_nick_name"=>$from_nick_name,"to_nick_name"=>$to_nick_name)));
     $create_time = time();
     if($to_mid==''){
         echo  j("109","此用户不存在",$data);
     }else {
         $db->insert("SYLNotification", "title,text_content,json_data,type,create_time,to_mid",
             "'好友申请','{$from_nick_name}想成为你的好友','$json_data','$type','$create_time','$to_mid'");
         echo  j("000","succ",$data);
     }
     return;
}else if($action == "create_friendship") {
    $json_data =  urldecode(p("json_data"));
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
     $arr = array();
    $db->select("SYLMember","*","pwd='$pwd' and mobile = '$mobile' limit 1");
     while($row=$db->fetch_array()) {
         array_push($arr, array(
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
    $db->insert("SYLMember","nick_name,pwd,register_time,email,mobile","'$nick_name','$pwd','$register_time','$email','$mobile
    '");
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
     $info= p("info");
     $meet_lat= p("meet_lat");
     $meet_lng= p("meet_lng");
     $category_idx= p("category_idx");
     $meet_address = p("meet_address");
     $activity_address = p("activity_address");
     $db->insert("SYLActivity", "activity_title,start_time,info,mid,meet_lat,meet_lng,category_idx,meet_address,activity_address",
         "'$activity_title','$start_time','$info','$mid','$meet_lat','$meet_lng','$category_idx','$meet_address','$activity_address'");
     echo j("000", "succ");
     return;
 }else if($action == "get_category"){
    $rst =$db->select("SYLCategory");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push($arr,array(
            "idx"=>$row["category_idx"],
            "title"=>$row["category_title"],
            "category_level"=>$row["category_level"],
            "parent_idx"=>$row["parent_idx"]
        ));
    }
    //  var_dump($arr);
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
         return;
 }else if($action == "get_my_activity_list"){
    $rst =$db->query("select * from  SYLTogether.SYLActivity as t1
      LEFT  JOIN SYLTogether.SYLCategory as t2
      on t1.category_idx=t2.category_idx where 1");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push($arr,array(
            "activity_title"=>$row["activity_title"],
            "address"=>$row["address"],
            "activity_idx"=>$row["activity_idx"],
            "category_title"=>$row["category_title"],
            "start_time"=>$row["start_time"]
        ));
    }
   //   var_dump($arr);
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else if($action == "get_near_activity_coordinate"){
    $lat = p("lat");
    $lng = p("lng");
    $rst =$db->query("SELECT *,
        (POWER(MOD(ABS(meet_lng - $lng),360),2) + POWER(ABS(meet_lat - $lat),2)) AS distance
        FROM SYLActivity as t1 left JOIN  SYLCategory as t2 on t1.category_idx=t2.category_idx
        LEFT  join SYLMember as t3 on t1.mid=t3.mid
        ORDER BY distance LIMIT 100");
    $arr = array();
    while($row=$db->fetch_array()){
        array_push($arr,array(
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
        ));
    }
    //   var_dump($arr);
    $data = array(
        "data"=>$arr
    );
    echo  j("000","succ",$data);
    return;
}else{
     die(j("002","wrong action"));
 }
/////////////////////////////////////////////////////
//get or post param
function p($key)
{
  $param=$_REQUEST[$key];
  if($param==''){
   $param=$_GET[$key];
  }
  $param = safe($param);
  return $param;
}

function createCaptcha(){
    $str = "2,3,4,5,6,7,8,9,a,b,c,d,f,g";      //要显示的字符，可自己进行增删
    $list = explode(",", $str);
    $cmax = count($list) - 1;
    $verifyCode = '';
    for ( $i=0; $i < 5; $i++ ){
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
function j($errCode="000",$msg="成功",$arr)
{
    $arr2 =  array(
        "errCode"=>$errCode,
        "msg"=>$msg
    );
    if($arr==null){
        $arr = array();
    }
    $arr3=array_merge($arr,$arr2);
    return json_encode($arr3);
}
