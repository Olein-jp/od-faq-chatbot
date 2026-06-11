<?php
/**
 * Plugin uninstall routine.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$od_faq_chatbot_settings = get_option( 'ai_faq_chatbot_settings', array() );

if ( is_array( $od_faq_chatbot_settings ) && ! empty( $od_faq_chatbot_settings['delete_data_on_uninstall'] ) ) {
	delete_option( 'ai_faq_chatbot_settings' );

	$od_faq_chatbot_upload_dir = wp_upload_dir();
	$od_faq_chatbot_base_dir   = trailingslashit( $od_faq_chatbot_upload_dir['basedir'] ) . 'ai-faq-chatbot';

	if ( is_dir( $od_faq_chatbot_base_dir ) ) {
		$od_faq_chatbot_files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $od_faq_chatbot_base_dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $od_faq_chatbot_files as $od_faq_chatbot_file ) {
			if ( $od_faq_chatbot_file->isDir() ) {
				rmdir( $od_faq_chatbot_file->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			} else {
				wp_delete_file( $od_faq_chatbot_file->getPathname() );
			}
		}

		rmdir( $od_faq_chatbot_base_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
}
