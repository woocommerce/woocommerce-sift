jQuery(document).ready(() => {
	/* This makes sure all input with the same event are in sync */
	jQuery('input[type=checkbox][name^=wc_sift_for_woocommerce_enable_]').on('change', function() {
		const name = jQuery(this).attr('name');
		jQuery('input[type=checkbox][name=' + name +']').not(this).prop('checked', jQuery(this).prop('checked'));
	});
});


