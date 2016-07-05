<?
error_reporting("E_ALL");
require_once  "Mysql.php";
 $db = new MbMysql("127.0.0.1","root","123","micro_bang","","UTF8");
 $action = p("action");
 if($action==""){
    echo j("001","action can not be empty");
 }
 /***********************************/
 if($action == "register"){///////////////////////////
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
         $arr = array("mid"=>$rst[0]["mid"]);
         echo j("000", "登陆成功",$arr);
     }else{
         echo j("004", "用户名或密码错误");
     }
 }elseif($action== "logout"){
 }elseif($action== "login1"){
 }elseif($action== "login2"){
 }elseif($action== "login3"){
 }else{
     echo j(null,"002","wrong action");
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
