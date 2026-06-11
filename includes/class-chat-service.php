<?php
/**
 * Chat service.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles question validation, search, AI response and logs.
 */
class OD_FAQ_Chatbot_Chat_Service {

	/**
	 * Settings.
	 *
	 * @var OD_FAQ_Chatbot_Settings
	 */
	private $settings;

	/**
	 * Search service.
	 *
	 * @var OD_FAQ_Chatbot_Search
	 */
	private $search;

	/**
	 * AI client.
	 *
	 * @var OD_FAQ_Chatbot_AI_Client
	 */
	private $ai_client;

	/**
	 * Logs.
	 *
	 * @var OD_FAQ_Chatbot_Log_Repository
	 */
	private $logs;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Settings       $settings Settings.
	 * @param OD_FAQ_Chatbot_Search         $search Search.
	 * @param OD_FAQ_Chatbot_AI_Client      $ai_client AI client.
	 * @param OD_FAQ_Chatbot_Log_Repository $logs Logs.
	 */
	public function __construct( OD_FAQ_Chatbot_Settings $settings, OD_FAQ_Chatbot_Search $search, OD_FAQ_Chatbot_AI_Client $ai_client, OD_FAQ_Chatbot_Log_Repository $logs ) {
		$this->settings  = $settings;
		$this->search    = $search;
		$this->ai_client = $ai_client;
		$this->logs      = $logs;
	}

	/**
	 * Checks whether current visitor can use chat.
	 *
	 * @return bool
	 */
	public function can_access() {
		$settings = $this->settings->get_settings();

		if ( empty( $settings['chat_enabled'] ) ) {
			return false;
		}

		if ( 'logged_in' === $settings['access_mode'] && ! is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * Answers a question.
	 *
	 * @param string $question Raw question.
	 * @return array<string, mixed>|WP_Error
	 */
	public function answer( $question ) {
		if ( ! $this->can_access() ) {
			return new WP_Error( 'chat_unavailable', __( '現在チャットを利用できません。', 'od-faq-chatbot' ), array( 'status' => 403 ) );
		}

		$settings = $this->settings->get_settings();
		$question = $this->sanitize_question( $question );

		if ( '' === $question ) {
			return new WP_Error( 'question_required', __( '質問を入力してください。', 'od-faq-chatbot' ), array( 'status' => 400 ) );
		}

		$chunks = $this->search->search( $question, absint( $settings['max_chunks'] ) );

		if ( is_wp_error( $chunks ) ) {
			return $chunks;
		}

		if ( empty( $chunks ) ) {
			$answer = $settings['no_answer_message'];
			$this->save_logs( $question, $answer, 'unanswered', array(), 'related_chunk_not_found' );

			return array(
				'answer'     => $answer,
				'references' => array(),
				'status'     => 'unanswered',
			);
		}

		$ai_answer = $this->ai_client->generate_answer( $question, $chunks, $settings );

		if ( is_wp_error( $ai_answer ) ) {
			return $ai_answer;
		}

		$references = $this->build_references( $chunks );

		$this->save_logs( $question, $ai_answer, 'answered', $references, '' );

		return array(
			'answer'     => $ai_answer,
			'references' => $references,
			'status'     => 'answered',
		);
	}

	/**
	 * Sanitizes a question.
	 *
	 * @param string $question Raw question.
	 * @return string
	 */
	private function sanitize_question( $question ) {
		$question = wp_strip_all_tags( (string) $question );
		$question = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $question );
		$question = sanitize_textarea_field( $question );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $question, 0, 500 );
		}

		return substr( $question, 0, 500 );
	}

	/**
	 * Builds unique references from chunks.
	 *
	 * @param array<int, array<string,mixed>> $chunks Chunks.
	 * @return array<int, array{title:string,url:string}>
	 */
	private function build_references( $chunks ) {
		$references = array();
		$seen       = array();

		foreach ( $chunks as $chunk ) {
			$url = esc_url_raw( $chunk['source_url'] ?? $chunk['url'] ?? '' );

			if ( '' === $url || isset( $seen[ $url ] ) ) {
				continue;
			}

			$references[] = array(
				'title' => sanitize_text_field( $chunk['source_title'] ?? $chunk['heading'] ?? $url ),
				'url'   => $url,
			);
			$seen[ $url ] = true;
		}

		return $references;
	}

	/**
	 * Saves question and unanswered logs.
	 *
	 * @param string                           $question Question.
	 * @param string                           $answer Answer.
	 * @param string                           $status Status.
	 * @param array<int, array<string,string>> $references References.
	 * @param string                           $reason Unanswered reason.
	 * @return void
	 */
	private function save_logs( $question, $answer, $status, $references, $reason ) {
		$created_at = current_time( 'c' );
		$id         = uniqid( 'log_', true );

		$this->logs->append_question(
			array(
				'id'         => $id,
				'question'   => $question,
				'answer'     => $answer,
				'status'     => $status,
				'references' => $references,
				'created_at' => $created_at,
			)
		);

		if ( 'unanswered' === $status ) {
			$this->logs->append_unanswered(
				array(
					'id'         => uniqid( 'unanswered_', true ),
					'question'   => $question,
					'reason'     => $reason,
					'created_at' => $created_at,
				)
			);
		}
	}
}
