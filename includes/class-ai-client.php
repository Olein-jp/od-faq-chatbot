<?php
/**
 * AI Connector integration.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates answers through WordPress AI Connector when available.
 */
class OD_FAQ_Chatbot_AI_Client {

	/**
	 * Returns whether an AI integration appears available.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( function_exists( 'wp_ai_generate_text' ) || function_exists( 'wp_ai_connector_generate_text' ) || function_exists( 'ai_connector_generate_text' ) ) {
			return true;
		}

		return has_filter( 'od_faq_chatbot_ai_generate_answer' );
	}

	/**
	 * Generates an answer.
	 *
	 * @param string                          $question Question.
	 * @param array<int, array<string,mixed>> $chunks Related chunks.
	 * @param array<string, mixed>            $settings Settings.
	 * @return string|WP_Error
	 */
	public function generate_answer( $question, $chunks, $settings ) {
		$prompt = $this->build_prompt( $question, $chunks, $settings );
		$args   = array(
			'max_tokens'   => absint( $settings['max_answer_length'] ?? 800 ),
			'temperature'  => 0.2,
			'instructions' => $settings['persona_instruction'] ?? '',
		);

		$filtered = apply_filters( 'od_faq_chatbot_ai_generate_answer', null, $prompt, $args, $question, $chunks );

		if ( is_string( $filtered ) && '' !== trim( $filtered ) ) {
			return trim( $filtered );
		}

		if ( is_wp_error( $filtered ) ) {
			return $filtered;
		}

		if ( function_exists( 'wp_ai_generate_text' ) ) {
			$response = wp_ai_generate_text( $prompt, $args );
			return $this->normalize_response( $response );
		}

		if ( function_exists( 'wp_ai_connector_generate_text' ) ) {
			$response = wp_ai_connector_generate_text( $prompt, $args );
			return $this->normalize_response( $response );
		}

		if ( function_exists( 'ai_connector_generate_text' ) ) {
			$response = ai_connector_generate_text( $prompt, $args );
			return $this->normalize_response( $response );
		}

		return new WP_Error( 'ai_connector_unavailable', __( 'AI 接続が設定されていないため回答を生成できません。', 'od-faq-chatbot' ) );
	}

	/**
	 * Builds the prompt sent to AI.
	 *
	 * @param string                          $question Question.
	 * @param array<int, array<string,mixed>> $chunks Chunks.
	 * @param array<string, mixed>            $settings Settings.
	 * @return string
	 */
	private function build_prompt( $question, $chunks, $settings ) {
		$context_lines = array();

		foreach ( $chunks as $index => $chunk ) {
			$context_lines[] = sprintf(
				"[参照 %d]\nタイトル: %s\nURL: %s\n見出し: %s\n本文:\n%s",
				$index + 1,
				$chunk['source_title'] ?? '',
				$chunk['source_url'] ?? $chunk['url'] ?? '',
				$chunk['heading'] ?? '',
				$chunk['text'] ?? ''
			);
		}

		return implode(
			"\n\n",
			array(
				(string) ( $settings['persona_instruction'] ?? '' ),
				(string) ( $settings['tone_instruction'] ?? '' ),
				'次の参照情報だけを根拠に回答してください。参照情報にない内容は推測せず「サイト上では確認できません」と回答してください。',
				'最大回答文字数: ' . absint( $settings['max_answer_length'] ?? 800 ),
				"参照情報:\n" . implode( "\n\n", $context_lines ),
				"質問:\n" . $question,
			)
		);
	}

	/**
	 * Normalizes different AI response shapes.
	 *
	 * @param mixed $response Response.
	 * @return string|WP_Error
	 */
	private function normalize_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( is_string( $response ) ) {
			return trim( $response );
		}

		if ( is_array( $response ) ) {
			foreach ( array( 'text', 'content', 'answer', 'message' ) as $key ) {
				if ( isset( $response[ $key ] ) && is_string( $response[ $key ] ) ) {
					return trim( $response[ $key ] );
				}
			}
		}

		if ( is_object( $response ) ) {
			foreach ( array( 'text', 'content', 'answer', 'message' ) as $key ) {
				if ( isset( $response->{$key} ) && is_string( $response->{$key} ) ) {
					return trim( $response->{$key} );
				}
			}
		}

		return new WP_Error( 'ai_response_invalid', __( '回答の生成中にエラーが発生しました。時間をおいて再度お試しください。', 'od-faq-chatbot' ) );
	}
}
