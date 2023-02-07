/**
 * This file is part of the Tracy (https://tracy.nette.org)
 */

// enables <table class="tracy-sortable">
class TableSort
{
	static init() {
		document.documentElement.addEventListener('click', (e) => {
			if (e.target.matches('.tracy-sortable > :first-child > tr:first-child *')) {
				TableSort.sort(e.target.closest('td,th'));
			}
		});

		TableSort.init = function() {};
	}

	static sort(tcell) {
		let tbody = tcell.closest('table').tBodies[0];
		let preserveFirst = !tcell.closest('thead') && !tcell.parentNode.querySelectorAll('td').length;
		let asc = !(tbody.tracyAsc === tcell.cellIndex);
		tbody.tracyAsc = asc ? tcell.cellIndex : null;
		let getText = (cell) => { return cell ? (cell.getAttribute('data-order') || cell.innerText) : ''; };

		Array.from(tbody.children)
			.slice(preserveFirst ? 1 : 0)
			.sort((a, b) => {
				return function(v1, v2) {
					return v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2)
						? v1 - v2
						: v1.toString().localeCompare(v2, undefined, {numeric: true, sensitivity: 'base'});
				}(getText((asc ? a : b).children[tcell.cellIndex]), getText((asc ? b : a).children[tcell.cellIndex]));
			})
			.forEach((tr) => { tbody.appendChild(tr); });
	}
}


let Tracy = window.Tracy = window.Tracy || {};
Tracy.TableSort = Tracy.TableSort || TableSort;
