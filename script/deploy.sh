#!/bin/bash

# The brilliant wp_release deploy script we use is made by Olof Montin:
#   https://github.com/spurge/wordpress-plugin-git-svn/tree/e41c818def53ae9bf02ad2873e5389ee1612266f 

wp_release -n magic-the-gathering-card-tooltips -s http://plugins.svn.wordpress.org/magic-the-gathering-card-tooltips/ -g git@github.com:SebastianZaha/wordpress_mtg_tooltips.git
