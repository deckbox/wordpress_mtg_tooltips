=== Magic the Gathering Card Tooltips ===
Contributors: grimdonkey, BrentClark
Tags: magic the gathering, deckbox, MtG, tcg, ccg, magic, cards, tooltips
Requires at least: 2.8.6
Tested up to: 4.3.1
Stable tag: 3.1.3

Easily transform Magic the Gathering card names into links that show the card
image in a tooltip when hovering over them. You can also quickly create deck listings.

== Description ==

The plugin adds a button in the visual post editor. To use it, just select the text
that represents a Magic the Gathering card name and click the button. The card name
will be enclosed in [mtg_card][/mtg_card] tags (or the shorthand [c][/c] tag). When
viewing the post, the card name will show up as a link to the card's page on
https://deckbox.org . Hovering over the link will show the card's image in a tooltip.

A similar tag can be used to quickly create deck listings: [mtg_deck][/mtg_deck]
(or the shorthand [d][/d]). A deck listing should contain a list of cards and categories.
All cards have a number before their name. All other lines are interpreted as category
names. Do *not* include card numbers on the category name lines, they will automatically
be computed and displayed by the plugin. A short example follows.

The default style for displaying decks is a card listing with each card having a
mouseover tooltip. This style can be changed to an embedded view, where the
listing has a card image to the right of the deck. This can be toggled in the plugin
settings for all decks, or specifically for a deck by setting the style attribute to
the tag, as can be seen below.

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

== Installation ==

1. Head over to the "Install Plugins" section of your Admin Panel, and use the "Upload"
   link to install the zip file.
2. Activate the plugin through the 'Plugins' menu.
3. Use the "MtG" button in the editor, or manually write the tags.

Alternatively, you can search for "Magic the Gathering" from the Admin panel Plugins
section, select this plugin and click 'Install'.

== Frequently Asked Questions ==

= Do you support other games? =

Yes, there is a separate plugin for World of Warcraft TCG card tooltips, you can find it at
[http://wordpress.org/extend/plugins/world-of-warcraft-card-tooltips/](http://wordpress.org/extend/plugins/world-of-warcraft-card-tooltips/).

= Is the 3.0 and 2.0 version compatible with the older ones? =

Completely compatible: your old posts will remain the same as before, even though your new
posts will use the tag syntax.

== Screenshots ==

1. The mouseover effect
2. An example of a really small deck listing, produced by the code shown in the description

== Changelog ==

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
