=== Magic the Gathering Card Tooltips ===
Contributors: grimdonkey
Tags: magic the gathering, deckbox, MtG, tcg, ccg, magic, cards, tooltips
Requires at least: 2.8.6
Tested up to: 6.9
Stable tag: 3.8.0

Easily transform Magic the Gathering card names into links that show the card image in a tooltip when hovering over them. You can also quickly create deck listings.

== Description ==

The plugin adds shortcodes that transform mtg card names and decks to provide a mouseover image. For cards use the [mtg_card][/mtg_card] shortcode (or the shortened version [c][/c]). When viewing the post, the card name will show up as a link to the card's page on https://deckbox.org . Hovering over the link will show the card's image in a tooltip.

A similar tag can be used to quickly create deck listings: [mtg_deck][/mtg_deck] (or the shortened version [d][/d]). A deck listing should contain a list of cards and categories. All cards have a number before their name. All other lines are interpreted as category names. Do *not* include card numbers on the category name lines, they will automatically be computed and displayed by the plugin. A short example follows.

The default style for displaying decks is a card listing with each card having a mouseover tooltip. This style can be changed to an embedded view, where the listing has a card image to the right of the deck. This can be toggled in the plugin settings for all decks, or specifically for a deck by setting the style attribute to the tag, as can be seen below.

    [d title="Really Small Deck" style="embedded"]
        Creatures
        2 Bloodbraid Elf
        4 Grizzly Bears

        Spells
        4 Lightning Bolt

        Sideboard
        4 Cultivate
    [/d]

The screenshot section includes an image of the above deck listing.

**Embedded Card Display**: Individual card shortcodes also support the embedded style, displaying the card image inline instead of as a tooltip:

    [card style="embedded"]Lightning Bolt[/card]
    [c style="embedded"]Tarmogoyf (FUT) 153[/c]

**Arena Format Support**: You can now paste deck lists directly from MTG Arena (or other compatible exports - like Moxfield) with set codes and collector numbers:

    [deck]
    4 Lightning Bolt (M10) 146
    4 Mountain (FIN)
    2 Snapcaster Mage
    [/deck]

The plugin will display only the card name and link to the specific printing when set code and number are provided. Set codes are case-insensitive. This format also works with the [card] shortcode: [card]Lightning Bolt (M10) 146[/card]

== Installation ==

1. Head over to the "Install Plugins" section of your Admin Panel, and use the "Upload" link to install the zip file.
2. Activate the plugin through the 'Plugins' menu.
3. Manually write the [c] tags for cards, or add a shortcode block for decks ([d]).

Alternatively, you can search for "Magic the Gathering" from the Admin panel Plugins section, select this plugin and click 'Install'.

== Frequently Asked Questions ==

= Do you support other games? =

Yes, there is a separate plugin for World of Warcraft TCG card tooltips, you can find it at [http://wordpress.org/extend/plugins/world-of-warcraft-card-tooltips/](http://wordpress.org/extend/plugins/world-of-warcraft-card-tooltips/).

= Is the 3.0 and 2.0 version compatible with the older ones? =

Completely compatible: your old posts will remain the same as before, even though your new posts will use the tag syntax.

== Screenshots ==

1. The mouseover effect
2. An example of a really small deck listing, produced by the code shown in the description

== Changelog ==

= 3.8.0 =
* Add embedded style support for individual card shortcodes
* Cards with style="embedded" display the card image inline instead of as a tooltip
* Add rounded corners to card images for better visual appearance

= 3.7.0 =
* Add support for MTG Arena deck format with set codes and collector numbers
* Cards can now specify exact printings using format: "Card Name (SET) 123"
* Display text shows only card name, links include set/number for accurate tooltips
* Works in both deck listings and card shortcodes
* Test on latest wordpress (6.9)

= 3.6.0 =
* Cleanup plugin options processing and sanitization. Use a nonce in the form.

= 3.5.0 =
* Fix handling of the style attribute on deck tags
* Test on latest wordpress (6.7.1)

= 3.4.0 =
* Rephrase the readme to refer to shortcode blocks in the visual editor, since newer wordpress versions do not have the old tinymce editor for which we had the icons.
* Test on latest wordpress (6.6.2)

= 3.3.0 =
* Set table width to 100% for decks. max-width is still at 510px so it should
  not change anything for existing users. The max-width can be set from
  the plugin options to allow larger deckview sizes.

= 3.2.0 =
* Fixed tooltips on mobile, updated wp compat to 6.0.

= 3.1.6 =
* Improve responsiveness, do not force width in px. Move Lands by default to second column.

= 3.1.5 =
* Improve responsiveness, do not force width in px. Move Lands by default to second column.

= 3.1.0 =
* Added support for bbpress. Shortcodes are not working there by default.

= 3.0.3 =
* Small fix: removed unnecessary echo

= 3.0.2 =
* Added configuration options for font size and line height for deck listings

= 3.0.1 =
* Small fixes to the embedded view.

= 3.0.0 =
* Implement embedded view for decks (no floating card tooltips on mouseover, but the card image
  is present to the right of the listing)
* Add a few new shortcodes [c], [card], [d] and [deck]
* Add a customization option for the width of the deck listing display

= 2.0.0 =
* Rewrote the card button to use shortcode tags.
* Implemented deck listing support.

= 1.0.3 =
* Button in editor was not showing. Now it works.

= 1.0.2 =
* Cleanup header information in php file to correctly represent released version.

= 1.0.1 =
* Test on newer Wordpress versions, cleanup readme file.

= 1.0.0 =
* Initial plugin release
