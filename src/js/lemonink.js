jQuery(document).ready(function($) {

	function show_and_hide_panels() {
		var is_lemoninkable = !!$('input#_li_lemoninkable:checked').size();

		$('.show_if_lemoninkable').toggle(is_lemoninkable);
		$('.show_if_downloadable .downloadable_files').toggle(!is_lemoninkable);
	}

	show_and_hide_panels();
	$('input#_li_lemoninkable').on('change', show_and_hide_panels);
});
