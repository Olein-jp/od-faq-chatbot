<?php
/**
 * Knowledge base view.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$od_faq_chatbot_documents = is_array( $knowledge_base['documents'] ?? null ) ? $knowledge_base['documents'] : array();
?>
<div class="wrap od-faq-chatbot-admin">
	<h1><?php echo esc_html__( 'ナレッジベース', 'od-faq-chatbot' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-section">
		<input type="hidden" name="action" value="od_faq_chatbot_save_settings">
		<?php wp_nonce_field( 'od_faq_chatbot_save_settings' ); ?>
		<h2><?php echo esc_html__( '対象投稿タイプ', 'od-faq-chatbot' ); ?></h2>
		<p><?php echo esc_html__( '公開済みコンテンツをナレッジベース化する投稿タイプを選択してください。', 'od-faq-chatbot' ); ?></p>
		<fieldset>
			<?php foreach ( $post_types as $od_faq_chatbot_post_type ) : ?>
				<label class="od-faq-chatbot-check">
					<input type="checkbox" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[target_post_types][]" value="<?php echo esc_attr( $od_faq_chatbot_post_type->name ); ?>" <?php checked( in_array( $od_faq_chatbot_post_type->name, $settings['target_post_types'], true ) ); ?>>
					<?php echo esc_html( $od_faq_chatbot_post_type->labels->name ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<?php submit_button( __( '対象投稿タイプを保存', 'od-faq-chatbot' ) ); ?>
	</form>

	<div class="od-faq-chatbot-section">
		<h2><?php echo esc_html__( '生成', 'od-faq-chatbot' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: date, 2: documents, 3: chunks */
				esc_html__( '最終生成日時: %1$s / 学習済みページ数: %2$d / チャンク数: %3$d', 'od-faq-chatbot' ),
				esc_html( $knowledge_meta['generated_at'] ?? __( '未生成', 'od-faq-chatbot' ) ),
				absint( $knowledge_meta['document_count'] ?? 0 ),
				absint( $knowledge_meta['chunk_count'] ?? 0 )
			);
			?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-inline-form">
			<input type="hidden" name="action" value="od_faq_chatbot_generate_knowledge">
			<input type="hidden" name="mode" value="all">
			<?php wp_nonce_field( 'od_faq_chatbot_generate_knowledge' ); ?>
			<?php submit_button( __( '全件再生成', 'od-faq-chatbot' ), 'primary', 'submit', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-inline-form">
			<input type="hidden" name="action" value="od_faq_chatbot_generate_knowledge">
			<input type="hidden" name="mode" value="changed">
			<?php wp_nonce_field( 'od_faq_chatbot_generate_knowledge' ); ?>
			<?php submit_button( __( '変更コンテンツのみ再生成', 'od-faq-chatbot' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>

	<div class="od-faq-chatbot-section">
		<h2><?php echo esc_html__( '学習済みページ一覧', 'od-faq-chatbot' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'タイトル', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '投稿タイプ', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '最終更新日時', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( '最終学習日時', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( 'チャンク数', 'od-faq-chatbot' ); ?></th>
					<th><?php echo esc_html__( 'URL', 'od-faq-chatbot' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $od_faq_chatbot_documents ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'まだ学習済みページはありません。', 'od-faq-chatbot' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $od_faq_chatbot_documents as $od_faq_chatbot_document ) : ?>
					<tr>
						<td><?php echo esc_html( $od_faq_chatbot_document['title'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_document['post_type'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_document['modified_at'] ?? '' ); ?></td>
						<td><?php echo esc_html( $od_faq_chatbot_document['learned_at'] ?? '' ); ?></td>
						<td><?php echo esc_html( (string) count( $od_faq_chatbot_document['chunks'] ?? array() ) ); ?></td>
						<td><a href="<?php echo esc_url( $od_faq_chatbot_document['url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( '開く', 'od-faq-chatbot' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
