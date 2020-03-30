jQuery(document).ready(function($) {

	function show_and_hide_panels() {
		var $input = $(this);
		var $parent = $input.closest('#general_product_data, .woocommerce_variable_attributes');
		var is_lemoninkable = $input.is(':checked');
		$parent.find('.show_if_lemoninkable').toggle(is_lemoninkable);
		$parent.find('.show_if_downloadable .downloadable_files, .show_if_variation_downloadable .downloadable_files').toggle(!is_lemoninkable);
	}

	$('input[name*=_li_lemoninkable]').each(show_and_hide_panels);
	$("body").on('change', 'input[name*=_li_lemoninkable]', show_and_hide_panels);
});
