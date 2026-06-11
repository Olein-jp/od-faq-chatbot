<?php
/**
 * Dashboard view.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap od-faq-chatbot-admin">
	<h1><?php echo esc_html__( 'AI FAQチャットボット', 'od-faq-chatbot' ); ?></h1>

	<div class="od-faq-chatbot-grid">
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( 'プラグイン状態', 'od-faq-chatbot' ); ?></h2>
			<p><?php echo esc_html__( '有効', 'od-faq-chatbot' ); ?></p>
		</div>
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( 'AI Connector 接続状態', 'od-faq-chatbot' ); ?></h2>
			<p><?php echo $ai_available ? esc_html__( '利用可能', 'od-faq-chatbot' ) : esc_html__( '未検出', 'od-faq-chatbot' ); ?></p>
		</div>
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( '最終生成日時', 'od-faq-chatbot' ); ?></h2>
			<p><?php echo esc_html( $knowledge_meta['generated_at'] ?? __( '未生成', 'od-faq-chatbot' ) ); ?></p>
		</div>
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( '学習済みページ数', 'od-faq-chatbot' ); ?></h2>
			<p><?php echo esc_html( (string) ( $knowledge_meta['document_count'] ?? 0 ) ); ?></p>
		</div>
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( 'チャンク数', 'od-faq-chatbot' ); ?></h2>
			<p><?php echo esc_html( (string) ( $knowledge_meta['chunk_count'] ?? 0 ) ); ?></p>
		</div>
		<div class="od-faq-chatbot-card">
			<h2><?php echo esc_html__( 'ログ件数', 'od-faq-chatbot' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: question count, 2: unanswered count */
					esc_html__( '質問: %1$d / 未回答: %2$d', 'od-faq-chatbot' ),
					absint( $question_count ),
					absint( $unanswered_count )
				);
				?>
			</p>
		</div>
	</div>

	<p class="od-faq-chatbot-actions">
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=od-faq-chatbot-knowledge' ) ); ?>"><?php echo esc_html__( 'ナレッジベースを開く', 'od-faq-chatbot' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=od-faq-chatbot-chat-settings' ) ); ?>"><?php echo esc_html__( 'チャット設定を開く', 'od-faq-chatbot' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=od-faq-chatbot-logs' ) ); ?>"><?php echo esc_html__( 'ログを開く', 'od-faq-chatbot' ); ?></a>
	</p>
</div>
