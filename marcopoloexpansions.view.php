<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com> & Vinicius Rezende <vinicius@rezende.dev>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * marcopoloexpansions.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in marcopoloexpansions_marcopoloexpansions.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_marcopoloexpansions_marcopoloexpansions extends game_view
{
  function getGameName()
  {
    return "marcopoloexpansions";
  }

  function insertPlayerMatBlock($other_player, $player)
  {
    $color_map = array("ff0000" => "red", "008000" => "green", "0000ff" => "blue", "ffa500" => "yellow", "663399" => "purple");
    $background_color = "";
    $player_color_name = $color_map[$player["player_color"]];
    $other_player_parchment = $other_player ? "other_player" : "";
    $this->page->insert_block("playerMat", array(
      "PLAYER_ID" => $player["player_id"], "PLAYER_NAME" => $player["player_name"],
      "PLAYER_COLOR_NAME" => $player_color_name, "PLAYER_COLOR" => $player["player_color"], "PLAYER_BACKGROUND_COLOR" => $background_color,
      "CSS_OTHER_PLAYER_PARCHMENT" => $other_player_parchment
    ));
  }

  function build_page($viewArgs)
  {
    // Get players & players number
    global $g_user;
    $your_player_id = $g_user->get_id();
    $players = $this->game->loadPlayersBasicInfos();
    $players_nbr = count($players);
    $top_half_players = [];
    $bottom_half_players = [];

    $this->tpl['TRADING_POST_POINTS_DESC'] = self::_("5/10 VP for placing 8th/9th trading post.");
    $this->tpl['CONTRACT_POINTS_DESC'] = self::_("7 VP for most fulfilled contracts at end of game.");
    $this->tpl['ROUND'] = self::_("Round: ");
    $this->tpl['HIDE_PIECES'] = self::_("Toggle map pieces");
    $this->page->begin_block("marcopoloexpansions_marcopoloexpansions", "playerMat");

    foreach ($players as $player_id => $player) {
      if ($player_id == $your_player_id || sizeof($top_half_players) > 0) {
        $top_half_players[$player_id] = $player;
      } else {
        $bottom_half_players[$player_id] = $player;
      }
    }

    $ordered_players = array_merge($top_half_players, $bottom_half_players);
    foreach ($ordered_players as $player_id => $player) {
      $this->insertPlayerMatBlock($player_id != $your_player_id, $player);
    }

    /*********** Do not change anything below this line  ************/
  }
}
