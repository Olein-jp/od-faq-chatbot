<?php
/**
 * Display settings view.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap od-faq-chatbot-admin">
	<h1><?php echo esc_html__( '表示設定', 'od-faq-chatbot' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-section">
		<input type="hidden" name="action" value="od_faq_chatbot_save_settings">
		<?php wp_nonce_field( 'od_faq_chatbot_save_settings' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php echo esc_html__( 'チャット表示', 'od-faq-chatbot' ); ?></th>
				<td>
					<input type="hidden" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[chat_enabled]" value="0">
					<label>
						<input type="checkbox" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[chat_enabled]" value="1" <?php checked( ! empty( $settings['chat_enabled'] ) ); ?>>
						<?php echo esc_html__( '有効にする', 'od-faq-chatbot' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( '利用対象', 'od-faq-chatbot' ); ?></th>
				<td>
					<label><input type="radio" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[access_mode]" value="all" <?php checked( 'all', $settings['access_mode'] ); ?>> <?php echo esc_html__( '全訪問者', 'od-faq-chatbot' ); ?></label><br>
					<label><input type="radio" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[access_mode]" value="logged_in" <?php checked( 'logged_in', $settings['access_mode'] ); ?>> <?php echo esc_html__( 'ログインユーザーのみ', 'od-faq-chatbot' ); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-excluded-post-ids"><?php echo esc_html__( '除外ページ', 'od-faq-chatbot' ); ?></label></th>
				<td>
					<input id="od-faq-chatbot-excluded-post-ids" type="text" class="regular-text" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[excluded_post_ids]" value="<?php echo esc_attr( implode( ',', array_map( 'absint', $settings['excluded_post_ids'] ) ) ); ?>">
					<p class="description"><?php echo esc_html__( '除外する投稿 ID をカンマ区切りで入力してください。', 'od-faq-chatbot' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( '除外投稿タイプ', 'od-faq-chatbot' ); ?></th>
				<td>
					<?php foreach ( $post_types as $od_faq_chatbot_post_type ) : ?>
						<label class="od-faq-chatbot-check">
							<input type="checkbox" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[excluded_post_types][]" value="<?php echo esc_attr( $od_faq_chatbot_post_type->name ); ?>" <?php checked( in_array( $od_faq_chatbot_post_type->name, $settings['excluded_post_types'], true ) ); ?>>
							<?php echo esc_html( $od_faq_chatbot_post_type->labels->name ); ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'アンインストール時のデータ削除', 'od-faq-chatbot' ); ?></th>
				<td>
					<input type="hidden" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[delete_data_on_uninstall]" value="0">
					<label>
						<input type="checkbox" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[delete_data_on_uninstall]" value="1" <?php checked( ! empty( $settings['delete_data_on_uninstall'] ) ); ?>>
						<?php echo esc_html__( '設定、ナレッジベース、ログを削除する', 'od-faq-chatbot' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button( __( '表示設定を保存', 'od-faq-chatbot' ) ); ?>
	</form>
</div>
