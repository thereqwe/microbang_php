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
         $rst = $db->fetch_array();
         $arr = array(
             "mid"=>$rst[0]["mid"],
             "nickname"=>$rst[0]["nickname"]
         );
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
     $friend_mid = p("friend_mid");
     if($mid==''||$friend_mid==''){
         echo j("006","mid can't be empty");
         return;
     }
     $db->insert("mb_friend","mid,friend_mid,create_time","$mid,$friend_mid,'$now'");
     echo j("000","add friend succ");
 }elseif($action== "removeFriend"){
     $mid = p("mid");
     $friend_mid = p("friend_mid");
     if($mid==''||$friend_mid==''){
         echo j("006","param err");
         return;
     }
     $db->delete("mb_friend","mid=$mid and friend_mid=$friend_mid");
     echo j("000","add friend succ");
 }elseif($action== "editProfile"){
     $mid = p("mid"); //todo add more editable profile
     $nickname = p("nickname");
     if($mid==''||$nickname==''){
         echo j("007","param err");
         return;
     }
     $db->update("mb_member","nickname='$nickname'","mid=$mid");
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
