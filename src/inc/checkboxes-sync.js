jQuery(document).ready(() => {

	var preventReccursion = false;
	jQuery('input[type=checkbox][name^=wc_sift_for_woocommerce_enable_]').on('change', function() {
		if(preventReccursion) {
			return;
		}

		var name = jQuery(this).attr('name');
		preventReccursion = true;
		jQuery('input[type=checkbox][name=' + name +']').not(this).prop('checked', jQuery(this).prop('checked'));
		preventReccursion = false;
	});
});


