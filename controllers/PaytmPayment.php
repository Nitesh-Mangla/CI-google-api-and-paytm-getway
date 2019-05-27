<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaytmPayment extends CI_Controller {

	public function __construct()
	{
			parent::__construct();
			require_once APPPATH.'PaytmKit/lib/config_paytm.php';
			require_once APPPATH.'PaytmKit/lib/encdec_paytm.php';
	}

	public function orderPlace()
	{
			$ORDER_ID = md5(rand(0,1000));
			$TXN_AMOUNT = 200;

			$data = $this->handlePaytmRequest( $ORDER_ID, $TXN_AMOUNT );
			$para = array(
				'paramList' => $data['paramList'],
				'checkSum'  => $data['checkSum'],
				'PAYTM_TXN_URL' => PAYTM_TXN_URL
			);

			$this->load->view('payamount', $para);
	}

	public function handlePaytmRequest( $ORDER_ID, $TXN_AMOUNT )
	{
			$checkSum = "";
			$paramList = array();
			$paramList["MID"] = PAYTM_MERCHANT_MID;
			$paramList["ORDER_ID"] = $ORDER_ID;
			$paramList["CUST_ID"] = md5(rand(0,1000));
			$paramList["INDUSTRY_TYPE_ID"] = 'Retail';
			$paramList["CHANNEL_ID"] = 'WEB';
			$paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
			$paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;
			$paramList['CALLBACK_URL'] = base_url('PaytmPayment/getResponse');
			$checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);

			return array(
				'paramList' => $paramList,
				'checkSum'  => $checkSum
			);
	}

	public function getResponse()
	{
		
		$paytmChecksum = "";
		$paramList = array();
		$isValidChecksum = "FALSE";

		$paramList = $_POST;
		$paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg
		$isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum); 

		if($isValidChecksum == "TRUE") {
			echo "<b>Checksum matched and following are the transaction details:</b>" . "<br/>";
			if ($_POST["STATUS"] == "TXN_SUCCESS") {
				echo "<b>Transaction status is success</b>" . "<br/>";
			}

			else {
				echo "<b>Transaction status is failure</b>" . "<br/>";
			}

			if (isset($_POST) && count($_POST)>0 )
			{ 
				foreach($_POST as $paramName => $paramValue) {
						echo "<br/>" . $paramName . " = " . $paramValue;
				}
			}			

		}
		else {
			echo "<b>Checksum mismatched.</b>";
			//Process transaction as suspicious.
		}
	}
}