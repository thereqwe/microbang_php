<?
 require_once  "Mysql.php";
 $db = new MbMysql("30.97.16.232","root","123","micro_bang","","UTF8");
 $action = p("action");
 if($action==""){
   echo json_encode(array("errCode"=>"001","msg"=>"no action"));
 }
 if($action == "register"){
  $name = p("name");
  $pwd = p("pwd");
  echo "$pwd $name";
 }elseif($action== "login"){

 }else{
   echo json_encode(array("errCode"=>"002","msg"=>"wrong action"));
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
