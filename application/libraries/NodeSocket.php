<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NodeSocket {
	protected $_ci;

	function __construct() {
		$this->_ci         = &get_instance();
		$this->_ci->socket = $this;
	}

	public function Emmit($Event = 'notificapqrd', $PlayLoad) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, NOTIFY_FULL_SERVER . $Event);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_PORT, NOTIFY_PORT);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '$_POST=' . json_encode($PlayLoad));

		$headers = [
			"Content-Type: application/x-www-form-urlencoded",
			"Accept: text/html",
		];

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$output = json_decode(curl_exec($ch), TRUE);

		return $output;
	}
}