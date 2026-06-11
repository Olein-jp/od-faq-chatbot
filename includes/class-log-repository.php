<?php
/**
 * Chat log repository.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appends and reads JSONL logs.
 */
class OD_FAQ_Chatbot_Log_Repository {

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
	 * Appends a question log.
	 *
	 * @param array<string, mixed> $entry Log entry.
	 * @return bool
	 */
	public function append_question( $entry ) {
		return $this->append( 'question-logs.jsonl', $entry );
	}

	/**
	 * Appends an unanswered log.
	 *
	 * @param array<string, mixed> $entry Log entry.
	 * @return bool
	 */
	public function append_unanswered( $entry ) {
		return $this->append( 'unanswered-logs.jsonl', $entry );
	}

	/**
	 * Reads log entries.
	 *
	 * @param string $type Log type.
	 * @param int    $limit Maximum entries.
	 * @return array<int, array<string, mixed>>
	 */
	public function read( $type, $limit = 100 ) {
		$file = 'unanswered' === $type ? 'unanswered-logs.jsonl' : 'question-logs.jsonl';
		$path = $this->get_log_path( $file );

		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return array();
		}

		$lines   = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file
		$lines   = array_slice( array_reverse( $lines ), 0, absint( $limit ) );
		$entries = array();

		foreach ( $lines as $line ) {
			$entry = json_decode( $line, true );

			if ( is_array( $entry ) ) {
				$entries[] = $entry;
			}
		}

		return $entries;
	}

	/**
	 * Deletes log files.
	 *
	 * @return void
	 */
	public function clear() {
		$files = array( 'question-logs.jsonl', 'unanswered-logs.jsonl' );

		foreach ( $files as $file ) {
			$path = $this->get_log_path( $file );

			if ( file_exists( $path ) ) {
				unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}
	}

	/**
	 * Returns count of log entries.
	 *
	 * @param string $type Log type.
	 * @return int
	 */
	public function count( $type ) {
		$file = 'unanswered' === $type ? 'unanswered-logs.jsonl' : 'question-logs.jsonl';
		$path = $this->get_log_path( $file );

		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return 0;
		}

		$lines = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file

		return is_array( $lines ) ? count( $lines ) : 0;
	}

	/**
	 * Appends a JSONL entry.
	 *
	 * @param string               $file File name.
	 * @param array<string, mixed> $entry Entry.
	 * @return bool
	 */
	private function append( $file, $entry ) {
		if ( ! $this->security->ensure_storage() ) {
			error_log( 'OD FAQ Chatbot: failed to prepare log storage.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		$json = wp_json_encode( $entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		if ( false === $json ) {
			error_log( 'OD FAQ Chatbot: failed to encode log entry.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		$result = file_put_contents( $this->get_log_path( $file ), $json . "\n", FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		if ( false === $result ) {
			error_log( 'OD FAQ Chatbot: failed to write log entry.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		return true;
	}

	/**
	 * Returns a log file path.
	 *
	 * @param string $file File name.
	 * @return string
	 */
	private function get_log_path( $file ) {
		return trailingslashit( $this->security->get_storage_dir() ) . 'logs/' . $file;
	}
}
