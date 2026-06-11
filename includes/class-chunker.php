<?php
/**
 * Content chunking.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Splits normalized HTML into knowledge chunks.
 */
class OD_FAQ_Chatbot_Chunker {

	const MAX_CHUNK_LENGTH = 1200;

	/**
	 * Splits content by headings first, then by length.
	 *
	 * @param string $html Content HTML.
	 * @param int    $post_id Post ID.
	 * @param string $url Source URL.
	 * @return array<int, array<string, mixed>>
	 */
	public function chunk( $html, $post_id, $url ) {
		$sections = $this->split_by_headings( $html );
		$chunks   = array();
		$position = 1;

		foreach ( $sections as $section ) {
			$text_parts = $this->split_text( $section['text'], self::MAX_CHUNK_LENGTH );

			foreach ( $text_parts as $text ) {
				$text = trim( $text );

				if ( '' === $text ) {
					continue;
				}

				$chunks[] = array(
					'chunk_id' => sprintf( 'chunk_%d_%03d', absint( $post_id ), $position ),
					'heading'  => $section['heading'],
					'text'     => $text,
					'summary'  => $this->summarize( $text ),
					'keywords' => $this->extract_keywords( $section['heading'] . ' ' . $text ),
					'url'      => $url,
					'position' => $position,
				);

				++$position;
			}
		}

		return $chunks;
	}

	/**
	 * Splits HTML into heading sections.
	 *
	 * @param string $html Content HTML.
	 * @return array<int, array{heading:string,text:string}>
	 */
	private function split_by_headings( $html ) {
		$html = preg_replace( '#<(h[1-6])[^>]*>#i', "\n<$1>", $html );
		$html = preg_replace( '#</(h[1-6])>#i', "</$1>\n", (string) $html );
		$rows = preg_split( "/\n+/", (string) $html );

		$sections        = array();
		$current_heading = '';
		$current_text    = '';

		foreach ( $rows as $row ) {
			if ( preg_match( '#<h[1-6]>(.*?)</h[1-6]>#is', $row, $matches ) ) {
				if ( '' !== trim( $current_text ) ) {
					$sections[] = array(
						'heading' => $current_heading,
						'text'    => $this->normalize_text( $current_text ),
					);
				}

				$current_heading = $this->normalize_text( $matches[1] );
				$current_text    = '';
				continue;
			}

			$current_text .= "\n" . $row;
		}

		if ( '' !== trim( $current_text ) ) {
			$sections[] = array(
				'heading' => $current_heading,
				'text'    => $this->normalize_text( $current_text ),
			);
		}

		if ( empty( $sections ) ) {
			$sections[] = array(
				'heading' => '',
				'text'    => $this->normalize_text( $html ),
			);
		}

		return $sections;
	}

	/**
	 * Splits long text into size-limited chunks.
	 *
	 * @param string $text Text.
	 * @param int    $max_length Maximum length.
	 * @return string[]
	 */
	private function split_text( $text, $max_length ) {
		if ( $this->length( $text ) <= $max_length ) {
			return array( $text );
		}

		$sentences = preg_split( '/(?<=[。．！？!?])\s*/u', $text, -1, PREG_SPLIT_NO_EMPTY );
		$chunks    = array();
		$current   = '';

		foreach ( $sentences as $sentence ) {
			if ( $this->length( $current . $sentence ) > $max_length && '' !== $current ) {
				$chunks[] = $current;
				$current  = '';
			}

			$current .= $sentence;
		}

		if ( '' !== $current ) {
			$chunks[] = $current;
		}

		return $chunks;
	}

	/**
	 * Normalizes text.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private function normalize_text( $text ) {
		$text = wp_strip_all_tags( $text );
		$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$text = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $text );
		$text = preg_replace( '/[ \t]+/u', ' ', (string) $text );
		$text = preg_replace( "/\n{3,}/u", "\n\n", (string) $text );

		return trim( (string) $text );
	}

	/**
	 * Creates a short summary.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private function summarize( $text ) {
		if ( $this->length( $text ) <= 120 ) {
			return $text;
		}

		return $this->substring( $text, 0, 120 ) . '...';
	}

	/**
	 * Extracts basic keywords.
	 *
	 * @param string $text Text.
	 * @return string[]
	 */
	private function extract_keywords( $text ) {
		preg_match_all( '/[\p{L}\p{N}ー]{2,}/u', $text, $matches );
		$keywords = array_slice( array_values( array_unique( $matches[0] ?? array() ) ), 0, 20 );

		return array_map( 'sanitize_text_field', $keywords );
	}

	/**
	 * Returns text length.
	 *
	 * @param string $text Text.
	 * @return int
	 */
	private function length( $text ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
	}

	/**
	 * Returns text substring.
	 *
	 * @param string $text Text.
	 * @param int    $start Start.
	 * @param int    $length Length.
	 * @return string
	 */
	private function substring( $text, $start, $length ) {
		return function_exists( 'mb_substr' ) ? mb_substr( $text, $start, $length ) : substr( $text, $start, $length );
	}
}
