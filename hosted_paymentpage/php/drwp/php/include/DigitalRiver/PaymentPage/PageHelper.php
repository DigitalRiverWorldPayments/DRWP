<?php

// define if openSSL cert reading bugs require workarounds
define('OPENSSLBUG1', true) ; // problem reading public key from cert
define('OPENSSLBUG2', true) ; // problem reading serial from cert

class DigitalRiver_PaymentPage_PageHelper
{
    private $encryptionVector       = array( -1, 2, -3, 4, -5, 6, -7, 8, -9, 10, -11, 12, -13, 14, -15, 16 );
    private $signature              = null;
    private $encryptionKey;

	private $merchantCert			= null;
    protected $merchantKey			= null;
    private $merchantId				= null;

    private $isTEST					= TRUE;
    private $doDEBUG				= FALSE;

    private $digriverKey			= null;
    private $digriverCert			= null;
    private $digriverSerial			= null;

    private $paymentPageUrl			= null;
	
    const CERT_BASE					= "D:\\xampp183\\drwp\\certs\\";
    const CERT_DIR_PROD				= "prod\\";
    const CERT_DIR_TEST				= "test\\";
	
	// not actually used
    const CERT_DIR_DR				= "digriver\\";
    const CERT_DIR_MERCH			= "merchant\\";

    const MERCHANT_CERT_NAME		= "merchant_cert.pem";
    const MERCHANT_KEY_NAME			= "merchant_key.pem";
    const DIGRIVER_KEY_NAME			= "netgiro_key.pem" ; 
    const DIGRIVER_CERT_NAME		= "netgiro_cert.pem" ;
    const DIGRIVER_SERIAL_PROD		= 4 ;
		
	const PAGE_HOST_PROD   = "https://secure.payments.digitalriver.com" ;
	const PAGE_HOST_TEST   = "https://testpage.payments.digitalriver.com" ;

	private $REQUEST_MAP = array(
	'MerchantId'       => 'A',
	'SubmerchantId'    => 'B',
	'POSId'            => 'C',
	'TxChannel'        => 'D',
	'CardTxType'       => 'E',
	'Token'            => 'F',
	'OrderId'          => 'G',
	'OrderDesc'        => 'H',
	'OrderDetailDesc'  => 'I',
	'Amount'           => 'J',
	'Currency'         => 'K',
	'VATAmount'        => 'L',
	'VATRate'          => 'M',
	'CustomerFName'    => 'N',
	'CustomerLName'    => 'O',
	'CustomerAddr'     => 'P',
	'CustomerPOBox'    => 'Q',
	'CustomerZip'      => 'R',
	'CustomerAptNo'    => 'S',
	'Country'          => 'T',
	'Language'         => 'U',
	'ReturnUrl'        => 'V',
	'Timeout'          => 'W',
	'AdditionalParams' => 'Y',
	'PaymentMethodId'  => 'Z',
	'StoreFlag'        => 'AA',
	'TemplateRef'      => 'AB',
	'Version'          => 'AC',
	'CompanyTaxId'     => 'AAF',
	'BillingFirstName'			=> 'AQ',
	'BillingLastName'           => 'AP',
	'BillingFullName'           => 'AR',
	'BillingAddressLine1'       => 'AG',
	'BillingAddressLine2'       => 'AH',
	'BillingAddressLine3'       => 'BJ',
	'BillingCity'				=> 'AI',
	'BillingStateProvince'		=> 'AJ',
	'BillingZipCode'			=> 'AK',
	'BillingCountry'			=> 'AL',
	'BillingEmailAddress'       => 'AM',
	'BillingPhone'				=> 'AN',
	'BillingMobilePhone'        => 'AO',
	'BillingSSN'				=> 'AAE',
	'BillingBuyerType'          => 'BE',
	'BillingBuyerVATNo'         => 'BD',
	'BillingCompanyName'        => 'BC',
	
	'ShippingFirstName'			=> 'AAC',
	'ShippingLastName'          => 'AAB',
	'ShippingFullName'          => 'AAD',
	'ShippingAddressLine1'      => 'AS',
	'ShippingAddressLine2'      => 'AT',
	'ShippingAddressLine3'      => 'BH',
	'ShippingCity'				=> 'AU',
	'ShippingStateProvince'     => 'AV',
	'ShippingZipCode'           => 'AW',
	'ShippingCountry'           => 'AX',
	'ShippingEmailAddress'      => 'AY',
	'ShippingPhone'				=> 'AZ',
	'ShippingMobilePhone'       => 'AAA',
	'ShippingCompanyName'       => 'BG',
	'RecurringType'       => 'EA',
	
	
	'CompanyResponsibleFullName'    => 'CN' ,
	'CompanyResponsibleBirthDate'   => 'CB'  ,
	'CompanyResponsibleVATNumber'   => 'CV' ,
	'BirthDate'   					=> 'BT'  ,

	
	);
	
	private $RESULT_MAP = array(
	'A' => 'MerchantId',
	'B' => 'TxStatus',
	'C' => 'TransactionId',
	'D' => 'PaymentMethod',
	'E' => 'OrderId',
	'F' => 'Timestamp',
	'G' => 'VResId',
	'H' => 'PAResId',
	'I' => '3DSecureStatus',
	'J' => 'POSId',
	'K' => 'CardTxType',
	'L' => 'CardTxId',
	'M' => 'CardType',
	'N' => 'StoreTxId',
	'O' => 'Last4Digits',
	'P' => 'CardExpDate',
	'Q' => 'StoreCardType',
	'R' => 'IbpTxId',
	'S' => 'IbpTxType',
	'T' => 'RedirectedStatus',
	'U' => 'CardNumberMasked',
	'V' => 'MaskedAccountNumber',
	'W' => 'ExpirationDate',
	'X' => 'EftReferenceId',
	'Y' => 'EftPaymentSlipUrl',
	'Z' => 'EftTrxId',
	'AA' => 'DirectDebitTrxId',
	'AB' => 'PayoutTrxId',
	'AC' => 'AvsAnswerCode',
	'AD' => 'AvsResponse',
	'AE' => 'AcquirerAnswerCode',
	'AF' => 'ClientAnswerCode',
    'AG' => 'CvAnswerCode',
	'AH' => 'CvResponse',
	'AI' => 'PaymentMethodName',
	'AJ' => 'AcquirerAuthCode',

	
);

	protected function _merchCertPath($name)
	{
		if($this->isTEST)
		{
			// return self::CERT_BASE.self::CERT_DIR_TEST.$this->merchantId."\\".$name;
			return self::CERT_BASE.self::CERT_DIR_TEST.$name;
		}
		else
		{
			return self::CERT_BASE.self::CERT_DIR_PROD.$this->merchantId."\\".$name;
		}
	}
	
	protected function _drCertPath($name)
	{
		if($this->isTEST)
		{
			return self::CERT_BASE.self::CERT_DIR_TEST.$name;
		}
		else
		{
			return self::CERT_BASE.self::CERT_DIR_PROD.$name;
		}
	}
	

	public function DigitalRiver_PaymentPage_PageHelper($merchantId, $isTEST=TRUE)
    {
		$this->initHelper($merchantId, $isTEST) ;
	}
	
	protected function initHelper($merchantId, $isTEST=TRUE)
    {
		$this->merchantId	= $merchantId;
		$this->isTEST 		= $isTEST;
		// print '<br>Dtest '.$this->isTEST.'<br>';
		
		$this->merchantCert		= $this->_merchCertPath(self::MERCHANT_CERT_NAME);
		$this->merchantKey		= $this->_merchCertPath(self::MERCHANT_KEY_NAME);
		$this->digriverKey		= $this->_drCertPath(self::DIGRIVER_KEY_NAME);
		$this->digriverCert		= $this->_drCertPath(self::DIGRIVER_CERT_NAME);
		if($this->isTEST)
		{
			$this->digriverSerial	= $this->_getCertificateSerial($this->digriverCert);
			$this->pageHost			= self::PAGE_HOST_TEST;
		}
		else
		{
			if(OPENSSLBUG2)
			{
				$this->digriverSerial	= self::DIGRIVER_SERIAL_PROD;
			} else {
				$this->digriverSerial	= $this->_getCertificateSerial($this->digriverCert);
			}
			$this->pageHost			= self::PAGE_HOST_PROD;
		}
	}
	
	public function merchantId()
	{
		return $this->merchantId;
	}
	
	public function setMerchantId($merchantId)
	{
		$this->merchantId = $merchantId;
	}
	
    public function encrypt($params)
    {
        $this->encryptionKey = $this->_generateKey(128);
		
        $str = $this->_prepareString($params);
        //$str = utf8_encode($str);
        $str = $this->_zipEncode($str);
		
        $str = $this->_encryptString($str);
        $this->_sign($str);
        $this->encryptionKey = $this->_encryptPubKey($this->encryptionKey);

        $bytes = $this->_generateBytesArr($str);
        $bytes = $this->_toByte($bytes);

        $strBytes = $this->_getStrFromBytes($bytes);
        $encBytes = base64_encode($strBytes);

        $encBytes = str_replace('+', '-', $encBytes);
	    $encBytes = str_replace('/', '_', $encBytes);

        return $encBytes;
    }

    protected function encrypt_test($params)
    {
        $this->encryptionKey = $this->_generateKey(128);
        $str = $this->_prepareString($params);

        $str = utf8_encode($str);
		echo '<p>E STR '.$str;
        $str = $this->_zipEncode($str);
        $str = $this->_encryptString($str);
		echo '<p>E ENC '.$str;
        // comment this out to see diff // $this->_sign($str);
        $this->encryptionKey = $this->_encryptPubKey($this->encryptionKey);

        $bytes = $this->_generateBytesArr($str);
        $bytes = $this->_toByte($bytes);
		//echo '<p>E BYTS '.var_dump($bytes);

        $strBytes = $this->_getStrFromBytes($bytes);
        echo '<p>E SBS '.$strBytes;
		$encBytes = base64_encode($strBytes);

        $encBytes = str_replace('+', '-', $encBytes);
	    $encBytes = str_replace('/', '_', $encBytes);

        return $encBytes;
    }

	public function encrypt_url($params)
    {
	
		$params['MerchantId'] = $this->merchantId;
		$drparams = $this->_prepareParams($params) ;
		// print '<p>ENCRYPT --- '.$this->merchantId.' DR - </p>' ;
		
		$creq = $this->encrypt($drparams) ;
		
		$url = $this->pageHost.'/pay/?creq='.$creq ;
		
        return $url;
    }

	public function encrypt_creq($params)
    {
		$drparams = $this->_prepareParams($params) ;
		$creq = $this->encrypt_test($drparams) ;
		
        return $creq;
    }

    public function decrypt($EncryptedStr)
    {
        $EncryptedStr = str_replace('-', '+', $EncryptedStr);
        $EncryptedStr = str_replace('_', '/', $EncryptedStr);

        $str = base64_decode($EncryptedStr);
        $strBytes = $this->_getBytesFromStr($str);
        $bytesArr = explode( ',', $strBytes );

        if ($bytesArr[0] != 4)
        {
            $str = $this->_zipDecode($str);
        }

        if (count($bytesArr) < 266)
        {
            print "Error: answer is too short!";
        }

        $decryptObject = $this->_parseObjects($str);
        $this->encryptionKey = $this->_decryptPubKey($decryptObject->key);

        $str = $this->_decryptString($decryptObject->encText);
        $str = $this->_zipDecode($str);

        if ($this->_verifySign($decryptObject->encText, $decryptObject->signature))
        {
            return $this->_parseAnswer($str);
        }
        else
        {
            return false;
        }
    }

    public function decrypt_creq($EncryptedStr)
    {
        $EncryptedStr = str_replace('-', '+', $EncryptedStr);
        $EncryptedStr = str_replace('_', '/', $EncryptedStr);

        $str = base64_decode($EncryptedStr);
        echo '<p>D SBS '.$str;

		// this seems to be missing
		$str = $this->_decryptString($str);
       echo '<p>D Str '.$str;

		$strBytes = $this->_getBytesFromStr($str);
        // echo '<p>SBTS '.$strBytes;
		$bytesArr = explode( ',', $strBytes );
		// echo '<br>D BYTS '.var_dump($bytesArr);
        if ($bytesArr[0] != 4)
        {
			echo '<br>zDEC';
            $str = $this->_zipDecode($str);
        }

        if (count($bytesArr) < 266)
        {
            print "Error: answer is too short!";
        }

		
        $decryptObject = $this->_parseObjects($str);
		// echo '<p><br>DOBJ '.var_dump($decryptObject);
		echo '<p><br>D ENC '.$decryptObject->encText;
        $this->encryptionKey = $this->_decryptPubPubKey($decryptObject->key);

        $str = $this->_decryptString($decryptObject->encText);
		echo '<p>D STR = '.$str.'<p>';
        $str = $this->_zipDecode($str);

        if ($this->_verifySign($decryptObject->encText, $decryptObject->signature))
        {
            return $str;
		}
        else
        {
            return false;
        }
    }

	public function decode_params($drparams)
    {
		$params = array() ;
        foreach ($drparams as $name => $value)
        {
            if (!empty($name) && !empty($value))
			{
				$key = $this->RESULT_MAP[$name];
                $params[$key] = $value;
			}
        }
		
        return $params;
    }

	
    protected function _prepareString($params)
    {
        $str = '';

        foreach ($params as $name => $value)
        {
            if (!empty($name) && !empty($value))
                $str .= $name.'='.$value.';';
        }
        return $str;
    }

    protected function _prepareParams($params)
    {
        $drparams = array() ;

        foreach ($params as $name => $value)
        {
            if (!empty($name) && !empty($value))
			{
				$drkey = $this->REQUEST_MAP[$name];
                $drparams[$drkey] = $value ;
			}
        }

        return $drparams;
    }


    protected function _parseAnswer($str)
    {
        $answer = array();

        foreach(explode(';', $str) as $param)
        {
            $paramArr = explode('=', $param);

            switch ($paramArr[0])
            {
                case 'A':
                    $answer['mid'] = $paramArr[1];
                    break;
                case 'B':
                    $answer['status'] = $paramArr[1];
                    break;
					
				case 'C':
					$answer['TransactionId'] = $paramArr[1];
					break;
				case 'D':
					$answer['PaymentMethod'] = $paramArr[1];
					break;
				case 'F':
					$answer['Timestamp'] = $paramArr[1];
					break;
				case 'G':
					$answer['VResId'] = $paramArr[1];
					break;
				case 'H':
					$answer['PAResId'] = $paramArr[1];
					break;
				case 'I':
					$answer['3DSecureStatus'] = $paramArr[1];
					break;
				case 'J':
					$answer['POSId'] = $paramArr[1];
					break;
				case 'K':
					$answer['CardTxType'] = $paramArr[1];
					break;
				case 'L':
					$answer['CardTxId'] = $paramArr[1];
					break;
				case 'M':
					$answer['CardType'] = $paramArr[1];
					break;
				case 'O':
					$answer['Last4Digits'] = $paramArr[1];
					break;
				case 'P':
					$answer['CardExpDate'] = $paramArr[1];
					break;
				case 'Q':
					$answer['StoreCardType'] = $paramArr[1];
					break;
				case 'R':
					$answer['IbpTxId'] = $paramArr[1];
					break;
				case 'S':
					$answer['IbpTxType'] = $paramArr[1];
					break;
	
				case 'T':
                    $answer['redirectStatus'] = $paramArr[1];
                    break;

				case 'U':
					$answer['CardNumberMasked'] = $paramArr[1];
					break;

                case 'N':
                    $answer['token'] = $paramArr[1];
                    break;
                case 'V':
                    $answer['maskedAccountNum'] = $paramArr[1];
                    break;
                case 'W':
                    $answer['expDate'] = $paramArr[1];
                    break;
                case 'E':
                    $answer['orderId'] = $paramArr[1];
                    break;
				case 'AC':
					$answer['AvsAnswerCode'] = $paramArr[1];
					break;
				case 'AD':
					$answer['AvsResponse'] = $paramArr[1];
					break;
				case 'AE':
					$answer['AcquirerAnswerCode'] = $paramArr[1];
					break;
				case 'AF':
					$answer['ClientAnswerCode'] = $paramArr[1];
					break;
				case 'AG':
					$answer['CvAnswerCode'] = $paramArr[1];
					break;
				case 'AH':
					$answer['CvResponse'] = $paramArr[1];
					break;
				case 'AI':
					$answer['PaymentMethodName'] = $paramArr[1];
					break;
				case 'AJ':
					$answer['AcquirerAuthCode'] = $paramArr[1];
					break;

            }
        }

        return $answer;
    }


    protected function _getBytesFromStr($str)
    {
        $byte_array = unpack('c*', $str);
        $bytes = implode(',', $byte_array);

        return $bytes;
    }

    protected function _toByte($bytes)
    {
        if (!is_array($bytes))
        {
            $bytes = explode(',', $bytes);
        }

        for ($i = 0, $cnt = count($bytes); $i < $cnt; $i++)
        {
            if ($bytes[$i] > 128)
            {
                $bytes[$i] = $bytes[$i] - 256;
            }
        }

        return $bytes;
    }

    protected function _getStrFromBytes($bytes)
    {
        $str = call_user_func_array("pack", array_merge(array("C*"), $bytes));
        return $str;
    }

    protected function _generateBytesArr($encryptedText)
    {
        $i = 0;

        $byte = array();
        $byte[] = 4;

        $certSerial = $this->digriverSerial; // $this->_getCertificateSerial(self::DIGRIVER_CERT_VALUE);
		if($this->doDEBUG)
			echo "DR_Ser ".$certSerial ;
        $encSerial = $this->_encodeCertificate($certSerial);

        while($i < 4)
        {
            $byte[] = $encSerial[$i];
            $i++;
        }

        $i = 0;

        $certSerial = $this->_getCertificateSerial($this->merchantCert);
		if($this->doDEBUG)
			echo "<br>Merch Ser ".$certSerial ;
		
        $encSerial = $this->_encodeCertificate($certSerial);

        while($i < 4)
        {
            $byte[] = $encSerial[$i];
            $i++;
        }

        unset($certSerial);
        unset($encSerial);

        $keyBytes = explode(',', $this->_getBytesFromStr($this->encryptionKey));

        foreach ($keyBytes as $keyByte)
        {
            $byte[] = $keyByte;
        }

        $signatureBytes = explode(',', $this->_getBytesFromStr($this->signature));

        foreach ($signatureBytes as $signatureByte)
        {
            $byte[] = $signatureByte;
        }

        $encryptedTextBytes = explode(',', $this->_getBytesFromStr($encryptedText));

        foreach($encryptedTextBytes as $encryptedTextByte)
        {
            $byte[] = $encryptedTextByte;
        }

        return $byte;
    }

    protected function _parseObjects($str)
    {
        $bytes = explode(',', $this->_getBytesFromStr($str));

        $encSerial1     = array();
        $encSerial2     = array();
        $key            = array();
        $signature      = array();
        $encText        = array();

        $count = 1;

        while($count < 5)
        {
            $encSerial1[] = $bytes[$count];
            $count++;
        }

        $count = 5;

        while($count < 9)
        {
            $encSerial2[] = $bytes[$count];
            $count++;
        }

        $count = 9;

        while($count < 137)
        {
            $key[] = $bytes[$count];
            $count++;
        }

        $count = 137;

        while($count < 265)
        {
            $signature[] = $bytes[$count];
            $count++;
        }

        $count = 265;

        while($count <= (count($bytes) - 1))
        {
            $encText[] = $bytes[$count];
            $count++;
        }

        $object = new stdClass();
        $object->encSerial1 = $this->_getStrFromBytes($encSerial1);
        $object->encSerial2 = $this->_getStrFromBytes($encSerial2);
        $object->key        = $this->_getStrFromBytes($key);
        $object->signature  = $this->_getStrFromBytes($signature);
        $object->encText    = $this->_getStrFromBytes($encText);

        return $object;
    }

    protected function _getCertificateSerial($cert)
    {
        //$cert = Mage::helper('core')->decrypt($cert);
		$cert1 = file_get_contents($cert) ;
        $data = openssl_x509_parse($cert1, true);
		// echo "_getSer \n" ;
		// var_dump($data);
        return $data['serialNumber'];
    }

    protected function _getCertificateSerial_x($cert)
    {
         return "4";
    }

    protected function _encodeCertificate($certificate)
    {
        $bytes = array(
            ($certificate >> 24) & 0xFF,
            ($certificate >> 16) & 0xFF,
            ($certificate >> 8) & 0xFF,
            $certificate & 0xFF,
        );

        return $bytes;
    }

    protected function _zipEncode($str)
    {
        // return gzencode($str, 1, FORCE_GZIP);
        // return gzdeflate($str, -1, ZLIB_ENCODING_RAW); // ZLIB_ENCODING_GZIP);
        return $this->_gzencode($str, 0);
		
    }

	
	protected function _gzencode($data, $level = 9)  
	{ 
		/*
		if (!extension_loaded('zlib') or !function_exists('gzcompress') { 
			return false; 
		} 
		*/
		if ($compressed = gzcompress($data, $level)) { 
			$crc = crc32($data); 
			$size = strlen($data); 

			// Remove wrong crc: 
			$compressed = substr($compressed, 0, -4); 
			// Add gzip header: 
			$compressed = "\x1f\x8b\x08\x00\x00\x00\x00\x00".$compressed; 
			// Add new crc: 

			$compressed .= pack('V', $crc); 
			$compressed .= pack('V', $size); 
		} 

		return $compressed; 
	} 

    protected function _zipDecode($str)
    {
        return gzinflate(substr($str,10,-8));
    }

    protected function _generateKey($bits = 128){
        $length = (int)((int)$bits / 8);
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    protected function _encryptString($str)
    {
        $size = mcrypt_get_block_size('rijndael-128', 'cbc');
        $str = $this->pkcs5_pad($str, $size);

        $key = $this->encryptionKey;
        $iv = $this->_getEncryptionVector();
        $encString = mcrypt_encrypt(MCRYPT_RIJNDAEL_128 ,$key, $str, MCRYPT_MODE_CBC, $iv);

        return $encString;
    }

    protected function _decryptString($EncryptedStr)
    {
        $key = $this->encryptionKey;
        $iv = $this->_getEncryptionVector();

        $string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $EncryptedStr, MCRYPT_MODE_CBC, $iv );
        return $string;
    }

    protected function _decryptSt_test($EncryptedStr)
    {
        $key = $this->encryptionKey;
        $iv = $this->_getEncryptionVector();

        $string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $EncryptedStr, MCRYPT_MODE_CBC, $iv );
        return $string;
    }

    protected function pkcs5_pad ($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    protected function _encryptPubKey($str)
    {
        $cryptText = '';
		$key = null;
        //$cert = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::DIGRIVER_CERT_VALUE));
		// if($this->isTEST)
		if(OPENSSLBUG1)
		{
			$pkey = file_get_contents($this->digriverKey); // "D:\\xampp\\htdocs\\netgiro_cert.pem"); //
			if($this->doDEBUG)
				print 'Dig River Pub key<br>'.$this->digriverKey.'<p>'.$pkey.'<p>'; //String: '.$str.'<p>' ;
			$key = openssl_get_publickey($pkey);
		} else {
			$cert = file_get_contents($this->digriverCert); // "D:\\xampp\\htdocs\\netgiro_cert.pem");
			if($this->doDEBUG)
				print 'Dig River Cert<br>'.$this->digriverCert.'<p>'.$cert.'<p>'; //String: '.$str.'<p>' ;
			$key = openssl_get_publickey($cert);
		}
              
		if($this->doDEBUG)
		{
			print 'Dig River key<p>';
			var_dump($key) ;
			print '<p>';
		}
		
		if($this->doDEBUG > 1)
		{
			print 'Netgiro key<br>';
			$det = openssl_pkey_get_details($key);
			print $det['key'];
			print '<br>bits '.$det['bits'] ;			
			print '<br><br>' ;
		}
		
// bool openssl_public_encrypt ( string $data , string &$crypted , mixed $key [, int $padding = OPENSSL_PKCS1_PADDING ] )
        openssl_public_encrypt( $str,           $cryptText,        $key,         OPENSSL_PKCS1_PADDING );

        return $cryptText;
    }

	// actually this routine is misnamed because it uses the private merchant key.
    protected function _decryptPubKey($encryptedKey)
    {
        $decryptedKey = '';

        //$cert = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::MERCHANT_KEY_CERT_VALUE));
		$cert = file_get_contents($this->merchantKey);
        $key = openssl_get_privatekey($cert);
		
		if($this->doDEBUG)
		{
			print 'Merc pub key<br>';
			htmlentities(var_dump(openssl_pkey_get_details($key)));
			print '<br>' ;
		}
		
        openssl_private_decrypt($encryptedKey, $decryptedKey, $key, OPENSSL_PKCS1_PADDING);

        return $decryptedKey;
    }

    protected function _decryptPubPubKey($encryptedKey)
    {
        $decryptedKey = '';

        //$cert = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::MERCHANT_KEY_CERT_VALUE));
		$cert = file_get_contents($this->merchantCert);
        $key = openssl_get_publickey($cert);
        openssl_public_decrypt($encryptedKey, $decryptedKey, $key, OPENSSL_PKCS1_PADDING);

        return $decryptedKey;
    }

    protected function _sign($str)
    {
			
		$cert = file_get_contents($this->merchantKey);
        // print 'Merchant Cert<br>'.$cert.'<p>String: '.$str.'<p>' ;
		
        $private_key = openssl_get_privatekey($cert); 

		if($this->doDEBUG > 1)
		{
			openssl_pkey_export($private_key, $exp, null );
			print 'Merchant private key<br>';
			print $exp;
			$det = openssl_pkey_get_details($private_key);
			
			print '<br> bits '.$det['bits'].'<br>' ;
			// htmlentities(var_dump(openssl_pkey_get_details($private_key)));
			print '<br>' ;
		}
		
        openssl_sign($str, $this->signature, $private_key, OPENSSL_ALGO_SHA1);
        openssl_free_key($private_key);

        return $str;
    }

    protected function _verifySign($str, $signature)
    {
 		$key = null;
		// $this->isTEST)
		if(OPENSSLBUG1)
		{
			$pkey = file_get_contents($this->digriverKey); 
			$key = openssl_get_publickey($pkey);
		} else {
			$cert = file_get_contents($this->digriverCert); 
			$key = openssl_get_publickey($cert);
		}

        $ok = openssl_verify($str, $signature, $key);

        if ($ok == 1)
            return true;
        else
            return false;
    }

    protected function _getEncryptionVector()
    {
        $iv = '';
        foreach ($this->encryptionVector as $byte)
        {
            $iv .= chr((int)$byte);
        }
        return $iv;
    }
}

?>
