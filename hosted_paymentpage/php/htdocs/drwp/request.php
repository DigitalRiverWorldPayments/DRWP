<html>
 <head>
  <title>PHP Test</title>
 </head>
 <body>
 <?php echo '<p>Hello DR World</p>';

/**
 * @author      Simon Britnell <sbritnell@digitalriver.com>
 * @copyright   2013 Digital River Inc.
 * @version     0.02
 */ 
	require_once "DigitalRiver/PaymentPage.php";

	$query = $_SERVER['PHP_SELF'];
	$path = pathinfo( $query ); 	
	$dirname = $path['dirname'];
	if ($dirname != '/') $dirname = $dirname . '/' ;
	$returnurl = 'http://localhost'.$dirname.'result.php?no=1' ; // add dummy param as helper appends creq with '&'

	$params = array(
		'MerchantId'		=> "1234567890", // MUSt insert merchantID here !!!!!!
		'TxChannel'			=> "Web Online", // "(Web Online|Mail|Telephone|Fax|FaceToFace|Cash register)"
		'POSId'				=> '0',
		'OrderId'			=> "TestOrderID_fromPHP-0006", // remember to update  this OrderId for new payments !!
		'OrderDesc'			=> 'An example order submitted from a PHP webpage',
		'Amount'			=> "10.0",
		'Currency'			=> "EUR",		   // SEK EUR GBP
		'CardTxType'		=> "authorize",		   // authorize debit refund
		'Country'			=> "UK",
		'Language'			=> "en",		// sv
		'ReturnUrl'			=> $returnurl,
		'StoreFlag'			=> "0", //  0=Do not store, 1=Store and process, 2=Store only
		// 'TemplateRef' => "TestAddPaymentMethod_UK_GB", // use TemplateRef if required
		'CustomerFName'		=> 'Simon',
		'CustomerLName'		=> 'Britnell',
    ) ;


	if ($params['MerchantId'] == '1234567890')
		echo '<p>Warning: MerchantId has not been edited to use the correct value<br>This URL will fail.' ;

	
	$help = new PageHelper() ;
	$url = $help->encrypt_url($params);
	echo '<p><br><a href="'.$url.'">Go to payment page</a></p>' ;
 ?>
 </body>
</html> 