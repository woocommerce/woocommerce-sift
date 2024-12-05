var jQuery = window.jQuery;
if ( jQuery ) {
	jQuery(document).ready(() => {
		// Make sure all disabled checked are unchecked
		jQuery('input[type=checkbox][name^=wc_sift_for_woocommerce_enable_]:disabled').prop('checked', false);

		jQuery('input[type=checkbox][name^=wc_sift_for_woocommerce_enable_]').on('change', function() {
			const name = jQuery(this).attr('name');
			jQuery('input[type=checkbox][name=' + name +']').not(this).prop('checked', jQuery(this).prop('checked'));
		});
	});
}

