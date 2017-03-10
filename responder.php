<?
error_reporting("E_ALL");
require_once  "Mysql.php";
 $db = new MbMysql("127.0.0.1","root","123","micro_bang","","UTF8");
 $now = now();
 $action = p("action");
 if($action==""){
    die(j("001","action can not be empty"));
 }
 /***********************************/
 if($action == "register"){
    $name = p("name");
    $pwd = p("pwd");
    $rst = $db->query("select * from mb_member where name = '$name' ");
    $num  = mysql_num_rows($rst);
    if($num>0){
        echo j("003","用户名已经被注册!");
    }else{
        $time = now();
        $db->insert("mb_member","name,pwd,create_time","'$name','$pwd','$time'");
        echo j("000","注册成功");
    }
 }elseif($action== "login"){
     $name = p("name");
     $pwd = p("pwd");
     $sql = "select * from mb_member where name = '$name' and pwd='$pwd'";
     $rst = $db->query($sql);
     $num  = mysql_num_rows($rst);
     if($num>0) {
         while($row = $db->fetch_array($rst)) {
             $arr = array(
                 "mid" => $row["mid"],
                 "nickname" => $row["nickname"],
                 "avatar_url" => $row["avatar_url"]
             );
         }
         $db->update("mb_member","is_online=1","name='{$name}' and pwd = '$pwd' limit 1");
         echo j("000", "登陆成功",$arr);
     }else{
         echo j("004", "用户名或密码错误");
     }
 }elseif($action== "logout"){
     $mid = p("mid");
     if($mid==''){
        echo j("006","mid can't be empty");
        return;
     }
     $db->update("mb_friend","is_online=0","mid='$mid' limit 1");
     echo j("000","logout succ");
 }elseif($action== "addFriend"){
     $mid = p("mid");
     $friend_name = p("friend_name");
     if($mid==''||$friend_name==''){
         echo j("006","param can't be empty");
         return;
     }

     $db->query("select * from mb_member where name = '$friend_name' and mid!=$mid");
     if($db->db_num_rows()<=0){
         echo j("009", "抱歉,无此用户!");
     }else {
         $row = $db->fetch_array();
         $friend_mid = $row["mid"];
         $db->query("select * from mb_friend where mid = $mid and friend_mid = $friend_mid");
         if($db->db_num_rows()>=1){
             echo j("010", "已经是你好友啦!");
             return;
         }else {
             $db->insert("mb_friend", "mid,friend_mid,create_time", "$mid,$friend_mid,'$now'");
             echo j("000", "添加成功");
             return;
         }
     }
 }elseif($action== "removeFriend"){
     $mid = p("mid");
     $friend_mid = p("friend_mid");
     if($mid==''||$friend_mid==''){
         echo j("006","param err");
         return;
     }
     $db->delete("mb_friend","mid=$mid and friend_mid=$friend_mid");
     echo j("000","add friend succ");
 }elseif($action== "getFriendList"){
    $mid = p("mid");
     if($mid==''){
         echo j("009","参数错误");
         return;
     }
     $rst = $db->query("select * from mb_friend as t1 left join mb_member as t2 on t1.friend_mid = t2.mid
                         where t1.mid=$mid");
     $arr = array();
     while($row=$db->fetch_array()){
         array_push($arr,array(
             "create_time"=>$row["create_time"],
             "friend_mid"=>$row["friend_mid"],
             "nickname"=>$row["nickname"],
             "avatar_url"=>$row["avatar_url"]
         ));
     }
   //  var_dump($arr);
     $data = array(
         "data"=>$arr
     );
     echo  j("000","succ",$data);
 }elseif($action== "getProfile"){
     $mid = p("mid");
     if($mid==""){
         echo j("011","mid empty");
         return;
     }
     $db->query("select * from mb_member where mid=$mid limit 1");
     $row = $db->fetch_array();
     $arr = array(
         "nickname"=>$row["nickname"],
         "avatar_url"=>$row["avatar_url"]
     );
     echo  j("000","succ",$arr);
 }elseif($action== "getNewMsg"){
     $mid = p("mid");
     $arr = array();
     $rst = $db->query("select * from mb_msg where to_mid='$mid'  and is_finished=0");
     while($row=$db->fetch_array($rst)){
         $id = $row["id"];
         array_push($arr,array(
             "from_mid"=>$row["from_mid"],
             "to_mid"=>$row["to_mid"],
             "msg"=>urldecode($row["msg"]),
             "create_time"=>$row["create_time"]
         ));
     }
     $data = array(
         "data"=>$arr
     );
     $db->query("update mb_msg set is_finished=1 where to_mid='$mid' and is_finished=0");
     echo    j("000","succ",$data);
 }elseif($action== "editProfile"){
     $mid = p("mid"); //todo add more editable profile
     $nickname = p("nickname");
     $avatar_url = p("avatar_url");
     if($mid==''||$nickname==''){
         echo j("007","param err");
         return;
     }
     $db->update("mb_member","nickname='$nickname',avatar_url='$avatar_url'","mid=$mid");
     echo j("000","eidt profile succ");
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
