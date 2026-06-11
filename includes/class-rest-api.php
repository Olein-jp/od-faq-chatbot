<?php
/**
 * REST API endpoints.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers public chat REST endpoints.
 */
class OD_FAQ_Chatbot_REST_API {

	/**
	 * Chat service.
	 *
	 * @var OD_FAQ_Chatbot_Chat_Service
	 */
	private $chat_service;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Chat_Service $chat_service Chat service.
	 */
	public function __construct( OD_FAQ_Chatbot_Chat_Service $chat_service ) {
		$this->chat_service = $chat_service;
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'od-faq-chatbot/v1',
			'/ask',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'ask' ),
				'permission_callback' => array( $this, 'can_ask' ),
				'args'                => array(
					'question' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
						'validate_callback' => static function ( $value ) {
							return is_string( $value ) && '' !== trim( $value );
						},
					),
				),
			)
		);
	}

	/**
	 * Checks chat permissions and nonce.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function can_ask( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'x_wp_nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'rest_forbidden', __( '不正なリクエストです。', 'od-faq-chatbot' ), array( 'status' => 403 ) );
		}

		if ( ! $this->chat_service->can_access() ) {
			return new WP_Error( 'chat_unavailable', __( '現在チャットを利用できません。', 'od-faq-chatbot' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Answers a question.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function ask( WP_REST_Request $request ) {
		$result = $this->chat_service->answer( (string) $request->get_param( 'question' ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}
}
