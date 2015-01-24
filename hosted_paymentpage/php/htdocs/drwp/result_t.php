<html>
 <head>
  <title>PHP Result</title>
  <link rel="stylesheet" type="text/css" href="/drwp/style.css" />
 </head>
 <body>
 <?php 
 
	require_once "DigitalRiver/PaymentPage/PageHelper.php";
	require_once "DigitalRiver/PaymentPage/StatusCodeHelper.php";

	echo '<h2>Hello DR World</h2>';
 
	// print_r($_GET);
 
	$creq = $_GET['response'];	
	$mid = $_GET['m'];	
	$tst = $_GET['t'];	
	// $caller = $_GET['c'];	
	
	if (array_key_exists ( 'd' , $_GET )) {
		$dbg = $_GET['d'];
	} else {
		$dbg = FALSE;
	}
	$help = new DigitalRiver_PaymentPage_PageHelper($mid,$tst) ;
	$drparams = $help->decrypt($creq);
	
	// trying to build a back button to return to demo page. hopefully I can also increment the orderid
	//print '<a href="'.$caller.'" id="drPP"><img style="position:relative;top:10;margin:auto;border:0px;" src="/drwp/image/back.png"></a>'
	
	print '<div id="TBL1">' ;
	
	foreach ($drparams as $key => $val) {
		print "<p>$key = $val</p>\n";
	} 

	$code = $drparams["ClientAnswerCode"];
	$chelp = new DigitalRiver_Payment_StatusCodeHelper() ;
	$message = $chelp->getMessage($code);
	print "<p>Message: $message</p>\n";

	print '</div>' ;

?>
 </body>
</html> 