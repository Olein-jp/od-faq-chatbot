<?php
/**
Plugin Name:       OD FAQ Chatbot
Description:       FAQ chatbot plugin for WordPress.
Version:           0.0.1
Requires at least: 6.5
Requires PHP:      7.4
Author:            Olein Design
Text Domain:       od-faq-chatbot
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OD_FAQ_CHATBOT_VERSION', '0.0.1' );
define( 'OD_FAQ_CHATBOT_FILE', __FILE__ );
define( 'OD_FAQ_CHATBOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'OD_FAQ_CHATBOT_URL', plugin_dir_url( __FILE__ ) );
define( 'OD_FAQ_CHATBOT_OPTION', 'ai_faq_chatbot_settings' );
define( 'OD_FAQ_CHATBOT_STORAGE_DIR', 'ai-faq-chatbot' );
define( 'OD_FAQ_CHATBOT_GITHUB_OWNER', 'Olein-jp' );
define( 'OD_FAQ_CHATBOT_GITHUB_REPOSITORY', 'od-faq-chatbot' );

if ( file_exists( OD_FAQ_CHATBOT_PATH . 'vendor/autoload.php' ) ) {
	require_once OD_FAQ_CHATBOT_PATH . 'vendor/autoload.php';
}

require_once OD_FAQ_CHATBOT_PATH . 'includes/class-settings.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-security.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-knowledge-repository.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-log-repository.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-content-extractor.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-chunker.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-knowledge-builder.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-search.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-ai-client.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-chat-service.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-rest-api.php';
require_once OD_FAQ_CHATBOT_PATH . 'admin/class-admin.php';
require_once OD_FAQ_CHATBOT_PATH . 'public/class-public.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-plugin.php';
require_once OD_FAQ_CHATBOT_PATH . 'includes/class-activator.php';

register_activation_hook( __FILE__, array( 'OD_FAQ_Chatbot_Activator', 'activate' ) );

/**
 * Starts the plugin.
 *
 * @return void
 */
function od_faq_chatbot_bootstrap() {
	od_faq_chatbot_register_github_updater();

	OD_FAQ_Chatbot_Plugin::instance()->run();
}
add_action( 'plugins_loaded', 'od_faq_chatbot_bootstrap' );

/**
 * Registers GitHub Releases as the plugin update source.
 *
 * @return void
 */
function od_faq_chatbot_register_github_updater() {
	if ( ! class_exists( 'Inc2734\WP_GitHub_Plugin_Updater\Bootstrap' ) ) {
		return;
	}

	new Inc2734\WP_GitHub_Plugin_Updater\Bootstrap(
		plugin_basename( OD_FAQ_CHATBOT_FILE ),
		OD_FAQ_CHATBOT_GITHUB_OWNER,
		OD_FAQ_CHATBOT_GITHUB_REPOSITORY
	);
}
