jQuery(document).ready(function($) {
    $('.mtg_deck_embedded a').mouseover(function(event) {
	$(this).parents('.mtg_deck').find('img.on_page').attr('src',$(this).attr('href') + '/tooltip');
        event.stopPropagation();
    });
    // Disable the mouseover images when the links contain the embedded card image already
    $('a.deckbox_link:has(img)').mouseover(function(event) {
        event.stopPropagation();
    });
});
