<?php declare( strict_types=1 );

class Payment_Method {

	private string $payment_type;
	private Payment_Gateway $payment_gateway;
	private string $card_bin;
	private string $card_last4;
	private string $avs_result_code;
	private string $cvv_result_code;
	private string $verification_status;
	private string $routing_number;
	private string $shortened_iban_first6;
	private string $shortened_iban_last4;
	private bool $sepa_direct_debit_mandate;
	private string $decline_reason_code;
	private string $wallet_address;
	private string $wallet_type;
	private string $paypal_payer_id;
	private string $paypal_payer_email;
	private string $paypal_payer_status;
	private string $paypal_address_status;
	private string $paypal_protection_eligibility;
	private string $paypal_payment_status;
	private string $stripe_cvc_check;
	private string $stripe_address_line1_check;
	private string $stripe_address_line2_check;
	private string $stripe_address_zip_check;
	private string $stripe_funding;
	private string $stripe_brand;
	private string $account_holder_name;
	private string $account_number_last5;
	private string $bank_name;
	private string $bank_country;

	public function set_woo_payment_gateway( string $woo_gateway_id ) {
		$this->payment_gateway = new Payment_Gateway( $woo_gateway_id );
	}

	public function to_array(): array {
		return array(
			'$payment_type'    => Payment_Method::normalize_payment_type_string( $gateway_id, $gateway_payment_type ),
			'$payment_gateway' => $this->payment_gateway->to_string(),
		);
	}

}