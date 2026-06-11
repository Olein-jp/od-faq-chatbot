<?php
/**
 * Storage directory security.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensures uploads storage is protected.
 */
class OD_FAQ_Chatbot_Security {

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'ensure_storage' ) );
	}

	/**
	 * Returns the base storage directory.
	 *
	 * @return string
	 */
	public function get_storage_dir() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] ) . OD_FAQ_CHATBOT_STORAGE_DIR;
	}

	/**
	 * Ensures base and log directories exist and are protected.
	 *
	 * @return bool
	 */
	public function ensure_storage() {
		$base_dir = $this->get_storage_dir();
		$logs_dir = trailingslashit( $base_dir ) . 'logs';

		if ( ! wp_mkdir_p( $logs_dir ) ) {
			return false;
		}

		$this->write_protection_files( $base_dir );
		$this->write_protection_files( $logs_dir );

		return true;
	}

	/**
	 * Removes all plugin storage files.
	 *
	 * @return void
	 */
	public function delete_storage() {
		$base_dir = $this->get_storage_dir();

		if ( ! is_dir( $base_dir ) ) {
			return;
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $base_dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			} else {
				wp_delete_file( $file->getPathname() );
			}
		}

		rmdir( $base_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}

	/**
	 * Writes index.php and .htaccess files.
	 *
	 * @param string $dir Target directory.
	 * @return void
	 */
	private function write_protection_files( $dir ) {
		$index_file   = trailingslashit( $dir ) . 'index.php';
		$htaccess     = trailingslashit( $dir ) . '.htaccess';
		$index_source = "<?php\n// Silence is golden.\n";
		$deny_source  = "<FilesMatch \"\\.(json|jsonl)$\">\n\tRequire all denied\n</FilesMatch>\n";

		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, $index_source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, $deny_source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}
}
