/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

const
	COLLAPSE_COUNT = 7,
	COLLAPSE_COUNT_TOP = 14,
	TYPE_ARRAY = 'a',
	TYPE_OBJECT = 'o',
	TYPE_RESOURCE = 'r',
	PROP_VIRTUAL = 4,
	PROP_PRIVATE = 2;

const
	HINT_CTRL = 'Ctrl-Click to open in editor',
	HINT_ALT = 'Alt-Click to expand/collapse all child nodes';

class Dumper
{
	static init(context) {
		(context || document).querySelectorAll('[itemprop=tracy-snapshot], [data-tracy-snapshot]').forEach((el) => {
			let preList, snapshot = JSON.parse(el.getAttribute('data-tracy-snapshot'));

			if (el.tagName === 'META') { // <meta itemprop=tracy-snapshot>
				snapshot = JSON.parse(el.getAttribute('content'));
				preList = el.parentElement.querySelectorAll('[data-tracy-dump]');
			} else if (el.matches('[data-tracy-dump]')) { // <pre data-tracy-snapshot data-tracy-dump>
				preList = [el];
				el.removeAttribute('data-tracy-snapshot');
			} else { // <span data-tracy-dump>
				el.querySelectorAll('[data-tracy-dump]').forEach((el) => {
					if (el.nextSibling) {
						el.parentNode.removeChild(el.nextSibling); // remove \n after toggler
					}
					el.parentNode.replaceChild( // replace toggler
						build(JSON.parse(el.getAttribute('data-tracy-dump')), snapshot, el.classList.contains('tracy-collapsed')),
						el
					);
				});
				return;
			}

			preList.forEach((el) => { // <pre>
				let built = build(JSON.parse(el.getAttribute('data-tracy-dump')), snapshot, el.classList.contains('tracy-collapsed'));
				el.appendChild(built);
				el.classList.remove('tracy-collapsed');
				el.removeAttribute('data-tracy-dump');
			});
		});

		if (Dumper.inited) {
			return;
		}
		Dumper.inited = true;

		// enables <span data-tracy-href=""> & ctrl key
		document.documentElement.addEventListener('click', (e) => {
			let el;
			if (e.ctrlKey && (el = e.target.closest('[data-tracy-href]'))) {
				location.href = el.getAttribute('data-tracy-href');
				return false;
			}
		});

		document.documentElement.addEventListener('tracy-toggle', (e) => {
			if (!e.target.matches('.tracy-dump *')) {
				return;
			}

			let cont = e.detail.relatedTarget;
			let origE = e.detail.originalEvent;

			if (origE && origE.usedIds) { // triggered by expandChild()
				toggleChildren(cont, origE.usedIds);
				return;

			} else if (origE && origE.altKey && cont.querySelector('.tracy-toggle')) { // triggered by alt key
				if (e.detail.collapsed) { // reopen
					e.target.classList.toggle('tracy-collapsed', false);
					cont.classList.toggle('tracy-collapsed', false);
					e.detail.collapsed = false;
				}

				let expand = e.target.tracyAltExpand = !e.target.tracyAltExpand;
				toggleChildren(cont, expand ? {} : false);
			}

			cont.classList.toggle('tracy-dump-flash', !e.detail.collapsed);
		});

		document.documentElement.addEventListener('animationend', (e) => {
			if (e.animationName === 'tracy-dump-flash') {
				e.target.classList.toggle('tracy-dump-flash', false);
			}
		});

		document.addEventListener('mouseover', (e) => {
			if (!e.target.matches('.tracy-dump *')) {
				return;
			}

			let el;

			if (e.target.matches('.tracy-dump-hash') && (el = e.target.closest('.tracy-dump'))) {
				el.querySelectorAll('.tracy-dump-hash').forEach((el) => {
					if (el.textContent === e.target.textContent) {
						el.classList.add('tracy-dump-highlight');
					}
				});
				return;
			}

			if ((el = e.target.closest('.tracy-toggle')) && !el.title) {
				el.title = HINT_ALT;
			}
		});

		document.addEventListener('mouseout', (e) => {
			if (e.target.matches('.tracy-dump-hash')) {
				document.querySelectorAll('.tracy-dump-hash.tracy-dump-highlight').forEach((el) => {
					el.classList.remove('tracy-dump-highlight');
				});
			}
		});

		Tracy.Toggle.init();
	}
}


function build(data, repository, collapsed, parentIds, keyType) {
	let id, type = data === null ? 'null' : typeof data,
		collapseCount = collapsed === null ? COLLAPSE_COUNT : COLLAPSE_COUNT_TOP;

	if (type === 'null' || type === 'number' || type === 'boolean') {
		return createEl(null, null, [
			createEl(
				'span',
				{'class': 'tracy-dump-' + type.replace('ean', '')},
				[data + '']
			)
		]);

	} else if (type === 'string') {
		data = {
			string: data.replace(/&/g, '&amp;').replace(/</g, '&lt;'),
			length: [...data].length
		};

	} else if (Array.isArray(data)) {
		data = {array: null, items: data};

	} else if (data.ref) {
		id = data.ref;
		data = repository[id];
		if (!data) {
			throw new UnknownEntityException;
		}
	}


	if (data.string !== undefined || data.bin !== undefined) {
		let s = data.string === undefined ? data.bin : data.string;
		if (keyType === TYPE_ARRAY) {
			return createEl(null, null, [
				createEl(
					'span',
					{'class': 'tracy-dump-string'},
					{html: '<span class="tracy-dump-lq">\'</span>' + s + '<span>\'</span>'}
				),
			]);

		} else if (keyType !== undefined) {
			if (type !== 'string') {
				s = '<span class="tracy-dump-lq">\'</span>' + s + '<span>\'</span>';
			}

			const classes = [
				'tracy-dump-public',
				'tracy-dump-protected',
				'tracy-dump-private',
				'tracy-dump-dynamic',
				'tracy-dump-virtual',
			];
			return createEl(null, null, [
				createEl(
					'span',
					{
						'class': classes[typeof keyType === 'string' ? PROP_PRIVATE : keyType],
						'title': typeof keyType === 'string' ? 'declared in ' + keyType : null,
					},
					{html: s}
				),
			]);
		}

		let count = (s.match(/\n/g) || []).length;
		if (count) {
			let collapsed = count >= COLLAPSE_COUNT;
			return createEl(null, null, [
				createEl('span', {'class': collapsed ? 'tracy-toggle tracy-collapsed' : 'tracy-toggle'}, ['string']),
				'\n',
				createEl(
					'div',
					{
						'class': 'tracy-dump-string' + (collapsed ? ' tracy-collapsed' : ''),
						'title': data.length + (data.bin ? ' bytes' : ' characters'),
					},
					{html: '<span class="tracy-dump-lq">\'</span>' + s + '<span>\'</span>'}
				),
			]);
		}

		return createEl(null, null, [
			createEl(
				'span',
				{
					'class': 'tracy-dump-string',
					'title': data.length + (data.bin ? ' bytes' : ' characters'),
				},
				{html: '<span>\'</span>' + s + '<span>\'</span>'}
			),
		]);

	} else if (data.number) {
		return createEl(null, null, [
			createEl('span', {'class': 'tracy-dump-number'}, [data.number])
		]);

	} else if (data.text !== undefined) {
		return createEl(null, null, [
			createEl('span', {class: 'tracy-dump-virtual'}, [data.text])
		]);

	} else { // object || resource || array
		parentIds = parentIds ? parentIds.slice() : [];
		let recursive = id && parentIds.indexOf(id) > -1;
		parentIds.push(id);

		return buildStruct(
			data.array !== undefined
				? [
					createEl('span', {'class': 'tracy-dump-array'}, ['array']),
					' (' + (data.length || data.items.length) + ')'
				]
				: [
					createEl('span', {
						'class': data.object ? 'tracy-dump-object' : 'tracy-dump-resource',
						title: data.editor ? 'Declared in file ' + data.editor.file + ' on line ' + data.editor.line + (data.editor.url ? '\n' + HINT_CTRL : '') + '\n' + HINT_ALT : null,
						'data-tracy-href': data.editor ? data.editor.url : null
					}, [data.object || data.resource]),
					...(id ? [' ', createEl('span', {'class': 'tracy-dump-hash'}, [data.resource ? '@' + id.substr(1) : '#' + id])] : [])
				],
			recursive ? ' RECURSION' : ' …',
			recursive ? null : data.items,
			collapsed === true || data.collapsed || (data.items && data.items.length >= collapseCount),
			data.items && data.length > data.items.length,
			data.object ? TYPE_OBJECT : data.resource ? TYPE_RESOURCE : TYPE_ARRAY,
			repository,
			parentIds
		);
	}
}


function buildStruct(span, ellipsis, items, collapsed, cut, type, repository, parentIds) {
	let res, toggle, div, handler;

	if (!items || !items.length) {
		span.push(!items || items.length ? ellipsis : '');
		return createEl(null, null, span);
	}

	res = createEl(null, null, [
		toggle = createEl('span', {'class': collapsed ? 'tracy-toggle tracy-collapsed' : 'tracy-toggle'}, span),
		'\n',
		div = createEl('div', {'class': collapsed ? 'tracy-collapsed' : null})
	]);

	if (collapsed) {
		toggle.addEventListener('tracy-toggle', handler = function() {
			toggle.removeEventListener('tracy-toggle', handler);
			createItems(div, items, type, repository, parentIds, null);
			if (cut) {
				createEl(div, null, ['…\n']);
			}
		});
	} else {
		createItems(div, items, type, repository, parentIds, true);
		if (cut) {
			createEl(div, null, ['…\n']);
		}
	}
	return res;
}


function createEl(el, attrs, content) {
	if (!(el instanceof Node)) {
		el = el ? document.createElement(el) : document.createDocumentFragment();
	}
	for (let id in attrs || {}) {
		if (attrs[id] !== null) {
			el.setAttribute(id, attrs[id]);
		}
	}

	if (content && content.html !== undefined) {
		el.innerHTML = content.html;
		return el;
	}
	content = content || [];
	for (let id = 0; id < content.length; id++) {
		let child = content[id];
		if (child !== null) {
			el.appendChild(child instanceof Node ? child : document.createTextNode(child));
		}
	}
	return el;
}


function createItems(el, items, type, repository, parentIds, collapsed) {
	let key, val, vis, ref, i, tmp;

	for (i = 0; i < items.length; i++) {
		if (type === TYPE_ARRAY) {
			[key, val, ref] = items[i];
		} else {
			[key, val, vis = PROP_VIRTUAL, ref] = items[i];
		}

		createEl(el, null, [
			build(key, null, null, null, type === TYPE_ARRAY ? TYPE_ARRAY : vis),
			type === TYPE_ARRAY ? ' => ' : ': ',
			...(ref ? [createEl('span', {'class': 'tracy-dump-hash'}, ['&' + ref]), ' '] : []),
			tmp = build(val, repository, collapsed, parentIds),
			tmp.lastElementChild.tagName === 'DIV' ? '' : '\n',
		]);
	}
}


function toggleChildren(cont, usedIds) {
	let hashEl, id;

	cont.querySelectorAll(':scope > .tracy-toggle').forEach((el) => {
		hashEl = (el.querySelector('.tracy-dump-hash') || el.previousElementSibling);
		id = hashEl && hashEl.matches('.tracy-dump-hash') ? hashEl.textContent : null;

		if (!usedIds || (id && usedIds[id])) {
			Tracy.Toggle.toggle(el, false);
		} else {
			usedIds[id] = true;
			Tracy.Toggle.toggle(el, true, {usedIds: usedIds});
		}
	});
}


function UnknownEntityException() {}


let Tracy = window.Tracy = window.Tracy || {};
Tracy.Dumper = Tracy.Dumper || Dumper;

function init() {
	Tracy.Dumper.init();
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', init);
} else {
	init();
}
