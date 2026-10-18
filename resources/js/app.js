const navigationToggle = document.querySelector('[data-navigation-toggle]');
const navigation = document.querySelector('#main-navigation');

if (navigationToggle && navigation) {
	const closeNavigation = () => {
		navigation.classList.add('hidden');
		navigationToggle.setAttribute('aria-expanded', 'false');
	};

	navigationToggle.addEventListener('click', () => {
		const isExpanded = navigationToggle.getAttribute('aria-expanded') === 'true';

		navigation.classList.toggle('hidden', isExpanded);
		navigationToggle.setAttribute('aria-expanded', String(!isExpanded));
	});

	navigation.addEventListener('click', (event) => {
		if (event.target instanceof Element && event.target.closest('a')) {
			closeNavigation();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && navigationToggle.getAttribute('aria-expanded') === 'true') {
			closeNavigation();
			navigationToggle.focus();
		}
	});
}

const notificationDialog = document.querySelector('#notification-dialog');

if (notificationDialog instanceof HTMLDialogElement) {
	document.querySelectorAll('[data-notifications-open]').forEach((notificationButton) => {
		notificationButton.addEventListener('click', (event) => {
			event.preventDefault();
			notificationDialog.showModal();
		});
	});

	notificationDialog.querySelector('[data-notification-dialog-close]')?.addEventListener('click', () => {
		notificationDialog.close();
	});

	notificationDialog.addEventListener('click', (event) => {
		if (event.target === notificationDialog) {
			notificationDialog.close();
		}
	});
}

document.querySelectorAll('[data-question-builder]').forEach((builder) => {
	const questionList = builder.querySelector('[data-question-list]');
	const questionTemplate = builder.querySelector('[data-question-template]');
	const addQuestionButton = builder.querySelector('[data-add-question]');

	if (!questionList || !questionTemplate || !addQuestionButton) {
		return;
	}

	const updateQuestionNumbers = () => {
		const questionCards = questionList.querySelectorAll('[data-question-card]');

		questionCards.forEach((card, index) => {
			card.querySelector('[data-question-number]').textContent = String(index + 1);
		});

		addQuestionButton.disabled = questionCards.length >= Number(builder.dataset.maxQuestions);
	};

	builder.addEventListener('click', (event) => {
		const addButton = event.target.closest('[data-add-question]');

		if (addButton && !addButton.disabled) {
			const index = Number(builder.dataset.nextIndex);
			const questionMarkup = questionTemplate.innerHTML.replaceAll('__INDEX__', String(index));

			questionList.insertAdjacentHTML('beforeend', questionMarkup);
			builder.dataset.nextIndex = String(index + 1);
			updateQuestionNumbers();

			return;
		}

		const removeButton = event.target.closest('[data-remove-question]');

		if (removeButton) {
			removeButton.closest('[data-question-card]').remove();
			updateQuestionNumbers();
		}
	});

	builder.addEventListener('change', (event) => {
		if (!event.target.matches('[data-question-type]')) {
			return;
		}

		const optionsPanel = event.target.closest('[data-question-card]').querySelector('[data-question-options]');
		const choiceQuestion = ['radio', 'checkbox', 'dropdown'].includes(event.target.value);

		optionsPanel.classList.toggle('hidden', !choiceQuestion);
	});

	updateQuestionNumbers();
});
