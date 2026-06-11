<?php
/**
 * Floating chatbot template.
 *
 * @package OdFaqChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="od-faq-chatbot" data-od-faq-chatbot>
	<button class="od-faq-chatbot__toggle" type="button" aria-expanded="false" aria-controls="od-faq-chatbot-panel">
		<span class="screen-reader-text"><?php echo esc_html__( 'チャットを開く', 'od-faq-chatbot' ); ?></span>
		<span aria-hidden="true">FAQ</span>
	</button>
	<section id="od-faq-chatbot-panel" class="od-faq-chatbot__panel" aria-label="<?php echo esc_attr__( 'FAQチャット', 'od-faq-chatbot' ); ?>" hidden>
		<div class="od-faq-chatbot__header">
			<h2><?php echo esc_html__( 'FAQチャット', 'od-faq-chatbot' ); ?></h2>
			<button class="od-faq-chatbot__close" type="button"><?php echo esc_html__( '閉じる', 'od-faq-chatbot' ); ?></button>
		</div>
		<div class="od-faq-chatbot__messages" role="log" aria-live="polite"></div>
		<form class="od-faq-chatbot__form">
			<label class="screen-reader-text" for="od-faq-chatbot-question"><?php echo esc_html__( '質問を入力してください', 'od-faq-chatbot' ); ?></label>
			<textarea id="od-faq-chatbot-question" class="od-faq-chatbot__input" maxlength="500" rows="3" required></textarea>
			<button class="od-faq-chatbot__send" type="submit"><?php echo esc_html__( '送信', 'od-faq-chatbot' ); ?></button>
		</form>
		<p class="od-faq-chatbot__status" role="status" aria-live="polite"></p>
	</section>
</div>
