<?php
/**
 * Knowledge base file repository.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes knowledge JSON files.
 */
class OD_FAQ_Chatbot_Knowledge_Repository {

	/**
	 * Security helper.
	 *
	 * @var OD_FAQ_Chatbot_Security
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Security $security Security helper.
	 */
	public function __construct( OD_FAQ_Chatbot_Security $security ) {
		$this->security = $security;
	}

	/**
	 * Returns knowledge base path.
	 *
	 * @return string
	 */
	public function get_base_path() {
		return trailingslashit( $this->security->get_storage_dir() ) . 'knowledge-base.json';
	}

	/**
	 * Returns knowledge meta path.
	 *
	 * @return string
	 */
	public function get_meta_path() {
		return trailingslashit( $this->security->get_storage_dir() ) . 'knowledge-meta.json';
	}

	/**
	 * Reads knowledge base data.
	 *
	 * @return array<string, mixed>|null
	 */
	public function read_base() {
		return $this->read_json( $this->get_base_path() );
	}

	/**
	 * Reads knowledge meta data.
	 *
	 * @return array<string, mixed>|null
	 */
	public function read_meta() {
		return $this->read_json( $this->get_meta_path() );
	}

	/**
	 * Writes knowledge files.
	 *
	 * @param array<string, mixed> $base Knowledge base.
	 * @param array<string, mixed> $meta Knowledge meta.
	 * @return true|WP_Error
	 */
	public function write( $base, $meta ) {
		if ( ! $this->security->ensure_storage() ) {
			return new WP_Error( 'storage_unavailable', __( 'ナレッジベースの保存に失敗しました。uploads ディレクトリの書き込み権限を確認してください。', 'od-faq-chatbot' ) );
		}

		$base_result = $this->write_json( $this->get_base_path(), $base );

		if ( is_wp_error( $base_result ) ) {
			return $base_result;
		}

		return $this->write_json( $this->get_meta_path(), $meta );
	}

	/**
	 * Reads JSON from a file.
	 *
	 * @param string $path File path.
	 * @return array<string, mixed>|null
	 */
	private function read_json( $path ) {
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return null;
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data     = json_decode( $contents, true );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Writes JSON to a file.
	 *
	 * @param string               $path File path.
	 * @param array<string, mixed> $data Data.
	 * @return true|WP_Error
	 */
	private function write_json( $path, $data ) {
		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		if ( false === $json ) {
			return new WP_Error( 'json_encode_failed', __( 'JSON データの生成に失敗しました。', 'od-faq-chatbot' ) );
		}

		$result = file_put_contents( $path, $json . "\n", LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		if ( false === $result ) {
			return new WP_Error( 'json_write_failed', __( 'ナレッジベースの保存に失敗しました。uploads ディレクトリの書き込み権限を確認してください。', 'od-faq-chatbot' ) );
		}

		return true;
	}
}
