<?php

	/**
	 * Profiles 'activate_mailing' action handler
	 * @param  object $params Object with message parameters
	 */
	function profiles_activate_mailing_action($params) {
		# Create message contents
		$contents = '<p>Activation link: <a href="'.$params->link.'">'.$params->link.'</a></p>';
		# Format addresses
		$addresses = array_keys($params->to);
		$address = array_shift($addresses);
		$to = "{$params->to[$address]} <{$address}>";
		$addresses = array_keys($params->from);
		$address = array_shift($addresses);
		$from = "{$params->from[$address]} <{$address}>";
		# Headers
		$headers = array();
		$headers[] = "From: {$from}";
		# Send message
		wp_mail($to, $params->subject, $contents, $headers);
	}
	add_action('profiles_activate_mailing', 'profiles_activate_mailing_action');

	/**
	 * Profiles 'activate_mailing' action handler
	 * @param  object $params Object with message parameters
	 */
	function profiles_recover_mailing_action($params) {
		# Create message contents
		$contents = '<p>Recover link: <a href="'.$params->link.'">'.$params->link.'</a></p>';
		# Format addresses
		$addresses = array_keys($params->to);
		$address = array_shift($addresses);
		$to = "{$params->to[$address]} <{$address}>";
		$addresses = array_keys($params->from);
		$address = array_shift($addresses);
		$from = "{$params->from[$address]} <{$address}>";
		# Headers
		$headers = array();
		$headers[] = "From: {$from}";
		# Send message
		wp_mail($to, $params->subject, $contents, $headers);
	}
	add_action('profiles_recover_mailing', 'profiles_recover_mailing_action');

?>