<?php
/**
 * Extracts public WordPress content for learning.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collects published posts and normalizes content.
 */
class OD_FAQ_Chatbot_Content_Extractor {

	/**
	 * Chunker.
	 *
	 * @var OD_FAQ_Chatbot_Chunker
	 */
	private $chunker;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Chunker $chunker Chunker.
	 */
	public function __construct( OD_FAQ_Chatbot_Chunker $chunker ) {
		$this->chunker = $chunker;
	}

	/**
	 * Collects published documents.
	 *
	 * @param string[] $post_types Target post types.
	 * @return array<int, array<string, mixed>>
	 */
	public function collect( $post_types ) {
		$documents = array();
		$query     = new WP_Query(
			array(
				'post_type'              => $post_types,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $query->posts as $post ) {
			$document = $this->build_document( $post );

			if ( ! empty( $document['chunks'] ) ) {
				$documents[] = $document;
			}
		}

		wp_reset_postdata();

		return $documents;
	}

	/**
	 * Builds a document from a post.
	 *
	 * @param WP_Post $post Post object.
	 * @return array<string, mixed>
	 */
	public function build_document( WP_Post $post ) {
		$clean_html = $this->clean_html( $post->post_content );
		$text       = trim( wp_strip_all_tags( $clean_html ) );
		$hash       = substr( hash( 'sha256', $post->post_title . "\n" . $text . "\n" . $post->post_modified_gmt ), 0, 16 );
		$learned_at = current_time( 'c' );
		$post_id    = absint( $post->ID );
		$url        = get_permalink( $post );
		$chunks     = $this->chunker->chunk( $clean_html, $post_id, $url );

		return array(
			'id'           => 'doc_' . $post_id,
			'post_id'      => $post_id,
			'post_type'    => $post->post_type,
			'title'        => get_the_title( $post ),
			'url'          => $url,
			'status'       => $post->post_status,
			'modified_at'  => get_post_modified_time( 'c', false, $post ),
			'learned_at'   => $learned_at,
			'content_hash' => $hash,
			'chunks'       => $chunks,
		);
	}

	/**
	 * Removes unsafe and noisy markup while preserving headings for chunking.
	 *
	 * @param string $html Raw HTML.
	 * @return string
	 */
	private function clean_html( $html ) {
		$html = strip_shortcodes( $html );
		$html = preg_replace( '#<(script|style|noscript|iframe|svg|canvas)[^>]*>.*?</\1>#is', '', $html );
		$html = preg_replace( '#<!--.*?-->#s', '', (string) $html );
		$html = wpautop( $html );

		return wp_kses(
			$html,
			array(
				'h1' => array(),
				'h2' => array(),
				'h3' => array(),
				'h4' => array(),
				'h5' => array(),
				'h6' => array(),
				'p'  => array(),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
				'br' => array(),
			)
		);
	}
}
