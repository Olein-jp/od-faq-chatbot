<?php
/**
 * Admin screens.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers admin pages and actions.
 */
class OD_FAQ_Chatbot_Admin {

	/**
	 * Settings.
	 *
	 * @var OD_FAQ_Chatbot_Settings
	 */
	private $settings;

	/**
	 * Knowledge repository.
	 *
	 * @var OD_FAQ_Chatbot_Knowledge_Repository
	 */
	private $knowledge_repository;

	/**
	 * Knowledge builder.
	 *
	 * @var OD_FAQ_Chatbot_Knowledge_Builder
	 */
	private $knowledge_builder;

	/**
	 * Log repository.
	 *
	 * @var OD_FAQ_Chatbot_Log_Repository
	 */
	private $logs;

	/**
	 * AI client.
	 *
	 * @var OD_FAQ_Chatbot_AI_Client
	 */
	private $ai_client;

	/**
	 * Constructor.
	 *
	 * @param OD_FAQ_Chatbot_Settings             $settings Settings.
	 * @param OD_FAQ_Chatbot_Knowledge_Repository $knowledge_repository Repository.
	 * @param OD_FAQ_Chatbot_Knowledge_Builder    $knowledge_builder Builder.
	 * @param OD_FAQ_Chatbot_Log_Repository       $logs Logs.
	 * @param OD_FAQ_Chatbot_AI_Client            $ai_client AI client.
	 */
	public function __construct( OD_FAQ_Chatbot_Settings $settings, OD_FAQ_Chatbot_Knowledge_Repository $knowledge_repository, OD_FAQ_Chatbot_Knowledge_Builder $knowledge_builder, OD_FAQ_Chatbot_Log_Repository $logs, OD_FAQ_Chatbot_AI_Client $ai_client ) {
		$this->settings             = $settings;
		$this->knowledge_repository = $knowledge_repository;
		$this->knowledge_builder    = $knowledge_builder;
		$this->logs                 = $logs;
		$this->ai_client            = $ai_client;
	}

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_od_faq_chatbot_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_post_od_faq_chatbot_generate_knowledge', array( $this, 'handle_generate_knowledge' ) );
		add_action( 'admin_post_od_faq_chatbot_clear_logs', array( $this, 'handle_clear_logs' ) );
		add_action( 'admin_post_od_faq_chatbot_export_logs', array( $this, 'handle_export_logs' ) );
	}

	/**
	 * Registers admin menu pages.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'AI FAQチャットボット', 'od-faq-chatbot' ),
			__( 'AI FAQ', 'od-faq-chatbot' ),
			'manage_options',
			'od-faq-chatbot',
			array( $this, 'render_dashboard' ),
			'dashicons-format-chat',
			58
		);

		$pages = array(
			'knowledge'        => __( 'ナレッジベース', 'od-faq-chatbot' ),
			'chat-settings'    => __( 'チャット設定', 'od-faq-chatbot' ),
			'display-settings' => __( '表示設定', 'od-faq-chatbot' ),
			'logs'             => __( 'ログ', 'od-faq-chatbot' ),
		);

		foreach ( $pages as $slug => $title ) {
			add_submenu_page(
				'od-faq-chatbot',
				$title,
				$title,
				'manage_options',
				'od-faq-chatbot-' . $slug,
				array( $this, 'render_' . str_replace( '-', '_', $slug ) )
			);
		}
	}

	/**
	 * Enqueues admin assets.
	 *
	 * @param string $hook_suffix Hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'od-faq-chatbot' ) ) {
			return;
		}

		wp_enqueue_style(
			'od-faq-chatbot-admin',
			OD_FAQ_CHATBOT_URL . 'admin/assets/admin.css',
			array(),
			OD_FAQ_CHATBOT_VERSION
		);
	}

	/**
	 * Renders dashboard.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->render_view( 'dashboard' );
	}

	/**
	 * Renders knowledge page.
	 *
	 * @return void
	 */
	public function render_knowledge() {
		$this->render_view( 'knowledge' );
	}

	/**
	 * Renders chat settings page.
	 *
	 * @return void
	 */
	public function render_chat_settings() {
		$this->render_view( 'chat-settings' );
	}

	/**
	 * Renders display settings page.
	 *
	 * @return void
	 */
	public function render_display_settings() {
		$this->render_view( 'display-settings' );
	}

	/**
	 * Renders logs page.
	 *
	 * @return void
	 */
	public function render_logs() {
		$this->render_view( 'logs' );
	}

	/**
	 * Saves settings from custom admin forms.
	 *
	 * @return void
	 */
	public function handle_save_settings() {
		$this->verify_admin_action( 'od_faq_chatbot_save_settings' );

		$current = $this->settings->get_settings();
		$input   = isset( $_POST[ OD_FAQ_CHATBOT_OPTION ] ) && is_array( $_POST[ OD_FAQ_CHATBOT_OPTION ] ) ? wp_unslash( $_POST[ OD_FAQ_CHATBOT_OPTION ] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$this->settings->update_settings( wp_parse_args( $input, $current ) );
		$this->redirect_with_message( wp_get_referer(), 'settings_saved' );
	}

	/**
	 * Generates the knowledge base.
	 *
	 * @return void
	 */
	public function handle_generate_knowledge() {
		$this->verify_admin_action( 'od_faq_chatbot_generate_knowledge' );

		$mode   = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$result = 'changed' === $mode ? $this->knowledge_builder->rebuild_changed() : $this->knowledge_builder->rebuild_all();

		if ( is_wp_error( $result ) ) {
			$this->redirect_with_message( wp_get_referer(), 'knowledge_error', $result->get_error_message() );
		}

		$this->redirect_with_message( wp_get_referer(), 'knowledge_generated' );
	}

	/**
	 * Clears logs.
	 *
	 * @return void
	 */
	public function handle_clear_logs() {
		$this->verify_admin_action( 'od_faq_chatbot_clear_logs' );
		$this->logs->clear();
		$this->redirect_with_message( wp_get_referer(), 'logs_cleared' );
	}

	/**
	 * Exports question logs as CSV.
	 *
	 * @return void
	 */
	public function handle_export_logs() {
		$this->verify_admin_action( 'od_faq_chatbot_export_logs' );

		$entries = $this->logs->read( 'question', 10000 );

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=od-faq-chatbot-question-logs.csv' );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $output, array( 'created_at', 'question', 'answer', 'status', 'references' ) );

		foreach ( $entries as $entry ) {
			fputcsv(
				$output,
				array(
					$entry['created_at'] ?? '',
					$entry['question'] ?? '',
					$entry['answer'] ?? '',
					$entry['status'] ?? '',
					wp_json_encode( $entry['references'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
				)
			);
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Renders a view.
	 *
	 * @param string $view View name.
	 * @return void
	 */
	private function render_view( $view ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'このページにアクセスする権限がありません。', 'od-faq-chatbot' ) );
		}

		$settings         = $this->settings->get_settings();
		$post_types       = $this->settings->get_public_post_types();
		$knowledge_meta   = $this->knowledge_repository->read_meta();
		$knowledge_base   = $this->knowledge_repository->read_base();
		$question_logs    = $this->logs->read( 'question', 100 );
		$unanswered_logs  = $this->logs->read( 'unanswered', 100 );
		$question_count   = $this->logs->count( 'question' );
		$unanswered_count = $this->logs->count( 'unanswered' );
		$ai_available     = $this->ai_client->is_available();
		$view_path        = OD_FAQ_CHATBOT_PATH . 'admin/views/' . $view . '.php';

		$this->render_notice();

		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}

	/**
	 * Renders admin notices from redirect parameters.
	 *
	 * @return void
	 */
	private function render_notice() {
		$message_key = isset( $_GET['od_faq_chatbot_message'] ) ? sanitize_key( wp_unslash( $_GET['od_faq_chatbot_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error       = isset( $_GET['od_faq_chatbot_error'] ) ? sanitize_text_field( wp_unslash( $_GET['od_faq_chatbot_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( '' === $message_key && '' === $error ) {
			return;
		}

		$messages = array(
			'settings_saved'      => __( '設定を保存しました。', 'od-faq-chatbot' ),
			'knowledge_generated' => __( 'ナレッジベースを生成しました。', 'od-faq-chatbot' ),
			'logs_cleared'        => __( 'ログを削除しました。', 'od-faq-chatbot' ),
		);

		if ( '' !== $error ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
			return;
		}

		if ( isset( $messages[ $message_key ] ) ) {
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $messages[ $message_key ] ) );
		}
	}

	/**
	 * Verifies admin action.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	private function verify_admin_action( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'この操作を実行する権限がありません。', 'od-faq-chatbot' ) );
		}

		check_admin_referer( $action );
	}

	/**
	 * Redirects with a message.
	 *
	 * @param string|false $url URL.
	 * @param string       $message Message key.
	 * @param string       $error Error message.
	 * @return void
	 */
	private function redirect_with_message( $url, $message, $error = '' ) {
		$url = $url ? $url : admin_url( 'admin.php?page=od-faq-chatbot' );
		$url = remove_query_arg( array( 'od_faq_chatbot_message', 'od_faq_chatbot_error' ), $url );

		if ( '' !== $error ) {
			$url = add_query_arg( 'od_faq_chatbot_error', rawurlencode( $error ), $url );
		} else {
			$url = add_query_arg( 'od_faq_chatbot_message', $message, $url );
		}

		wp_safe_redirect( $url );
		exit;
	}
}
