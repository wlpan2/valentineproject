<?php
session_start(); 
// 取得系統組態
include ("configure.php");



// 連結資料庫
include ("connect_db.php");


//Obtain Login Information
$location = $_POST['location'];
$pin = $_POST['pin'];


// 比對其帳號與密碼
$sql = "SELECT user_id,user_name FROM users WHERE pin = $pin";
echo $sql;
//$rs = mysql_db_query($cfgDatabaseName, $sql, $link);
$rs = mysql_query($sql);
echo $rs;

$nT = mysql_num_rows($rs);

echo $nT;
// 依檢查結果分別導向主作業畫面與錯誤警告畫面
	if ( $nT==1 ) {
  		list($userid, $username) = mysql_fetch_row($rs);

   //設定 session 變數之初值
  		$_SESSION["username_ses"]  = $username;
  		$_SESSION["location_ses"]  = $location;
  		$_SESSION["userid_ses"] = $userid;
  		$_SESSION["progress_ses"] = 'n';
		$_SESSION["message_ses"] = "Login In Successful.";
  		Header("location:register.php");
  		exit;
	}
	else {
  		Header("location:main.html");
  		exit;
	}
?>
