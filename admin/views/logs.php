<?php
/**
 * Logs view.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap od-faq-chatbot-admin">
	<h1><?php echo esc_html__( 'ログ', 'od-faq-chatbot' ); ?></h1>

	<div class="od-faq-chatbot-actions">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-inline-form">
			<input type="hidden" name="action" value="od_faq_chatbot_export_logs">
			<?php wp_nonce_field( 'od_faq_chatbot_export_logs' ); ?>
			<?php submit_button( __( 'CSV エクスポート', 'od-faq-chatbot' ), 'secondary', 'submit', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-inline-form">
			<input type="hidden" name="action" value="od_faq_chatbot_clear_logs">
			<?php wp_nonce_field( 'od_faq_chatbot_clear_logs' ); ?>
			<?php submit_button( __( 'ログを削除', 'od-faq-chatbot' ), 'delete', 'submit', false ); ?>
		</form>
	</div>

	<div class="od-faq-chatbot-section">
		<h2><?php echo esc_html__( '質問ログ', 'od-faq-chatbot' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( '日時', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '質問文', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( 'ステータス', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '参照元 URL', 'od-faq-chatbot' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $question_logs ) ) : ?>
					<tr><td colspan="4"><?php echo esc_html__( '質問ログはありません。', 'od-faq-chatbot' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $question_logs as $od_faq_chatbot_entry ) : ?>
					<tr>
						<td><?php echo esc_html( $od_faq_chatbot_entry['created_at'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_entry['question'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_entry['status'] ?? '' ); ?></td>
						<td>
							<?php foreach ( $od_faq_chatbot_entry['references'] ?? array() as $od_faq_chatbot_reference ) : ?>
								<a href="<?php echo esc_url( $od_faq_chatbot_reference['url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $od_faq_chatbot_reference['title'] ?? $od_faq_chatbot_reference['url'] ?? '' ); ?></a><br>
							<?php endforeach; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="od-faq-chatbot-section">
		<h2><?php echo esc_html__( '未回答質問ログ', 'od-faq-chatbot' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( '日時', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '質問文', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '理由', 'od-faq-chatbot' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $unanswered_logs ) ) : ?>
					<tr><td colspan="3"><?php echo esc_html__( '未回答質問ログはありません。', 'od-faq-chatbot' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $unanswered_logs as $od_faq_chatbot_entry ) : ?>
					<tr>
						<td><?php echo esc_html( $od_faq_chatbot_entry['created_at'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_entry['question'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_entry['reason'] ?? '' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
