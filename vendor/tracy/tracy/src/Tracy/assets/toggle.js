/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

const MOVE_THRESHOLD = 100;

// enables <a class="tracy-toggle" href="#"> or <span data-tracy-ref="#"> toggling
class Toggle
{
	static init() {
		let start;
		document.documentElement.addEventListener('mousedown', (e) => {
			start = [e.clientX, e.clientY];
		});

		document.documentElement.addEventListener('click', (e) => {
			let el;
			if (
				!e.shiftKey && !e.ctrlKey && !e.metaKey
				&& (el = e.target.closest('.tracy-toggle'))
				&& Math.pow(start[0] - e.clientX, 2) + Math.pow(start[1] - e.clientY, 2) < MOVE_THRESHOLD
			) {
				Toggle.toggle(el, undefined, e);
				e.preventDefault();
				e.stopImmediatePropagation();
			}
		});
		Toggle.init = function() {};
	}


	// changes element visibility
	static toggle(el, expand, e) {
		let collapsed = el.classList.contains('tracy-collapsed'),
			ref = el.getAttribute('data-tracy-ref') || el.getAttribute('href', 2),
			dest = el;

		if (typeof expand === 'undefined') {
			expand = collapsed;
		}

		el.dispatchEvent(new CustomEvent('tracy-beforetoggle', {
			bubbles: true,
			detail: {collapsed: !expand, originalEvent: e}
		}));

		if (!ref || ref === '#') {
			ref = '+';
		} else if (ref.substr(0, 1) === '#') {
			dest = document;
		}
		ref = ref.match(/(\^\s*([^+\s]*)\s*)?(\+\s*(\S*)\s*)?(.*)/);
		dest = ref[1] ? dest.parentNode : dest;
		dest = ref[2] ? dest.closest(ref[2]) : dest;
		dest = ref[3] ? Toggle.nextElement(dest.nextElementSibling, ref[4]) : dest;
		dest = ref[5] ? dest.querySelector(ref[5]) : dest;

		el.classList.toggle('tracy-collapsed', !expand);
		dest.classList.toggle('tracy-collapsed', !expand);

		el.dispatchEvent(new CustomEvent('tracy-toggle', {
			bubbles: true,
			detail: {relatedTarget: dest, collapsed: !expand, originalEvent: e}
		}));
	}


	// save & restore toggles
	static persist(baseEl, restore) {
		let saved = [];
		baseEl.addEventListener('tracy-toggle', (e) => {
			if (saved.indexOf(e.target) < 0) {
				saved.push(e.target);
			}
		});

		let toggles = JSON.parse(sessionStorage.getItem('tracy-toggles-' + baseEl.id));
		if (toggles && restore !== false) {
			toggles.forEach((item) => {
				let el = baseEl;
				for (let i in item.path) {
					if (!(el = el.children[item.path[i]])) {
						return;
					}
				}
				if (el.textContent === item.text) {
					Toggle.toggle(el, item.expand);
				}
			});
		}

		window.addEventListener('unload', () => {
			toggles = saved.map((el) => {
				let item = {path: [], text: el.textContent, expand: !el.classList.contains('tracy-collapsed')};
				do {
					item.path.unshift(Array.from(el.parentNode.children).indexOf(el));
					el = el.parentNode;
				} while (el && el !== baseEl);
				return item;
			});
			sessionStorage.setItem('tracy-toggles-' + baseEl.id, JSON.stringify(toggles));
		});
	}


	// finds next matching element
	static nextElement(el, selector) {
		while (el && selector && !el.matches(selector)) {
			el = el.nextElementSibling;
		}
		return el;
	}
}


let Tracy = window.Tracy = window.Tracy || {};
Tracy.Toggle = Tracy.Toggle || Toggle;
