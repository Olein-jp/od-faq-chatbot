(function () {
	'use strict';

	var root = document.querySelector('[data-od-faq-chatbot]');

	if (!root || !window.ODFaqChatbot) {
		return;
	}

	var toggle = root.querySelector('.od-faq-chatbot__toggle');
	var panel = root.querySelector('.od-faq-chatbot__panel');
	var close = root.querySelector('.od-faq-chatbot__close');
	var form = root.querySelector('.od-faq-chatbot__form');
	var input = root.querySelector('.od-faq-chatbot__input');
	var send = root.querySelector('.od-faq-chatbot__send');
	var messages = root.querySelector('.od-faq-chatbot__messages');
	var status = root.querySelector('.od-faq-chatbot__status');
	var lastFocus = null;

	function setOpen(isOpen) {
		toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		panel.hidden = !isOpen;

		if (isOpen) {
			lastFocus = document.activeElement;
			input.focus();
		} else {
			(lastFocus || toggle).focus();
		}
	}

	function appendMessage(type, text, references) {
		var message = document.createElement('div');
		message.className = 'od-faq-chatbot__message od-faq-chatbot__message--' + type;
		message.textContent = text;

		if (references && references.length) {
			var title = document.createElement('strong');
			var list = document.createElement('ul');
			title.textContent = ODFaqChatbot.labels.references;
			list.className = 'od-faq-chatbot__references';

			references.forEach(function (reference) {
				var item = document.createElement('li');
				var link = document.createElement('a');
				link.href = reference.url;
				link.textContent = reference.title || reference.url;
				link.target = '_blank';
				link.rel = 'noopener noreferrer';
				item.appendChild(link);
				list.appendChild(item);
			});

			message.appendChild(document.createElement('br'));
			message.appendChild(title);
			message.appendChild(list);
		}

		messages.appendChild(message);
		messages.scrollTop = messages.scrollHeight;
	}

	function setLoading(isLoading) {
		send.disabled = isLoading;
		status.textContent = isLoading ? ODFaqChatbot.labels.loading : '';
	}

	function ask(question) {
		setLoading(true);

		return window.fetch(ODFaqChatbot.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': ODFaqChatbot.nonce
			},
			body: JSON.stringify({ question: question })
		})
			.then(function (response) {
				return response.json().then(function (data) {
					if (!response.ok) {
						throw new Error(data.message || ODFaqChatbot.labels.error);
					}

					return data;
				});
			})
			.then(function (data) {
				appendMessage('bot', data.answer, data.references || []);
			})
			.catch(function (error) {
				appendMessage('bot', error.message || ODFaqChatbot.labels.error, []);
			})
			.finally(function () {
				setLoading(false);
			});
	}

	toggle.addEventListener('click', function () {
		setOpen(panel.hidden);
	});

	close.addEventListener('click', function () {
		setOpen(false);
	});

	form.addEventListener('submit', function (event) {
		var question = input.value.trim();
		event.preventDefault();

		if (!question) {
			status.textContent = ODFaqChatbot.labels.question;
			input.focus();
			return;
		}

		appendMessage('user', question, []);
		input.value = '';
		ask(question);
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' === event.key && !panel.hidden) {
			setOpen(false);
		}
	});

	appendMessage('bot', ODFaqChatbot.initialMessage, []);

	if (ODFaqChatbot.privacyNotice) {
		appendMessage('bot', ODFaqChatbot.privacyNotice, []);
	}
}());
