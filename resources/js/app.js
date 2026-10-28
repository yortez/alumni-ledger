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

const announcementEditDialog = document.querySelector('#announcement-edit-dialog');

if (announcementEditDialog instanceof HTMLDialogElement) {
	const editForm = announcementEditDialog.querySelector('[data-announcement-edit-form]');
	const deleteForm = announcementEditDialog.querySelector('[data-announcement-delete-form]');
	const editButtons = [...document.querySelectorAll('[data-announcement-edit-open]')];

	const openAnnouncementEditDialog = (button, preserveValues = false) => {
		editForm.action = button.dataset.updateUrl;
		deleteForm.action = button.dataset.deleteUrl;
		editForm.elements.namedItem('announcement_id').value = button.dataset.announcementId;

		if (!preserveValues) {
			editForm.elements.namedItem('title').value = button.dataset.announcementTitle;
			editForm.elements.namedItem('body').value = button.dataset.announcementBody;
			editForm.elements.namedItem('category').value = button.dataset.announcementCategory;
			editForm.elements.namedItem('status').value = button.dataset.announcementStatus;
			editForm.elements.namedItem('publish_date').value = button.dataset.announcementPublishDate;
			editForm.elements.namedItem('expiry_date').value = button.dataset.announcementExpiryDate;
			editForm.querySelector('input[type="checkbox"][name="is_pinned"]').checked = button.dataset.announcementPinned === '1';
		}

		if (!announcementEditDialog.open) {
			announcementEditDialog.showModal();
		}
	};

	editButtons.forEach((button) => {
		button.addEventListener('click', () => openAnnouncementEditDialog(button));
	});

	announcementEditDialog.querySelectorAll('[data-announcement-edit-close]').forEach((closeButton) => {
		closeButton.addEventListener('click', () => announcementEditDialog.close());
	});

	announcementEditDialog.addEventListener('click', (event) => {
		if (event.target === announcementEditDialog) {
			announcementEditDialog.close();
		}
	});

	const previousAnnouncementButton = editButtons.find((button) => button.dataset.announcementId === announcementEditDialog.dataset.reopenFor);

	if (previousAnnouncementButton) {
		openAnnouncementEditDialog(previousAnnouncementButton, true);
	}
}

const surveyEditDialog = document.querySelector('#survey-edit-dialog');

if (surveyEditDialog instanceof HTMLDialogElement) {
	const editForm = surveyEditDialog.querySelector('[data-survey-edit-form]');
	const deleteForm = surveyEditDialog.querySelector('[data-survey-delete-form]');
	const questionBuilder = editForm.querySelector('[data-question-builder]');
	const questionList = questionBuilder.querySelector('[data-question-list]');
	const questionTemplate = questionBuilder.querySelector('[data-question-template]');
	const addQuestionButton = questionBuilder.querySelector('[data-add-question]');
	const editButtons = [...document.querySelectorAll('[data-survey-edit-open]')];

	const fillSurveyQuestions = (questions) => {
		questionList.replaceChildren();

		questions.forEach((question, index) => {
			const markup = questionTemplate.innerHTML.replaceAll('__INDEX__', String(index));
			questionList.insertAdjacentHTML('beforeend', markup);

			const card = questionList.lastElementChild;
			const setFieldValue = (name, value) => {
				const field = [...card.querySelectorAll('[data-question-field]')].find((candidate) => candidate.name === name);

				if (field) {
					field.value = value;
				}
			};

			setFieldValue(`questions[${index}][prompt]`, question.prompt ?? '');
			setFieldValue(`questions[${index}][section_label]`, question.section_label ?? '');
			setFieldValue(`questions[${index}][type]`, question.type ?? 'long_text');
			setFieldValue(`questions[${index}][options_text]`, (question.options ?? []).join('\n'));

			const requiredCheckbox = [...card.querySelectorAll('input[type="checkbox"][data-question-field]')]
				.find((field) => field.name === `questions[${index}][required]`);

			if (requiredCheckbox) {
				requiredCheckbox.checked = question.required !== false;
			}

			const choiceQuestion = ['radio', 'checkbox', 'dropdown'].includes(question.type);
			card.querySelector('[data-question-options]').classList.toggle('hidden', !choiceQuestion);
			card.querySelector('[data-question-number]').textContent = String(index + 1);
		});

		questionBuilder.dataset.nextIndex = String(questions.length);
		addQuestionButton.disabled = questions.length >= Number(questionBuilder.dataset.maxQuestions);
	};

	const openSurveyEditDialog = (button, preserveValues = false) => {
		editForm.action = button.dataset.updateUrl;
		deleteForm.action = button.dataset.deleteUrl;
		editForm.elements.namedItem('survey_id').value = button.dataset.surveyId;

		if (!preserveValues) {
			const surveyDataTemplate = [...document.querySelectorAll('[data-survey-edit-data]')]
				.find((template) => template.dataset.surveyEditData === button.dataset.surveyId);
			const survey = JSON.parse(surveyDataTemplate.content.textContent);

			editForm.elements.namedItem('title').value = survey.title;
			editForm.elements.namedItem('description').value = survey.description ?? '';
			editForm.elements.namedItem('closes_at').value = survey.closes_at ?? '';
			editForm.elements.namedItem('status').value = survey.status;

			const targetPrograms = new Set(survey.target_programs.map(String));
			editForm.querySelectorAll('[name="target_programs[]"]').forEach((checkbox) => {
				checkbox.checked = targetPrograms.has(checkbox.value);
			});

			const targetYears = new Set(survey.target_graduation_years.map(String));
			editForm.querySelectorAll('[name="target_graduation_years[]"]').forEach((checkbox) => {
				checkbox.checked = targetYears.has(checkbox.value);
			});

			fillSurveyQuestions(survey.questions);
		}

		if (!surveyEditDialog.open) {
			surveyEditDialog.showModal();
		}
	};

	editButtons.forEach((button) => {
		button.addEventListener('click', () => openSurveyEditDialog(button));
	});

	surveyEditDialog.querySelectorAll('[data-survey-edit-close]').forEach((closeButton) => {
		closeButton.addEventListener('click', () => surveyEditDialog.close());
	});

	surveyEditDialog.addEventListener('click', (event) => {
		if (event.target === surveyEditDialog) {
			surveyEditDialog.close();
		}
	});

	const previousSurveyButton = editButtons.find((button) => button.dataset.surveyId === surveyEditDialog.dataset.reopenFor);

	if (previousSurveyButton) {
		openSurveyEditDialog(previousSurveyButton, true);
	}
}

const roleEditDialog = document.querySelector('#role-edit-dialog');

if (roleEditDialog instanceof HTMLDialogElement) {
	const editForm = roleEditDialog.querySelector('[data-role-edit-form]');
	const customRoleFields = roleEditDialog.querySelector('[data-role-custom-fields]');
	const systemRoleNote = roleEditDialog.querySelector('[data-role-system-note]');
	const deleteForm = roleEditDialog.querySelector('[data-role-delete-form]');
	const editButtons = [...document.querySelectorAll('[data-role-edit-open]')];

	const openRoleEditDialog = (button, preserveValues = false) => {
		const isSystemRole = button.dataset.roleSystem === 'true';

		editForm.action = button.dataset.updateUrl;
		deleteForm.action = button.dataset.deleteUrl;
		editForm.elements.namedItem('role_id').value = button.dataset.roleId;
		customRoleFields.hidden = isSystemRole;
		systemRoleNote.hidden = !isSystemRole;
		deleteForm.hidden = isSystemRole;
		customRoleFields.querySelectorAll('input, select').forEach((field) => {
			field.disabled = isSystemRole;
		});

		if (!preserveValues) {
			editForm.elements.namedItem('name').value = button.dataset.roleName;
			editForm.elements.namedItem('description').value = button.dataset.roleDescription;
			customRoleFields.querySelector('[name="type"]').value = button.dataset.roleType;

			const permissions = new Set(button.dataset.rolePermissions.split(',').filter(Boolean));
			customRoleFields.querySelectorAll('[name="permissions[]"]').forEach((checkbox) => {
				checkbox.checked = permissions.has(checkbox.value);
			});
		}

		if (!roleEditDialog.open) {
			roleEditDialog.showModal();
		}
	};

	editButtons.forEach((button) => {
		button.addEventListener('click', () => openRoleEditDialog(button));
	});

	roleEditDialog.querySelectorAll('[data-role-edit-close]').forEach((closeButton) => {
		closeButton.addEventListener('click', () => roleEditDialog.close());
	});

	roleEditDialog.addEventListener('click', (event) => {
		if (event.target === roleEditDialog) {
			roleEditDialog.close();
		}
	});

	const previousRoleButton = editButtons.find((button) => button.dataset.roleId === roleEditDialog.dataset.reopenFor);

	if (previousRoleButton) {
		openRoleEditDialog(previousRoleButton, true);
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
