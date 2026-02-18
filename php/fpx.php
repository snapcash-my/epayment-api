<?php
// Defines
define('ROOT_DIR', dirname(__DIR__, 1));

class FPX
{
	private $config;
	public function __construct()
	{
		// read config.json
		$config_filename = ROOT_DIR.'/config.json';

		if (!file_exists($config_filename)) {
		    throw new Exception("Can't find ".$config_filename);
		}
		$this->config = json_decode(file_get_contents($config_filename), true);
	}

	public function get_bank_list($post)
	{
		$mode = $post['mode'];
		$env = $this->config['fpx']['environment'];
		$cache = $this->config['cache'];
		$exchange = $post['exchange'];

		$file = ROOT_DIR.'/fpx/'.$exchange.'-'. $mode. '-'. $env. '.json';
		$be_file = ROOT_DIR.'/fpx/'.$exchange.'-'. $mode. '-'. $env. '-be_message.json';
		$current_time = time();
		$expire_time = $cache * 60;

		if(file_exists($file) && $current_time - $expire_time < filemtime($file)) {
			$bank_list = json_decode(file_get_contents($file),true);
			$be_message = json_decode(file_get_contents($be_file),true);
		} else {

			if($this->config['fpx']['environment'] == 'Production')
				$url = "https://www.mepsfpx.com.my/FPXMain/RetrieveBankList";
			else
				$url = "https://uat.mepsfpx.com.my/FPXMain/RetrieveBankList";

			$data = $this->get_checksum_api($mode, $exchange, $env);
			$content = $this->get_response($url, $data);
			
			if ($content == 'ERROR') {
				# check for certificate error
				$data = openssl_x509_parse(file_get_contents(ROOT_DIR.'/fpx/'.$env.'/'.$exchange.'/'.$exchange.'.cer'));

				$validFrom = 'Start: ' . date('Y-m-d H:i:s', $data['validFrom_time_t']);
				$validTo = 'End: ' . date('Y-m-d H:i:s', $data['validTo_time_t']);

				$response = [
					'status' => 'error',
					'message' => 'Certificate Error. Please check the validity of FPX certificate.'."\n".$validFrom . "\n".$validTo . "\n"
				];
				return $response;
			}

			$token = strtok($content, "&");

			while ($token !== false) {
				list($key, $value) = explode("=", $token);
				$value = urldecode($value);
				$response_value[$key] = $value;
				$token = strtok("&");
			}

			$fpx_msgToken = reset($response_value);

			$token = strtok($response_value['fpx_bankList'], ",");

			while ($token !== false) {
				list($key, $value) = explode("~", $token);
				$value = urldecode($value);
				$bank_list[$key] = $value;
				$token = strtok(",");
			}

			$be_message = $response_value['fpx_bankList']."|".$fpx_msgToken."|".$response_value['fpx_msgType']."|".$response_value['fpx_sellerExId'];

			if ($mode == '01'){
				$cimb = 'CIMB Clicks';
				$rakyat = 'Bank Rakyat';
			} else {
				$cimb = 'CIMB Bank';
				$rakyat = 'i-bizRAKYAT';
			}

			$bank_name = [
				'TEST0021' => 'SBI Bank A',
				'TEST0022' => 'SBI Bank B',
				'TEST0023' => 'SBI Bank C',
				'ABB0234' => 'Affin B2C - Test ID',
				'ABB0233' => 'Affin Bank',
				'ABB0232' => 'Affin Bank',
				'ABB0235' => 'AFFINMAX',
				'ABMB0212' => 'Alliance Bank (Personal)',
				'ABMB0213' => 'Alliance Bank (Business)',
				'AGRO01' => 'AGRONet',
				'AGRO02' => 'AGRONetBiz',
				'AMBB0208' => 'AmBank',
				'AMBB0209' => 'AmBank',
				'BIMB0340' => 'Bank Islam',
				'BKRM0602' => $rakyat,
				'BMMB0341' => 'Bank Muamalat',
				'BMMB0342' => 'Bank Muamalat',
				'BNP003' => 'BNP Paribas',
				'BSN0601' => 'BSN',
				'BCBB0235' => $cimb,
				'CIT0218' => 'Citibank Corporate Banking',
				'CIT0219' => 'Citibank',
				'DBB0199' => 'Deutsche Bank',
				'HLB0224' => 'Hong Leong Bank',
				'HSBC0223' => 'HSBC Bank',
				'KFH0346' => 'KFH',
				'MB2U0227' => 'Maybank2U',
				'MBB0228' => 'Maybank2E',
				'OCBC0229' => 'OCBC Bank',
				'PBB0233' => 'Public Bank PBe',
				'PBB0234' => 'Public Bank PB enterprise',
				'RHB0218' => 'RHB Bank',
				'SCB0215' => 'Standard Chartered',
				'SCB0216' => 'Standard Chartered',
				'UOB0226' => 'UOB Bank',
				'UOB0228' => 'UOB Regional',
				'UOB0229' => 'UOB Bank - Test ID',
				'BOCM01' => 'Bank of China',
				'MBBM2U2' => 'Unknown',
			];

			foreach ($bank_list as $key => $value) {
				if ($value == 'B') $value = ' (Offline)'; else $value = '';
				if(isset($bank_name[$key])) 
					$bank_list[$key] = $bank_name[$key].$value;
				else
					$bank_list[$key] = $key.$value;
			}

			// Keep Affin banks at top
			$affin_banks = array();
			if (isset($bank_list['ABB0234'])) $affin_banks['ABB0234'] = $bank_list['ABB0234'];
			if (isset($bank_list['ABB0233'])) $affin_banks['ABB0233'] = $bank_list['ABB0233'];
			
			// Keep AGRO banks and ABMB banks in specific order
			$agro_abmb_banks = array();
			if (isset($bank_list['ABMB0212'])) $agro_abmb_banks['ABMB0212'] = $bank_list['ABMB0212'];
			if (isset($bank_list['ABMB0213'])) $agro_abmb_banks['ABMB0213'] = $bank_list['ABMB0213'];
			if (isset($bank_list['AGRO01'])) $agro_abmb_banks['AGRO01'] = $bank_list['AGRO01'];
			if (isset($bank_list['AGRO02'])) $agro_abmb_banks['AGRO02'] = $bank_list['AGRO02'];
			
			// Remove special ordered banks before sorting
			unset(
				$bank_list['ABB0234'], 
				$bank_list['ABB0233'],
				$bank_list['AGRO01'],
				$bank_list['AGRO02'],
				$bank_list['ABMB0212'],
				$bank_list['ABMB0213']
			);
			
			// Sort remaining banks
			ksort($bank_list);
			
			// Merge all banks back in the desired order
			$bank_list = $affin_banks + $agro_abmb_banks + $bank_list;

			# store bank list for drop down select
			file_put_contents($file, json_encode($bank_list));

			#store bank list for BE message
			file_put_contents($be_file, json_encode($be_message));
		}
		
		$content = array();
		$content['bank_list'] = $bank_list;
		$content['be_message'] = $be_message;

		return $content;
	}

	public function api_bank()
	{
		$mode = $_POST['mode'];
		$env = $_POST['env'];
		$exchange = $_POST['exchange'];

		if($env == 'Production')
			$url = "https://www.mepsfpx.com.my/FPXMain/RetrieveBankList";
		else
			$url = "https://uat.mepsfpx.com.my/FPXMain/RetrieveBankList";

		$data = $this->get_checksum_api($mode, $exchange, $env);
		$content = $this->get_response($url, $data);

		if ($content == 'ERROR') {
			# check for certificate error
			$data = openssl_x509_parse(file_get_contents(ROOT_DIR.'/fpx/'.$env.'/'.$exchange.'/'.$exchange.'.cer'));

			$validFrom = 'Start: ' . date('Y-m-d H:i:s', $data['validFrom_time_t']);
			$validTo = 'End: ' . date('Y-m-d H:i:s', $data['validTo_time_t']);

			$response = [
				'status' => 'error',
				'message' => 'Certificate Error. Please check the validity of FPX certificate',
				'start_date' => $validFrom,
				'end_date' => $validTo
			];

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}
		
		echo $content;
		exit;
	}

	private function get_checksum_api($mode, $exchange, $env)
	{
		$msgToken = $mode;
		$msgType = 'BE';
		$version = '6.0';

		$out = $msgToken.'|'.$msgType.'|'.$exchange.'|'.$version;
		$key_location = ROOT_DIR.'/fpx/'.$env.'/'.$exchange.'/'.$exchange.'.key';
		
		try{
			$priv_key = file_get_contents($key_location);
			openssl_sign($out, $binary_signature, $priv_key, OPENSSL_ALGO_SHA1);
			$checkSum = strtoupper(bin2hex( $binary_signature ));

			$data = array(
				"fpx_msgType" => $msgType,
				"fpx_msgToken" => $msgToken,
				"fpx_sellerExId" => $exchange,
				"fpx_version" => $version,
				"fpx_checkSum" => $checkSum
			);

			return $data;
		}
		catch (Exception $e) {
    		return $e->getMessage(); 
		}
	}

	private function get_checksum($mode)
	{
		$msgToken = $mode;
		$msgType = 'BE';
		$sellerExId = $this->config['fpx']['exchange-id'];
		$version = '6.0';

		$out = $msgToken.'|'.$msgType.'|'.$sellerExId.'|'.$version;
		$key_location = ROOT_DIR.'/fpx/'.$this->config['fpx']['environment'].'/'.$sellerExId.'/'.$sellerExId.'.key';
		
		try{
			$priv_key = file_get_contents($key_location);
			openssl_sign($out, $binary_signature, $priv_key, OPENSSL_ALGO_SHA1);
			$checkSum = strtoupper(bin2hex( $binary_signature ));

			$data = array(
				"fpx_msgType" => $msgType,
				"fpx_msgToken" => $msgToken,
				"fpx_sellerExId" => $sellerExId,
				"fpx_version" => $version,
				"fpx_checkSum" => $checkSum
			);

			return $data;
		}
		catch (Exception $e) {
    		return $e->getMessage(); 
		}
	}

	private function get_response($url, $param)
	{
		try {
			$data = http_build_query($param);

			$opts = array(
			  'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $data
			  )
			);

			$context = stream_context_create($opts);
			$result = file_get_contents($url, false, $context);
			return $result;
		}
		catch(Exception $e) {
			return $e->getMessage(); 
		}
	}

}	
