<?php
/**
 * Keyword search over knowledge chunks.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finds relevant chunks for a question.
 */
class OD_FAQ_Chatbot_Search {

	/**
	 * Repository.
	 *
	 * @var OD_FAQ_Chatbot_Knowledge_Repository
	 */
	private $repository;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Knowledge_Repository $repository Repository.
	 */
	public function __construct( OD_FAQ_Chatbot_Knowledge_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Searches chunks.
	 *
	 * @param string $question Question.
	 * @param int    $limit Maximum chunks.
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	public function search( $question, $limit ) {
		$base = $this->repository->read_base();

		if ( ! is_array( $base ) || empty( $base['documents'] ) || ! is_array( $base['documents'] ) ) {
			return new WP_Error( 'knowledge_missing', __( '現在チャットを利用できません。サイト管理者がナレッジベースを生成する必要があります。', 'od-faq-chatbot' ) );
		}

		$tokens = $this->tokenize( $question );

		if ( empty( $tokens ) ) {
			return array();
		}

		$matches = array();

		foreach ( $base['documents'] as $document ) {
			foreach ( $document['chunks'] ?? array() as $chunk ) {
				$score = $this->score_chunk( $tokens, $document, $chunk );

				if ( $score <= 0 ) {
					continue;
				}

				$chunk['score']        = $score;
				$chunk['document_id']  = $document['id'] ?? '';
				$chunk['post_id']      = absint( $document['post_id'] ?? 0 );
				$chunk['source_title'] = $document['title'] ?? '';
				$chunk['source_url']   = $document['url'] ?? $chunk['url'];
				$matches[]             = $chunk;
			}
		}

		usort(
			$matches,
			static function ( $a, $b ) {
				return ( $b['score'] ?? 0 ) <=> ( $a['score'] ?? 0 );
			}
		);

		return array_slice( $matches, 0, max( 1, absint( $limit ) ) );
	}

	/**
	 * Scores a chunk against tokens.
	 *
	 * @param string[]             $tokens Tokens.
	 * @param array<string, mixed> $document Document.
	 * @param array<string, mixed> $chunk Chunk.
	 * @return int
	 */
	private function score_chunk( $tokens, $document, $chunk ) {
		$haystack = implode(
			' ',
			array(
				$document['title'] ?? '',
				$chunk['heading'] ?? '',
				$chunk['text'] ?? '',
				implode( ' ', $chunk['keywords'] ?? array() ),
			)
		);

		$score = 0;

		foreach ( $tokens as $token ) {
			if ( false !== mb_stripos( $haystack, $token ) ) {
				$score += false !== mb_stripos( (string) ( $chunk['heading'] ?? '' ), $token ) ? 3 : 1;
			}
		}

		return $score;
	}

	/**
	 * Tokenizes a question.
	 *
	 * @param string $question Question.
	 * @return string[]
	 */
	private function tokenize( $question ) {
		preg_match_all( '/[\p{L}\p{N}ー]{2,}/u', $question, $matches );
		$tokens = array_map( 'sanitize_text_field', $matches[0] ?? array() );
		$tokens = array_values( array_unique( $tokens ) );

		return array_slice( $tokens, 0, 30 );
	}
}
