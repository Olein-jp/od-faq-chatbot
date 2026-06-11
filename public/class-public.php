<?php
/**
 * Public frontend output.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the floating chatbot.
 */
class OD_FAQ_Chatbot_Public {

	/**
	 * Settings.
	 *
	 * @var OD_FAQ_Chatbot_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Settings $settings Settings.
	 */
	public function __construct( OD_FAQ_Chatbot_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_chatbot' ) );
	}

	/**
	 * Enqueues frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}

		wp_enqueue_style(
			'od-faq-chatbot',
			OD_FAQ_CHATBOT_URL . 'public/assets/chatbot.css',
			array(),
			OD_FAQ_CHATBOT_VERSION
		);

		wp_enqueue_script(
			'od-faq-chatbot',
			OD_FAQ_CHATBOT_URL . 'public/assets/chatbot.js',
			array(),
			OD_FAQ_CHATBOT_VERSION,
			true
		);

		$settings = $this->settings->get_settings();

		wp_localize_script(
			'od-faq-chatbot',
			'ODFaqChatbot',
			array(
				'restUrl'        => esc_url_raw( rest_url( 'od-faq-chatbot/v1/ask' ) ),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'initialMessage' => $settings['initial_message'],
				'privacyNotice'  => $settings['privacy_notice'],
				'labels'         => array(
					'open'       => __( 'チャットを開く', 'od-faq-chatbot' ),
					'close'      => __( 'チャットを閉じる', 'od-faq-chatbot' ),
					'question'   => __( '質問を入力してください', 'od-faq-chatbot' ),
					'send'       => __( '送信', 'od-faq-chatbot' ),
					'loading'    => __( '回答を生成しています。', 'od-faq-chatbot' ),
					'references' => __( '参照元', 'od-faq-chatbot' ),
					'error'      => __( '回答の生成中にエラーが発生しました。時間をおいて再度お試しください。', 'od-faq-chatbot' ),
				),
			)
		);
	}

	/**
	 * Renders chatbot template.
	 *
	 * @return void
	 */
	public function render_chatbot() {
		if ( ! $this->should_display() ) {
			return;
		}

		include OD_FAQ_CHATBOT_PATH . 'templates/chatbot.php';
	}

	/**
	 * Determines whether chatbot should be shown.
	 *
	 * @return bool
	 */
	private function should_display() {
		$settings = $this->settings->get_settings();

		if ( empty( $settings['chat_enabled'] ) ) {
			return false;
		}

		if ( 'logged_in' === $settings['access_mode'] && ! is_user_logged_in() ) {
			return false;
		}

		$post_id = get_queried_object_id();

		if ( $post_id && in_array( $post_id, array_map( 'absint', $settings['excluded_post_ids'] ), true ) ) {
			return false;
		}

		if ( is_singular() ) {
			$post_type = get_post_type( $post_id );

			if ( $post_type && in_array( $post_type, $settings['excluded_post_types'], true ) ) {
				return false;
			}
		}

		return true;
	}
}
