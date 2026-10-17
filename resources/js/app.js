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
