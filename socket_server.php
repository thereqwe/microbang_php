<?php
require_once  "Mysql.php";
$db = new MbMysql("127.0.0.1","root","123","micro_bang","","UTF8");
//error_reporting( E_ALL );
set_time_limit( 0 );
ob_implicit_flush();
$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
if ( $socket === false ) {
    echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
}
$ok = socket_bind( $socket, '30.97.16.232', 10006 );
if ( $ok === false ) {
    echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) );
}
while ( true ) {
    $from = "";
    $port = 0;
    socket_recvfrom( $socket, $buf,1024, 0, $from, $port );
    $json = json_decode($buf,true);
    $text = $json["msg"];
    $from_mid = $json["mid"];
    $to_mid = $json["to_mid"];
    $now = now();
    $db->insert("mb_msg","msg,create_time,type,from_mid,to_mid,is_finished","'{$text}','{$now}',1,'$from_mid','$to_mid',0");
    var_dump($buf);
    usleep( 1000 );
}

function now()
{
    return  date('y-m-d h:i:s',time());
}
