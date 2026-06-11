<?php
/**
 * Knowledge base builder.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and updates knowledge JSON.
 */
class OD_FAQ_Chatbot_Knowledge_Builder {

	/**
	 * Settings.
	 *
	 * @var OD_FAQ_Chatbot_Settings
	 */
	private $settings;

	/**
	 * Extractor.
	 *
	 * @var OD_FAQ_Chatbot_Content_Extractor
	 */
	private $extractor;

	/**
	 * Repository.
	 *
	 * @var OD_FAQ_Chatbot_Knowledge_Repository
	 */
	private $repository;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Settings             $settings Settings.
	 * @param OD_FAQ_Chatbot_Content_Extractor    $extractor Extractor.
	 * @param OD_FAQ_Chatbot_Knowledge_Repository $repository Repository.
	 */
	public function __construct( OD_FAQ_Chatbot_Settings $settings, OD_FAQ_Chatbot_Content_Extractor $extractor, OD_FAQ_Chatbot_Knowledge_Repository $repository ) {
		$this->settings   = $settings;
		$this->extractor  = $extractor;
		$this->repository = $repository;
	}

	/**
	 * Rebuilds all documents.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function rebuild_all() {
		$settings   = $this->settings->get_settings();
		$post_types = $settings['target_post_types'];

		if ( empty( $post_types ) ) {
			return new WP_Error( 'post_types_required', __( '対象投稿タイプを1つ以上選択してください。', 'od-faq-chatbot' ) );
		}

		$documents = $this->extractor->collect( $post_types );

		if ( empty( $documents ) ) {
			return new WP_Error( 'no_content', __( '学習対象の公開コンテンツがありません。', 'od-faq-chatbot' ) );
		}

		return $this->save_documents( $documents, $post_types );
	}

	/**
	 * Rebuilds only changed documents and removes missing documents.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function rebuild_changed() {
		$settings   = $this->settings->get_settings();
		$post_types = $settings['target_post_types'];

		if ( empty( $post_types ) ) {
			return new WP_Error( 'post_types_required', __( '対象投稿タイプを1つ以上選択してください。', 'od-faq-chatbot' ) );
		}

		$existing      = $this->repository->read_base();
		$existing_docs = array();
		$changed_docs  = $this->extractor->collect( $post_types );
		$changed_by_id = array();
		$documents     = array();

		if ( is_array( $existing ) && ! empty( $existing['documents'] ) && is_array( $existing['documents'] ) ) {
			foreach ( $existing['documents'] as $document ) {
				$existing_docs[ absint( $document['post_id'] ?? 0 ) ] = $document;
			}
		}

		foreach ( $changed_docs as $document ) {
			$post_id      = absint( $document['post_id'] );
			$existing_doc = $existing_docs[ $post_id ] ?? null;

			if ( is_array( $existing_doc ) && ( $existing_doc['content_hash'] ?? '' ) === $document['content_hash'] ) {
				$documents[] = $existing_doc;
			} else {
				$documents[] = $document;
			}

			$changed_by_id[ $post_id ] = true;
		}

		if ( empty( $documents ) ) {
			return new WP_Error( 'no_content', __( '学習対象の公開コンテンツがありません。', 'od-faq-chatbot' ) );
		}

		return $this->save_documents( $documents, $post_types );
	}

	/**
	 * Saves documents and meta.
	 *
	 * @param array<int, array<string, mixed>> $documents Documents.
	 * @param string[]                         $post_types Target post types.
	 * @return array<string, mixed>|WP_Error
	 */
	private function save_documents( $documents, $post_types ) {
		$chunk_count  = 0;
		$generated_at = current_time( 'c' );

		foreach ( $documents as $document ) {
			$chunk_count += count( $document['chunks'] ?? array() );
		}

		$base = array(
			'version'      => OD_FAQ_CHATBOT_VERSION,
			'generated_at' => $generated_at,
			'documents'    => array_values( $documents ),
		);

		$meta = array(
			'generated_at'      => $generated_at,
			'version'           => OD_FAQ_CHATBOT_VERSION,
			'target_post_types' => array_values( $post_types ),
			'document_count'    => count( $documents ),
			'chunk_count'       => $chunk_count,
		);

		$result = $this->repository->write( $base, $meta );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $meta;
	}
}
