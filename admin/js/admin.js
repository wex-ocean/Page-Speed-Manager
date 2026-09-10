(function () {
	'use strict';

	var config = window.PSMAdmin || {};

	function showMessage(form, message, isError) {
		var target = form.querySelector('.psm-form-message');
		if (!target) {
			return;
		}
		target.textContent = message;
		target.classList.toggle('is-error', Boolean(isError));
	}

	function postForm(form, data) {
		data.append('action', form.dataset.action);
		data.append('nonce', config.nonce);

		fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
			.then(function (response) { return response.json(); })
			.then(function (payload) {
				if (!payload.success) {
					throw new Error(payload.data && payload.data.message ? payload.data.message : 'Save failed.');
				}
				showMessage(form, 'Saved.', false);
			})
			.catch(function (error) { showMessage(form, error.message, true); });
	}

	document.querySelectorAll('.psm-ajax-form').forEach(function (form) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			postForm(form, new FormData(form));
		});
	});

	document.querySelectorAll('.psm-rule-toggle').forEach(function (toggle) {
		toggle.addEventListener('change', function () {
			var data = new FormData();
			data.append('post_id', toggle.dataset.postId);
			data.append('handle', toggle.dataset.handle);
			data.append('enabled', toggle.checked ? '1' : '0');
			data.append('action', toggle.dataset.action);
			data.append('nonce', config.nonce);
			fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
				.then(function (response) { return response.json(); })
				.then(function (payload) {
					if (!payload.success) {
						toggle.checked = !toggle.checked;
						window.alert(payload.data && payload.data.message ? payload.data.message : 'Save failed.');
					}
				})
				.catch(function () { toggle.checked = !toggle.checked; });
		});
	});

	document.querySelectorAll('[data-filter-target]').forEach(function (input) {
		input.addEventListener('input', function () {
			var needle = input.value.toLowerCase().trim();
			document.querySelectorAll(input.dataset.filterTarget).forEach(function (row) {
				row.hidden = needle !== '' && row.dataset.filterText.indexOf(needle) === -1;
			});
		});
	});
}());