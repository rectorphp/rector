/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

// enables .tracy-tabs, .tracy-tab-label, .tracy-tab-panel, .tracy-active
class Tabs
{
	static init() {
		document.documentElement.addEventListener('click', (e) => {
			let label, context;
			if (
				!e.shiftKey && !e.ctrlKey && !e.metaKey
				&& (label = e.target.closest('.tracy-tab-label'))
				&& (context = e.target.closest('.tracy-tabs'))
			) {
				Tabs.toggle(context, label);
				e.preventDefault();
				e.stopImmediatePropagation();
			}
		});

		Tabs.init = function() {};
	}

	static toggle(context, label) {
		let labels = context.querySelector('.tracy-tab-label').parentNode.querySelectorAll('.tracy-tab-label'),
			panels = context.querySelector('.tracy-tab-panel').parentNode.querySelectorAll(':scope > .tracy-tab-panel');

		for (let i = 0; i < labels.length; i++) {
			labels[i].classList.toggle('tracy-active', labels[i] === label);
		}

		for (let i = 0; i < panels.length; i++) {
			panels[i].classList.toggle('tracy-active', labels[i] === label);
		}
	}
}


let Tracy = window.Tracy = window.Tracy || {};
Tracy.Tabs = Tracy.Tabs || Tabs;
