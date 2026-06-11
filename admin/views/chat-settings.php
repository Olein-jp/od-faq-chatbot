<?php
/**
 * Chat settings view.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap od-faq-chatbot-admin">
	<h1><?php echo esc_html__( 'チャット設定', 'od-faq-chatbot' ); ?></h1>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="od-faq-chatbot-section">
		<input type="hidden" name="action" value="od_faq_chatbot_save_settings">
		<?php wp_nonce_field( 'od_faq_chatbot_save_settings' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="od-faq-chatbot-initial-message"><?php echo esc_html__( '初回メッセージ', 'od-faq-chatbot' ); ?></label></th>
				<td><textarea id="od-faq-chatbot-initial-message" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[initial_message]" rows="3" class="large-text"><?php echo esc_textarea( $settings['initial_message'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-tone"><?php echo esc_html__( '回答口調', 'od-faq-chatbot' ); ?></label></th>
				<td><textarea id="od-faq-chatbot-tone" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[tone_instruction]" rows="5" class="large-text"><?php echo esc_textarea( $settings['tone_instruction'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-persona"><?php echo esc_html__( 'AI 人格設定', 'od-faq-chatbot' ); ?></label></th>
				<td><textarea id="od-faq-chatbot-persona" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[persona_instruction]" rows="5" class="large-text"><?php echo esc_textarea( $settings['persona_instruction'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-no-answer"><?php echo esc_html__( '回答不能時メッセージ', 'od-faq-chatbot' ); ?></label></th>
				<td><textarea id="od-faq-chatbot-no-answer" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[no_answer_message]" rows="3" class="large-text"><?php echo esc_textarea( $settings['no_answer_message'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-privacy"><?php echo esc_html__( '注意文', 'od-faq-chatbot' ); ?></label></th>
				<td><textarea id="od-faq-chatbot-privacy" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[privacy_notice]" rows="3" class="large-text"><?php echo esc_textarea( $settings['privacy_notice'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-max-chunks"><?php echo esc_html__( '最大参照チャンク数', 'od-faq-chatbot' ); ?></label></th>
				<td><input id="od-faq-chatbot-max-chunks" type="number" min="1" max="10" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[max_chunks]" value="<?php echo esc_attr( (string) $settings['max_chunks'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-max-answer"><?php echo esc_html__( '最大回答文字数', 'od-faq-chatbot' ); ?></label></th>
				<td><input id="od-faq-chatbot-max-answer" type="number" min="100" max="2000" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[max_answer_length]" value="<?php echo esc_attr( (string) $settings['max_answer_length'] ); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="od-faq-chatbot-retention"><?php echo esc_html__( 'ログ保存期間', 'od-faq-chatbot' ); ?></label></th>
				<td><input id="od-faq-chatbot-retention" type="number" min="1" name="<?php echo esc_attr( OD_FAQ_CHATBOT_OPTION ); ?>[log_retention_days]" value="<?php echo esc_attr( (string) $settings['log_retention_days'] ); ?>"> <?php echo esc_html__( '日', 'od-faq-chatbot' ); ?></td>
			</tr>
		</table>
		<?php submit_button( __( '設定を保存', 'od-faq-chatbot' ) ); ?>
	</form>
</div>
