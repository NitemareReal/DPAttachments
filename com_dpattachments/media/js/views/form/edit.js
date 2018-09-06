(function (document, Joomla) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		Joomla.submitbutton = function (task) {
			if (task == 'attachment.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.querySelector('.com-dpattachments-form-edit .dp-form'));
			}
		};

		[].slice.call(document.querySelectorAll('.com-dpattachments-form-edit__actions .dp-button')).forEach(function (button) {
			button.addEventListener('click', function (e) {
				Joomla.submitbutton('attachment.' + e.target.getAttribute('data-task'));
			});
		});
	});
})(document, Joomla);
