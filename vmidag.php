<?php

/*
Plugin Name: VM idag Widget
Plugin URI: http://www.vmidag.se/wp-widget
Description: Adds a sidebar widget showing the today's World Cup games.
Author: Tobias Sjösten
Version: 1.0
Author URI: http://www.tobiassjosten.net/
*/


function widget_vmidag_init() {
  if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control')) {
    return;
  }

  function widget_vmidag($args) {
    $data = _vmidag_get_games_data();

    if (empty($data) || empty($data->games)) {
      return;
    }

    echo '<li id="vmidag" class="widget widget_vmidag">';
    echo '<h2 class="widgettitle">Dagens VM-matcher</h2>';

    $today_start = mktime(0, 0, 0);
    $today_end = mktime(23, 59, 59);

    $no_game_today = true;
    foreach ($data->games as $game) {
      if ($game->kickoff >= $today_start && $game->kickoff <= $today_end) {
        $no_game_today = false;
        echo sprintf('<p>%s – %s kl %s på %s</p>',
          '<a href="'.$game->home_team_uri_user.'" rel="external" title="Spelschema för '.$game->home_team.' under VM">'.$game->home_team.'</a>',
          '<a href="'.$game->away_team_uri_user.'" rel="external" title="Spelschema för '.$game->away_team.' under VM">'.$game->away_team.'</a>',
          date('H:i', $game->kickoff),
          $game->channel
        );
      }
    }

    if ($no_game_today) {
      echo '<p>Idag spelas tyvärr inga VM-matcher!</p>';
    }

    echo '</li>';
  }

  register_sidebar_widget(array('vmidag', 'widgets'), 'widget_vmidag');
}

/**
 * Helper function to fetch games data from vmidag.se.
 */
function _vmidag_get_games_data() {
  $data = wp_cache_get('games.json', 'vmidag');

  if (empty($data)) {
    echo('cache:"'.print_r($data,1).'"');
    $contents = wp_remote_fopen('http://local.vmidag.se/frontend_dev.php/games.json');
    $data = json_decode($contents);

    wp_cache_set('games.json', $data, 'vmidag');
  }

  return $data;
}

/**
 * Hook into WP and declare our widget.
 */
add_action('widgets_init', 'widget_vmidag_init');
