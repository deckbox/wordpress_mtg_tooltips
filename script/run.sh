#!/bin/bash

deploy() {
  # The brilliant wp_release deploy script we use is made by Olof Montin:
  #   https://github.com/spurge/wordpress-plugin-git-svn/tree/e41c818def53ae9bf02ad2873e5389ee1612266f
  bash "$(dirname "$0")/wp_release" -n magic-the-gathering-card-tooltips -s http://plugins.svn.wordpress.org/magic-the-gathering-card-tooltips/ -g https://github.com/SebastianZaha/wordpress_mtg_tooltips.git
}

copy_to_wp() {
  local plugin_path="$HOME/devel/_tmp/wordpress/wp-content/plugins/magic-the-gathering-card-tooltips"
  rm -rf "$plugin_path"
  cp -fr "$(dirname "$0")/../../wordpress_mtg_tooltips" "$plugin_path"
}

function help {
  printf "%s <task> [args]\n\nTasks:\n" "${0}"
  compgen -A function | grep -v "^_" | cat -n
}

# This idea is heavily inspired by: https://github.com/adriancooney/Taskfile
TIMEFORMAT=$'\nTask completed in %3lR'
time "${@:-help}"