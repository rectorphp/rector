/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

class BlueScreen
{
	static init(ajax) {
		let blueScreen = document.getElementById('tracy-bs');
		let styles = [];

		for (let i = 0; i < document.styleSheets.length; i++) {
			let style = document.styleSheets[i];
			if (!style.ownerNode.classList.contains('tracy-debug')) {
				style.oldDisabled = style.disabled;
				style.disabled = true;
				styles.push(style);
			}
		}

		if (navigator.platform.indexOf('Mac') > -1) {
			blueScreen.classList.add('mac');
		}

		document.getElementById('tracy-bs-toggle').addEventListener('tracy-toggle', function() {
			let collapsed = this.classList.contains('tracy-collapsed');
			for (let i = 0; i < styles.length; i++) {
				styles[i].disabled = collapsed ? styles[i].oldDisabled : true;
			}
		});

		if (!ajax) {
			document.body.appendChild(blueScreen);
			let id = location.href + document.querySelector('.section--error').textContent;
			Tracy.Toggle.persist(blueScreen, sessionStorage.getItem('tracy-toggles-bskey') === id);
			sessionStorage.setItem('tracy-toggles-bskey', id);
		}

		if (inited) {
			return;
		}
		inited = true;

		// enables toggling via ESC
		document.addEventListener('keyup', (e) => {
			if (e.keyCode === 27 && !e.shiftKey && !e.altKey && !e.ctrlKey && !e.metaKey) { // ESC
				Tracy.Toggle.toggle(document.getElementById('tracy-bs-toggle'));
			}
		});

		blueScreen.addEventListener('tracy-toggle', (e) => {
			if (!e.target.matches('.tracy-dump *') && e.detail.originalEvent) {
				e.detail.relatedTarget.classList.toggle('panel-fadein', !e.detail.collapsed);
			}
		});

		Tracy.TableSort.init();
		Tracy.Tabs.init();
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
		window.scrollTo(0, 0);
	}
}

let inited;


let Tracy = window.Tracy = window.Tracy || {};
Tracy.BlueScreen = Tracy.BlueScreen || BlueScreen;
