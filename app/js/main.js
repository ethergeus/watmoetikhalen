$(document).ready(function() {
    if (window.location.href.indexOf('?forgot&') > -1) {
        $('.btn.pwd-reset-click').click();
    }
	$('.table-of-contents a').click(function() {
		if (!$(this).hasClass('active')) {
			$(this).parent().children('.active').removeClass('active');
			$(this).addClass('active');
		}
	});
	$('.btn-add').click(function(e) {
		e.preventDefault();
		$last = $(this).parents().eq(1).children('.cijfers').children('.cijfer').last();
		$copy = $last.clone();
		$copy.children('input').val('');
		$last.after($copy);
	});
	$('.btn-remove').click(function(e) {
		e.preventDefault();
		$all = $(this).parents().eq(1).children('.cijfers').children('.cijfer');
		if ($all.length > 1) {
			$all.last().remove();
		}
	});
	$('select:has(option[value="0"])').one('change', function() {
		$(this).children('option[value="0"]').remove();
	});
	$('select:has(option[value="1"])').change(function() {
		$toggle = $(this).parents().eq(1).find('.vakkeuze .btn, .cijfers, .opties');
		if ($(this).val() == 1) {
			$toggle.hide();
		} else {
			$toggle.show();
		}
	});
    $('.close-welcome-user').click(function() {
        document.cookie = 'welcome_user_closed = 1';
    });
    $('.close-account-info').click(function() {
        document.cookie = 'account_info_closed = 1';
    });
    $('.close-gratiscv-promo').click(function() {
        document.cookie = 'gratiscv_promo_closed = 1';
    });
});
