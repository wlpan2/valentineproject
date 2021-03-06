<?php
/*
=========================================================
Kaugebra Aviation and Technology Service (KATS) 2012

   alert.php
   
The application is to display current token operation, exclusively for Valentine Project 2012
==========================================================
APPROACHES
1. All information will be obtained from data.

2. Analysis will be performed in the following.
	2a. Progression line to be printed P1 -> P2, P2 -> P3, P3 -> P4
	2b. Time reminder for upcoming invoice tokens (particularly to P2 and P3 requirements.
	2c. Warn for irregularities (P2 P3 and P4 direct registration without P1, or no time information...)
	2d. Check for separation of pre-invoice and immediate invoices.
	
3. Display all of information in order of orders above.

4. Refresh this page in each minute.
==========================================================
Methodology of Twin-Token System

1. All invoices must be initialisated into tokens by processing stations before any further operations.
2. Each invoice has two tokens, they are the following: (First two white invoices respectively)
	2.1 Production token: first token to be used to create the product related to the invoice.
	2.2 Delivery token: second token to be used for delivery of that product.
3. When someone is holding that piece of token, he or she must oblige to follow task from the token related. For instance, 
	when the production token is held, the task of creating the product must be performed unless forwarded to others.
4. Tokens can be transferred at anytime given clear acknowledgement and communications are tendered.
5. When token of related of task has been fullfilled, it is essential to report to processing station for token relaxation.
6. Token relaxation may be lifted if new situation is arisen - for example the product is made incorrectly or incorrect develivery procedures are made.
7. For simplicity purpose, delivery token is always attached to production token until the production is completed.  
===========================================================
*/
// Obtain location parameter
 $location = intval($_GET['location']);

// obtain all information from database
	// 取得系統組態
	include ("configure.php");
	// 連結資料庫
	include ("connect_db.php");
	$p1 = array();
	$p2 = array();
	$p3 = array();
	$p4 = array();
	// Obtain P1
	($location? $sql = "SELECT i.invoice_no FROM p1 p, invoices i WHERE p.invoice_no=i.invoice_no AND allocated_place=$location ORDER BY invoice_no" : $sql = "SELECT invoice_no FROM p1 ORDER BY invoice_no");
	$rs = mysql_query($sql);
	$num_rows_p1 = mysql_num_rows($rs);

	for ( $i=0; $i<$num_rows_p1; $i++ ) {
	$p1[] =  mysql_fetch_row($rs);}
	
	// Obtain P2
	($location? $sql = "SELECT i.invoice_no FROM p2 p, invoices i WHERE p.invoice_no=i.invoice_no AND allocated_place=$location ORDER BY invoice_no" : $sql = "SELECT invoice_no FROM p2 ORDER BY invoice_no");
	$rs = mysql_query($sql);
	$num_rows_p2 = mysql_num_rows($rs);
	$p2[0] =  mysql_fetch_row($rs);
	for ( $i=1; $i<$num_rows_p2; $i++ ) {
	$p2[$i] =  mysql_fetch_row($rs);}
	
	// Obtain P3
	($location? $sql = "SELECT i.invoice_no FROM p3 p, invoices i WHERE p.invoice_no=i.invoice_no AND allocated_place=$location ORDER BY invoice_no" : $sql = "SELECT invoice_no FROM p3 ORDER BY invoice_no");
	$rs = mysql_query($sql);
	$num_rows_p3 = mysql_num_rows($rs);
	$p3[0] =  mysql_fetch_row($rs);
	for ( $i=1; $i<$num_rows_p3; $i++ ) {
	$p3[$i] =  mysql_fetch_row($rs);}
	
	// Obtain P4
	($location? $sql = "SELECT i.invoice_no FROM p4 p, invoices i WHERE p.invoice_no=i.invoice_no AND allocated_place=$location ORDER BY invoice_no" : $sql = "SELECT invoice_no FROM p4 ORDER BY invoice_no");
	$rs = mysql_query($sql);
	$num_rows_p4 = mysql_num_rows($rs);
	$p4[0] =  mysql_fetch_row($rs);
	for ( $i=1; $i<$num_rows_p4; $i++ ) {
	$p4[$i] =  mysql_fetch_row($rs);}
	
	// Obtain invoice information
	($location? $sql = "SELECT invoice_no, start_time, end_time FROM invoices WHERE allocated_place = $location ORDER BY invoice_no" : $sql = "SELECT invoice_no, start_time, end_time FROM invoices ORDER BY invoice_no");
	$rs = mysql_query($sql);
	$num_rows_invoice = mysql_num_rows($rs);
	
	for($i=0; $i<$num_rows_invoice; $i++) {
	$invoice[$i] = mysql_fetch_row($rs);}
	
	date_default_timezone_set('Asia/Macao');
	$current_time = time();
	
	// Convert Matrix to Array
	
	if ($p1[0][0] != null) $p1s[0] = $p1[0][0]; $p1s=array();
	if ($p2[0][0] != null) $p2s[0] = $p2[0][0]; $p2s=array();
	if ($p3[0][0] != null) $p3s[0] = $p3[0][0]; $p3s=array();
	if ($p4[0][0] != null) $p4s[0] = $p4[0][0]; $p4s=array();
	$pi = array();

	for($i=0; $i<$num_rows_p1; $i++) {
		$p1s[] = $p1[$i][0];
	}
	for($i=0; $i<$num_rows_p2; $i++) {
		$p2s[] = $p2[$i][0];
	}
	for($i=0; $i<$num_rows_p3; $i++) {
		$p3s[] = $p3[$i][0];
	}
	for($i=0; $i<$num_rows_p4; $i++) {
		$p4s[] = $p4[$i][0];
	}
	for($i=0; $i<sizeof($invoice); $i++) {
		$pi[] = $invoice[$i][0];
	}
        unset($p1);
        unset($p2);
	$p1 = $p1s;
	$p2 = $p2s;
	$p3 = $p3s;
	$p4 = $p4s;
	
// analysis of obtained data
	// Find irregularities
		// Register WITHOUT previous post
		$p2_ir = array();
		$p3_ir = array();
		// P2 without P1, 
		for ($i=0; $i<sizeof($p2); $i++ ) {
			$found = false;
			for ($j=0; $j<sizeof($p1); $j++ ) {
				if ($p2[$i] == $p1[$j]){
					// Find correct registration
					$found = true;
					break;
				}
			}
			if (!$found){
				$p2_ir[] = $p2[$i];
			}
		}
		
		// P3 without P2
		for ($i=0; $i<sizeof($p3); $i++ ) {
			$found = false;
			for ($j=0; $j<sizeof($p2); $j++ ) {
				if ($p3[$i] == $p2[$j]){
					// Find correct registration
					$found = true;
					break;
				}
			}
			if (!$found){
				$p3_ir[] = $p3[$i];
			}
		}
	
	
	// Eliminate repeated data

	//P1
	
	$p1 = array_unique($p1);
	$p1= array_values($p1);
		//P2
	$p2 = array_unique($p2);
	$p2 = array_values($p2);
	//P3
	$p3 = array_unique($p3);
	$p3 = array_values($p3);
	//P4
	$p4 = array_unique($p4);
	$p4 = array_values($p4);

	// Eliminate post repeats and null value
		//P1 -> P2

$p1_size = sizeof($p1);
		for ($i=0; $i<$p1_size; $i++ ) {

			for ($j=0; $j<=sizeof($p2); $j++ ) {
                               
				if ($p1[$i] == $p2[$j] && (array_key_exists($i, $p1))){
					unset($p1[$i]);
					break;
				}
			}
		}
		$p1 = array_values($p1);
$p2_size = sizeof($p2);
		//P2 -> P3
		for ($i=0; $i<$p2_size; $i++ ) {
			for ($j=0; $j<sizeof($p3); $j++ ) {
				if ($p2[$i] == $p3[$j] && (array_key_exists($i, $p2))){
					unset($p2[$i]);
					break;
				}
			}
		}
		$p2 = array_values($p2);
$p3_size = sizeof($p3);
		//P3 -> P4
		for ($i=0; $i<$p3_size; $i++ ) {
			for ($j=0; $j<sizeof($p4); $j++ ) {
				if ($p3[$i] == $p4[$j] && (array_key_exists($i, $p3))){
					unset($p3[$i]);
					break;
				}
			}
		}
		$p3 = array_values($p3);
$p2_size = sizeof($p2);
		//P2 -> P4
		for ($i=0; $i<$p2_size; $i++ ) {
			for ($j=0; $j<sizeof($p4); $j++ ) {
				if ($p2[$i] == $p4[$j] && (array_key_exists($i, $p2))){
					unset($p2[$i]);
					break;
				}
			}
		}
		$p2 = array_values($p2);
$p2_size = sizeof($p2);
		//P1 -> P4
		for ($i=0; $i<$p2_size; $i++ ) {
			for ($j=0; $j<sizeof($p4); $j++ ) {
				if ($p1[$i] == $p4[$j] && (array_key_exists($i, $p1))){
					unset($p1[$i]);
					break;
				}
			}
		}
		$p1 = array_values($p1);
		
	// Count progressions
	$num_rows_p1 = count($p1);
	$num_rows_p2 = count($p2);
	$num_rows_p3 = count($p3);
	$num_rows_p4 = count($p4);
	
	//Time alert
		/* 6 Types of variables:
			start in 0 minute
			start in 30 minutes
			start in 60 minutes
			end in 0 minute
			end in 30 minute
			end in 60 minute
			*/
	$startnow = array();
	$start30 = array();
	$start60 = array();
	$endnow = array();
	$end30 = array();
	$end60 = array();
	for($i=0; $i<sizeof($invoice); $i++) {
		// Compare each invoice with time
		if ($invoice[$i][1] - $current_time < 0 && !(in_array($invoice[$i][0], $p4 , $strict = null))){
			// Time is here	
			array_push($startnow, array(0 => $invoice[$i][0], 1 => $invoice[$i][1]));
			
		}elseif($invoice[$i][1] - $current_time < 1800 && !(in_array($invoice[$i][0], $p4 , $strict = null))) {
			// Half and hour to go
			array_push($start30, array(0 => $invoice[$i][0], 1 => $invoice[$i][1]));
			
		}elseif($invoice[$i][1] - $current_time < 3600 && !(in_array($invoice[$i][0], $p4 , $strict = null))) {
			// One Hour to go
			array_push($start60, array(0 => $invoice[$i][0], 1 => $invoice[$i][1]));
		}
		
		
		if ($invoice[$i][2] - $current_time < 0 && !(in_array($invoice[$i][0], $p4 , $strict = null))){
			// Time is here	
			array_push($endnow, array(0 => $invoice[$i][0], 1 => $invoice[$i][2]));
			
		}elseif($invoice[$i][2] - $current_time < 1800 && !(in_array($invoice[$i][0], $p4 , $strict = null))) {
			// Half and hour to go
			array_push($end30, array(0 => $invoice[$i][0], 1 => $invoice[$i][2]));
			
			
		}elseif($invoice[$i][2] - $current_time < 3600 && !(in_array($invoice[$i][0], $p4 , $strict = null))) {
			// One Hour to go
			array_push($end60, array(0 => $invoice[$i][0], 1 => $invoice[$i][2]));
		}
	}
	// merge entire invoice table
	$pt = array();
	$pt = array_merge($p1, $p2, $p3, $p4, $pi);
	$pt = array_unique($pt);
        sort($pt);
	$pt = array_values($pt);

// Display them respectively (test platform)
/*
	 print_r($p1);
	 echo '<br>';
	 print_r($p2);
	 echo '<br>';
	 print_r($p3);
	 echo '<br>';
	 print_r($p4);
	 echo '<br>';
	 print_r($p2_ir);
	 echo '<br>';
	 print_r($p3_ir);
	 echo '<br>';
	 print_r($invoice);
	 echo '<br>startnow = ';
	 print_r($startnow);
	 echo '<br>start30 = ';
	 print_r($start30);
	 echo '<br>start60 = ';
	 print_r($start60);
	 echo '<br>endnow = ';
	 print_r($endnow);
	 echo '<br>end30 = ';
	 print_r($end30);
	 echo '<br>end60 = ';
	 print_r($end60);
        echo '<br>invoice list =';
	 print_r($pt);


echo '<br>';
echo 'P1='.$num_rows_p1.'<br>';
echo 'P2='.$num_rows_p2.'<br>';
echo 'P3='.$num_rows_p3.'<br>';
echo 'P4='.$num_rows_p4.'<br>';

*/

// Dislpay Platform
echo '<html><head><meta http-equiv="refresh" content="20" ><link rel="stylesheet" type="text/css" href="info.css" /><title>Valentine Project Display Platform</title></head>
<body><h1>PROGRESSION REPORT</h1><table><tr><td>
<h2>POST REGISTRATIONS</h2></td><td><h2>TIME ALERTS</h2></td></tr><td>
<table border = 1>
<tr><td>P1</td><td>P2</td><td>P3</td><td>P4</td></tr><tr><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($p1); $i++) {
	echo '<tr><td>'.$p1[$i].'</td></tr>';
}
echo '</table></td><td>';

echo '<table border = 0>';
for($i = 0;$i<sizeof($p2); $i++) {
	echo '<tr><td>'.$p2[$i].'</td></tr>';
}
echo '</table></td><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($p3); $i++) {
	echo '<tr><td>'.$p3[$i].'</td></tr>';
}
echo '</table></td><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($p4); $i++) {
	echo '<tr><td>'.$p4[$i].'</td></tr>';
}
echo '</table><tr><td>'.$num_rows_p1.'</td><td>'.$num_rows_p2.'</td><td>'.$num_rows_p3.'</td><td>'.$num_rows_p4.'</td></tr>
<tr><td colspan ="3">Working Now = '.($num_rows_p1+$num_rows_p2+$num_rows_p3).'</td></tr>
<tr><td colspan ="4">TRI = '.sizeof($invoice).'</td></tr>
<tr><td colspan ="4">TI = '.($num_rows_p1+$num_rows_p2+$num_rows_p3+$num_rows_p4).'</td></tr>';
echo '</td></tr></table></td>


<td>
<table border = 1>
<tr><td>Start Now</td><td>Start 30 Minutes</td><td>Start 60 Minutes</td></tr><tr><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($startnow); $i++) {
	echo '<tr><td>'.$startnow[$i][0].'</td><td>'.date("H:i m/d",$startnow[$i][1]).'</tr>';
}
echo '</table></td><td>';

echo '<table border = 0>';
for($i = 0;$i<sizeof($start30); $i++) {
	echo '<tr><td>'.$start30[$i][0].'</td><td>'.date("H:i m/d",$start30[$i][1]).'</tr>';
}
echo '</table></td><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($start60); $i++) {
	echo '<tr><td>'.$start60[$i][0].'</td><td>'.date("H:i m/d",$start60[$i][1]).'</tr>';
}
echo '</table></td></tr>';
echo '<tr><td>'.sizeof($startnow).'</td><td>'.sizeof($start30).'</td><td>'.sizeof($start60).'</td></tr>';
echo '</td></tr>
<tr><td>END NOW</td><td>END 30 Minutes</td><td>END 60 Minutes</td></tr><tr><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($endnow); $i++) {
	echo '<tr><td>'.$endnow[$i][0].'</td><td>'.date("H:i m/d",$endnow[$i][1]).'</tr>';
}
echo '</table></td><td>';

echo '<table border = 0>';
for($i = 0;$i<sizeof($end30); $i++) {
	echo '<tr><td>'.$end30[$i][0].'</td><td>'.date("H:i m/d",$end30[$i][1]).'</tr>';
}
echo '</table></td><td>';
echo '<table border = 0>';
for($i = 0;$i<sizeof($end60); $i++) {
	echo '<tr><td>'.$end60[$i][0].'</td><td>'.date("H:i m/d",$end60[$i][1]).'</tr>';
}
echo '</table></td></tr>';
echo '<tr><td>'.sizeof($endnow).'</td><td>'.sizeof($end30).'</td><td>'.sizeof($end60).'</td></tr>';
echo '</td></tr></table>
</td></table>
<h2>IRREGULARITIES</h2><table border="1">
<tr><td>P2 ONLY</td><td>P3 ONLY</td></tr>
<tr><td><table border="0">';
for($i = 0;$i<sizeof($p2_ir); $i++) {
	echo '<tr><td>'.$p2_ir[$i].'</td></tr>';}
echo '</table></td><td>
<table border="0">';
for($i = 0;$i<sizeof($p3_ir); $i++) {
	echo '<tr><td>'.$p3_ir[$i].'</td></tr>';}
echo '</table></td></tr><tr><td colspan ="2"><b>MISSING INVOICES BETWEEN</b></td></tr><tr><td colspan ="2"><table border = "0">';

for($i=0; $i<sizeof($pt)-1; $i++){
if (intval($pt[$i+1])-intval($pt[$i]) != 1){
echo '<tr><td><h2>'.$pt[$i].' AND ';


echo $pt[$i+1].'</h2></td></tr>';

}
}
echo '</table></td></tr></table><br><hr>Updated Time: '.
date("d/m/y H:i:s",time()).'
</body></html>';
print_r($pt);
?>