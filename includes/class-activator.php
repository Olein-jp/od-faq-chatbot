<?php
/**
 * Activation tasks.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation.
 */
class OD_FAQ_Chatbot_Activator {

	/**
	 * Creates default settings and protected storage directories.
	 *
	 * @return void
	 */
	public static function activate() {
		$settings = new OD_FAQ_Chatbot_Settings();

		if ( false === get_option( OD_FAQ_CHATBOT_OPTION, false ) ) {
			add_option( OD_FAQ_CHATBOT_OPTION, $settings->get_defaults() );
		}

		$security = new OD_FAQ_Chatbot_Security();
		$security->ensure_storage();
	}
}
