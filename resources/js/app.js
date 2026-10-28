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

const adminEditDialog = document.querySelector('#admin-edit-dialog');

if (adminEditDialog instanceof HTMLDialogElement) {
	const editForm = adminEditDialog.querySelector('[data-admin-edit-form]');
	const deleteForm = adminEditDialog.querySelector('[data-admin-delete-form]');
	const editButtons = [...document.querySelectorAll('[data-admin-edit-open]')];

	const openAdminEditDialog = (button, preserveValues = false) => {
		editForm.action = button.dataset.updateUrl;
		deleteForm.action = button.dataset.deleteUrl;
		editForm.elements.namedItem('admin_id').value = button.dataset.adminId;

		if (!preserveValues) {
			editForm.elements.namedItem('name').value = button.dataset.adminName;
			editForm.elements.namedItem('username').value = button.dataset.adminUsername;
			editForm.elements.namedItem('email').value = button.dataset.adminEmail;
			editForm.elements.namedItem('role_id').value = button.dataset.adminRole;
		}

		if (!adminEditDialog.open) {
			adminEditDialog.showModal();
		}
	};

	editButtons.forEach((button) => {
		button.addEventListener('click', () => openAdminEditDialog(button));
	});

	adminEditDialog.querySelectorAll('[data-admin-edit-close]').forEach((closeButton) => {
		closeButton.addEventListener('click', () => adminEditDialog.close());
	});

	adminEditDialog.addEventListener('click', (event) => {
		if (event.target === adminEditDialog) {
			adminEditDialog.close();
		}
	});

	const previousAdminButton = editButtons.find((button) => button.dataset.adminId === adminEditDialog.dataset.reopenFor);

	if (previousAdminButton) {
		openAdminEditDialog(previousAdminButton, true);
	}
}

const jobEditDialog = document.querySelector('#job-edit-dialog');

if (jobEditDialog instanceof HTMLDialogElement) {
	const editForm = jobEditDialog.querySelector('[data-job-edit-form]');
	const deleteForm = jobEditDialog.querySelector('[data-job-delete-form]');
	const editButtons = [...document.querySelectorAll('[data-job-edit-open]')];

	const openJobEditDialog = (button, preserveValues = false) => {
		editForm.action = button.dataset.updateUrl;
		deleteForm.action = button.dataset.deleteUrl;
		editForm.elements.namedItem('job_id').value = button.dataset.jobId;

		if (!preserveValues) {
			editForm.elements.namedItem('title').value = button.dataset.jobTitle;
			editForm.elements.namedItem('company').value = button.dataset.jobCompany;
			editForm.elements.namedItem('location').value = button.dataset.jobLocation;
			editForm.elements.namedItem('employment_type').value = button.dataset.jobEmploymentType;
			editForm.elements.namedItem('description').value = button.dataset.jobDescription;
			editForm.elements.namedItem('requirements').value = button.dataset.jobRequirements;
			editForm.elements.namedItem('application_deadline').value = button.dataset.jobDeadline;
			editForm.elements.namedItem('status').value = button.dataset.jobStatus;
		}

		if (!jobEditDialog.open) {
			jobEditDialog.showModal();
		}
	};

	editButtons.forEach((button) => {
		button.addEventListener('click', () => openJobEditDialog(button));
	});

	jobEditDialog.querySelectorAll('[data-job-edit-close]').forEach((closeButton) => {
		closeButton.addEventListener('click', () => jobEditDialog.close());
	});

	jobEditDialog.addEventListener('click', (event) => {
		if (event.target === jobEditDialog) {
			jobEditDialog.close();
		}
	});

	const previousJobButton = editButtons.find((button) => button.dataset.jobId === jobEditDialog.dataset.reopenFor);

	if (previousJobButton) {
		openJobEditDialog(previousJobButton, true);
	}
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
