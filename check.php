<?php

// 設定
$red ="<img src=\"red.png\" width=25>";
$green="<img src=\"green.png\" width=25>";

$html_line = ''; //表の作成で利用

$user_count = 8; //受験者数


//$userは表作成のところで使用するだけ
$user[1]["name"]="A";
$user[2]["name"]="B";
$user[3]["name"]="C";
$user[4]["name"]="D";
$user[5]["name"]="E";
$user[6]["name"]="F";
$user[7]["name"]="G";
$user[8]["name"]="H";
$user[9]["name"]="I";

$user[1]["num"]="ゼッケン３";
$user[2]["num"]="ゼッケン４";
$user[3]["num"]="ゼッケン１";
$user[4]["num"]="ゼッケン８";
$user[5]["num"]="ゼッケン６";
$user[6]["num"]="ゼッケン５";
$user[7]["num"]="ゼッケン７";
$user[8]["num"]="ゼッケン２";
$user[9]["num"]="ゼッケン∞";


//check項目
$port["http"]=80;
$port["smtp"]=25;
$port["https"]=443;
$port["dns"]=53;

$host1[1]["check"]=array("http","https","smtp","dns");
$host1[2]["check"]=array("smtp","dns");

//タイムアウトは1秒
$timeout=1;



 // check_ping-----------------------------------------
function check_ping($host,&$rval){
$rval='';
global $red , $green ; //表示用変数を使う
$ping = exec(sprintf('ping -c 1 -W 1 %s',$host),$res,$rval);

//pingコマンドは戻り値0が成功
if($rval != 0){
  return $red; //失敗した場合pingを赤にする
 }else{
  return $green; //成功時は緑
 }

}

 //check_dns-----------------------------------------
 
function check_dns($host,$dns_svr,$ping){
global $red , $green ; //表示用変数を使う
if($ping != 0){
  return $red;
}

exec(sprintf('dig +time=1 +tries=1 @%s %s',$host,$dns_svr),$res,$rval);

  if ( $rval != 0 ) {
      return $red;
  } else{
      return $green;
  }
}

 //check_tcp-----------------------------------------

function check_tcp($host,$port){
$timeout = 1; //1秒でタイムアウト
global $red , $green ; //表示用変数を使う

  $rval = fsockopen($host, $port, $errno, $errstr, $timeout);

  if ( ! $rval ) {
      return $red;
  } else{
      return $green;
  }
}

 //check_prot-----------------------------------------

function check_prot($host,$port,$key,$dns_solve,$ping){
global $red , $green;

//ping以外の確認
foreach($port as $prot => $num){

//pingが失敗した場合は各プロトコルを赤にする
if($ping != 0){
  $value[$prot]=$red;

 }else{
 
  //pingが成功したからDNSとその他のチェック
   if($prot == "dns"){

    $value[$prot]=check_dns($host,$dns_solve,$ping);
   }else{
    $value[$prot]= check_tcp($host,$num);
   
   } //dns以外の閉じる
 
 } //ping成功時の閉じる
 
 } //foreachの閉じる

 return $value;
 }
 
 //check_host-----------------------------------------
 function check_host(&$host,$dns_solve,$user){
	 foreach($host as $key => $host1){
	 $ping = '';
	 $port = port_num($host1);
	 $ping_value = check_ping($host1["ip"],$ping);
	 $host[$key]["value"]= check_prot($host1["ip"],$port,$user,$dns_solve,$ping);
	 $host[$key]["value"]["ping"]= $ping_value;
	  }
 }
 

 //port_num-----------------------------------------
 
 function port_num($host){
 global $port;
	 foreach($host["check"] as $check){
	  $prot[$check]=$port[$check];
	 }
  return $prot;
 }
 
 
 //-----------------------------------------
 //    スタート
 //-----------------------------------------

for ($key = 1; $key <= $user_count; $key++){

//DCのサーバー
$host1[1]["ip"] = "210.1.".$key.".11";
$host1[2]["ip"] = "210.1.".$key.".20";
$dns_solve = sprintf("www.netad%02d.it.jp",$key);

check_host($host1,$dns_solve,$key);

$html_line .= "<tr>\n";
$html_line .= "<td align=\"center\">".$key."：".$user[$key]["name"]."<br>(".$user[$key]["num"].")</td><td align=\"center\">".$host1[1]["value"]["ping"]."</td><td align=\"center\">".$host1[1]["value"]["http"]."</td><td align=\"center\">".$host1[1]["value"]["https"]."</td><td align=\"center\">".$host1[1]["value"]["smtp"]."</td><td align=\"center\">".$host1[1]["value"]["dns"]."</td><td>".$host1[2]["value"]["ping"]."</td><td align=\"center\">".$host1[2]["value"]["smtp"]."</td><td align=\"center\">".$host1[2]["value"]["dns"]."</td>\n";
$html_line .= "</tr>\n";

}

//　全体のHTMLを生成する

$html = "<html>\n<head><meta charset=\"UTF-8\">\n<meta http-equiv=\"Refresh\" content=\"10\">\n<style>table{
 margin-right : auto;
 margin-left : auto
}
</style>\n</head>\n<body>\n";

$date = date('H時i分s秒');
$html .= $date."現在の状況<br>※ネットワーク越しに行う、簡易な状況確認で、進捗具合の目安となります。<br>基本動作が確認できると赤から緑に変わります。<br><br>";

$html .= "<table border=1>\n";
$html .= "<tr><td rowspan=2 align=\"center\">席番号</td><td colspan=5 align=\"center\">データセンター</td><td colspan=3 align=\"center\">本部</td></tr>\n";
$html .= "<tr><td>Ping</td><td>Web(HTTP)</td><td>Web(HTTPS)</td><td>メール</td><td>DNS</td><td>Ping</td><td>メール</td><td>DNS</td></tr>\n";

$html .= $html_line;

$html .= "</body></html>";

echo $html;
$file = 'index.html';

file_put_contents($file,$html);

?>
