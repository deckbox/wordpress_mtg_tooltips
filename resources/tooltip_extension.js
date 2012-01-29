jQuery(document).ready(function($) {
    $('.mtg_deck_embedded a').mouseover(function(event) {
	$(this).parents('.mtg_deck').find('img.on_page').attr('src',$(this).attr('href') + '/tooltip');
        event.stopPropagation();
    });
});
