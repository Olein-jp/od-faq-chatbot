<?php
/**
 * Settings access and sanitization.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages plugin settings.
 */
class OD_FAQ_Chatbot_Settings {

	/**
	 * Registers Settings API hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_setting' ) );
	}

	/**
	 * Registers the option for WordPress.
	 *
	 * @return void
	 */
	public function register_setting() {
		register_setting(
			'od_faq_chatbot_settings',
			OD_FAQ_CHATBOT_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => $this->get_defaults(),
			)
		);
	}

	/**
	 * Returns default settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_defaults() {
		return array(
			'target_post_types'        => array( 'post', 'page' ),
			'chat_enabled'             => true,
			'access_mode'              => 'all',
			'excluded_post_ids'        => array(),
			'excluded_post_types'      => array(),
			'initial_message'          => __( 'ご質問を入力してください。サイト上の情報をもとに回答します。', 'od-faq-chatbot' ),
			'tone_instruction'         => __( 'わかりやすく丁寧な日本語で回答してください。', 'od-faq-chatbot' ),
			'persona_instruction'      => __( 'あなたはこのサイトのFAQ担当です。サイト上の根拠がある内容だけを回答してください。', 'od-faq-chatbot' ),
			'no_answer_message'        => __( 'サイト上では確認できません。', 'od-faq-chatbot' ),
			'privacy_notice'           => __( '個人情報は入力しないでください。', 'od-faq-chatbot' ),
			'max_chunks'               => 5,
			'max_answer_length'        => 800,
			'log_retention_days'       => 90,
			'delete_data_on_uninstall' => false,
		);
	}

	/**
	 * Returns merged settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings() {
		$settings = get_option( OD_FAQ_CHATBOT_OPTION, array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, $this->get_defaults() );
	}

	/**
	 * Updates settings.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return bool
	 */
	public function update_settings( $settings ) {
		return update_option( OD_FAQ_CHATBOT_OPTION, $this->sanitize( $settings ) );
	}

	/**
	 * Sanitizes settings.
	 *
	 * @param mixed $input Raw settings.
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ) {
		$defaults = $this->get_defaults();
		$input    = is_array( $input ) ? $input : array();

		$settings = array(
			'target_post_types'        => $this->sanitize_post_types( $input['target_post_types'] ?? array() ),
			'chat_enabled'             => ! empty( $input['chat_enabled'] ),
			'access_mode'              => in_array( $input['access_mode'] ?? 'all', array( 'all', 'logged_in' ), true ) ? sanitize_key( $input['access_mode'] ) : 'all',
			'excluded_post_ids'        => $this->sanitize_post_ids( $input['excluded_post_ids'] ?? array() ),
			'excluded_post_types'      => $this->sanitize_post_types( $input['excluded_post_types'] ?? array() ),
			'initial_message'          => $this->sanitize_textarea_limited( $input['initial_message'] ?? $defaults['initial_message'], 300 ),
			'tone_instruction'         => $this->sanitize_textarea_limited( $input['tone_instruction'] ?? $defaults['tone_instruction'], 1000 ),
			'persona_instruction'      => $this->sanitize_textarea_limited( $input['persona_instruction'] ?? $defaults['persona_instruction'], 1000 ),
			'no_answer_message'        => $this->sanitize_textarea_limited( $input['no_answer_message'] ?? $defaults['no_answer_message'], 300 ),
			'privacy_notice'           => $this->sanitize_textarea_limited( $input['privacy_notice'] ?? $defaults['privacy_notice'], 300 ),
			'max_chunks'               => $this->sanitize_int_range( $input['max_chunks'] ?? 5, 1, 10, 5 ),
			'max_answer_length'        => $this->sanitize_int_range( $input['max_answer_length'] ?? 800, 100, 2000, 800 ),
			'log_retention_days'       => max( 1, absint( $input['log_retention_days'] ?? 90 ) ),
			'delete_data_on_uninstall' => ! empty( $input['delete_data_on_uninstall'] ),
		);

		if ( empty( $settings['target_post_types'] ) ) {
			$settings['target_post_types'] = array( 'post', 'page' );
		}

		return $settings;
	}

	/**
	 * Returns public post types available for learning.
	 *
	 * @return array<string, WP_Post_Type>
	 */
	public function get_public_post_types() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		unset( $post_types['attachment'] );

		return $post_types;
	}

	/**
	 * Sanitizes public post type names.
	 *
	 * @param mixed $post_types Raw post types.
	 * @return string[]
	 */
	private function sanitize_post_types( $post_types ) {
		$post_types = is_array( $post_types ) ? $post_types : array();
		$allowed    = array_keys( $this->get_public_post_types() );

		return array_values(
			array_intersect(
				array_map( 'sanitize_key', $post_types ),
				$allowed
			)
		);
	}

	/**
	 * Sanitizes post IDs.
	 *
	 * @param mixed $post_ids Raw IDs.
	 * @return int[]
	 */
	private function sanitize_post_ids( $post_ids ) {
		if ( is_string( $post_ids ) ) {
			$post_ids = preg_split( '/[\s,]+/', $post_ids );
		}

		$post_ids = is_array( $post_ids ) ? $post_ids : array();
		$post_ids = array_filter( array_map( 'absint', $post_ids ) );

		return array_values(
			array_filter(
				array_unique( $post_ids ),
				static function ( $post_id ) {
					return (bool) get_post( $post_id );
				}
			)
		);
	}

	/**
	 * Sanitizes text and enforces a maximum length.
	 *
	 * @param mixed $value Raw value.
	 * @param int   $max_length Maximum length.
	 * @return string
	 */
	private function sanitize_textarea_limited( $value, $max_length ) {
		$value = sanitize_textarea_field( wp_strip_all_tags( (string) $value ) );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}

	/**
	 * Sanitizes an integer within a range.
	 *
	 * @param mixed $value Raw value.
	 * @param int   $min Minimum.
	 * @param int   $max Maximum.
	 * @param int   $fallback Fallback.
	 * @return int
	 */
	private function sanitize_int_range( $value, $min, $max, $fallback ) {
		$value = absint( $value );

		if ( $value < $min || $value > $max ) {
			return $fallback;
		}

		return $value;
	}
}
