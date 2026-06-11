<?php
/**
 * Main plugin coordinator.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin modules.
 */
class OD_FAQ_Chatbot_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var OD_FAQ_Chatbot_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Returns the plugin instance.
	 *
	 * @return OD_FAQ_Chatbot_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function run() {
		load_plugin_textdomain( 'od-faq-chatbot', false, dirname( plugin_basename( OD_FAQ_CHATBOT_FILE ) ) . '/languages' );

		$settings   = new OD_FAQ_Chatbot_Settings();
		$security   = new OD_FAQ_Chatbot_Security();
		$repository = new OD_FAQ_Chatbot_Knowledge_Repository( $security );
		$logs       = new OD_FAQ_Chatbot_Log_Repository( $security );
		$chunker    = new OD_FAQ_Chatbot_Chunker();
		$extractor  = new OD_FAQ_Chatbot_Content_Extractor( $chunker );
		$builder    = new OD_FAQ_Chatbot_Knowledge_Builder( $settings, $extractor, $repository );
		$search     = new OD_FAQ_Chatbot_Search( $repository );
		$ai_client  = new OD_FAQ_Chatbot_AI_Client();
		$chat       = new OD_FAQ_Chatbot_Chat_Service( $settings, $search, $ai_client, $logs );

		$settings->register_hooks();
		$security->register_hooks();

		( new OD_FAQ_Chatbot_Admin( $settings, $repository, $builder, $logs, $ai_client ) )->register_hooks();
		( new OD_FAQ_Chatbot_REST_API( $chat ) )->register_hooks();
		( new OD_FAQ_Chatbot_Public( $settings ) )->register_hooks();
	}
}
