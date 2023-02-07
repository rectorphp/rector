/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

class BlueScreen
{
	static init(ajax) {
		BlueScreen.globalInit();

		let blueScreen = document.getElementById('tracy-bs');

		document.documentElement.classList.add('tracy-bs-visible');
		if (navigator.platform.indexOf('Mac') > -1) {
			blueScreen.classList.add('tracy-mac');
		}

		blueScreen.addEventListener('tracy-toggle', (e) => {
			if (e.target.matches('#tracy-bs-toggle')) { // blue screen toggle
				document.documentElement.classList.toggle('tracy-bs-visible', !e.detail.collapsed);

			} else if (!e.target.matches('.tracy-dump *') && e.detail.originalEvent) { // panel toggle
				e.detail.relatedTarget.classList.toggle('tracy-panel-fadein', !e.detail.collapsed);
			}
		});

		if (!ajax) {
			document.body.appendChild(blueScreen);
			let id = location.href + document.querySelector('.tracy-section--error').textContent;
			Tracy.Toggle.persist(blueScreen, sessionStorage.getItem('tracy-toggles-bskey') === id);
			sessionStorage.setItem('tracy-toggles-bskey', id);
		}

		(new ResizeObserver(stickyFooter)).observe(blueScreen);

		if (document.documentElement.classList.contains('tracy-bs-visible')) {
			window.scrollTo(0, 0);
		}
	}


	static globalInit() {
		// enables toggling via ESC
		document.addEventListener('keyup', (e) => {
			if (e.keyCode === 27 && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) { // ESC
				Tracy.Toggle.toggle(document.getElementById('tracy-bs-toggle'));
			}
		});

		Tracy.TableSort.init();
		Tracy.Tabs.init();

		window.addEventListener('scroll', stickyFooter);

		BlueScreen.globalInit = function() {};
	}


	static loadAjax(content) {
		let ajaxBs = document.getElementById('tracy-bs');
		if (ajaxBs) {
			ajaxBs.remove();
		}
		document.body.insertAdjacentHTML('beforeend', content);
		ajaxBs = document.getElementById('tracy-bs');
		Tracy.Dumper.init(ajaxBs);
		BlueScreen.init(true);
	}
}

function stickyFooter() {
	let footer = document.querySelector('#tracy-bs footer');
	footer.classList.toggle('tracy-footer--sticky', false); // to measure footer.offsetTop
	footer.classList.toggle('tracy-footer--sticky', footer.offsetHeight + footer.offsetTop - window.innerHeight - document.documentElement.scrollTop < 0);
}

let Tracy = window.Tracy = window.Tracy || {};
Tracy.BlueScreen = Tracy.BlueScreen || BlueScreen;
