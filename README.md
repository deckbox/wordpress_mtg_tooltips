Plugin for MTG Card Tooltips
============================

Enables card images to show on mouseover for Magic the Gathering cards. It
provides a button (and corresponding shortcode tags) that wrap card names or
deck listings and turn them into smart card links with image tooltips.


More Information
----------------

For more details, screenshots and installation instructions please see the 
[official wordpress readme.txt](https://github.com/SebastianZaha/wordpress_mtg_tooltips/blob/master/readme.txt).

For bbpress forum compatibility I have included in the distribution Pippin's
fix for bbpress shortcodes [from here](http://wordpress.org/extend/plugins/bbpress-do-short-codes/).


Examples
--------

```
[d title="Really Small Deck" style="embedded"]
    Creatures
    2 Bloodbraid Elf
    4 Grizzly Bears

    Spells
    4 Lightning Bolt

    Sideboard
    4 Cultivate
[/d]
```

produces the following result:

![screenshot](https://github.com/SebastianZaha/wordpress_mtg_tooltips/blob/master/screenshot-2.png)


Support and Development
-----------------------

The code in this plugin is inspired from other shortcode-type wordpress plugins that looked 
solid to me. The spacing and conventions try to stay close to the 
[pear standards](http://pear.php.net/manual/en/standards.php). I am not a PHP developer, so I'm 
pretty sure the code here is not *the right way to do it*. 

I'll gladly accept pull requests with improvements and / or code cleanup.

