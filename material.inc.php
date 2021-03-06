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
 * material.inc.php
 *
 * MarcoPoloExpansions game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */
$this->goal_card_types = array(
  0 => array("type" => 0, "vp" => 4, "cities" => ["2", "14"]),
  1 => array("type" => 1, "vp" => 5, "cities" => ["2", "18"]),
  2 => array("type" => 2, "vp" => 7, "cities" => ["2", "12"]),
  3 => array("type" => 3, "vp" => 4, "cities" => ["4", "14"]),
  4 => array("type" => 4, "vp" => 5, "cities" => ["4", "24"]),
  5 => array("type" => 5, "vp" => 7, "cities" => ["4", "23"]),
  6 => array("type" => 6, "vp" => 4, "cities" => ["13", "24"]),
  7 => array("type" => 7, "vp" => 5, "cities" => ["13", "23"]),
  8 => array("type" => 8, "vp" => 6, "cities" => ["13", "18"]),
  9 => array("type" => 9, "vp" => 4, "cities" => ["10", "14"]),
  10 => array("type" => 10, "vp" => 5, "cities" => ["10", "23"]),
  11 => array("type" => 11, "vp" => 7, "cities" => ["10", "24"]),
  12 => array("type" => 12, "vp" => 4, "cities" => ["16", "3"]),
  13 => array("type" => 13, "vp" => 5, "cities" => ["16", "12"]),
  14 => array("type" => 14, "vp" => 6, "cities" => ["21", "18"]),
  15 => array("type" => 15, "vp" => 8, "cities" => ["21", "3"]),
  16 => array("type" => 16, "vp" => 5, "cities" => ["19", "12"]),
  17 => array("type" => 17, "vp" => 7, "cities" => ["19", "3"]),
);

$this->outpost_bonus_types = array(
  0 => array("type" => 0, "award" => ["coin" => 3]),
  1 => array("type" => 1, "award" => ["choice_of_good" => 1]),
  2 => array("type" => 2, "award" => ["travel" => 1]),
  3 => array("type" => 3, "award" => ["contract" => 1]),
  4 => array("type" => 4, "award" => ["gold" => 2]),
  5 => array("type" => 5, "award" => ["black_die" => 1]),
  6 => array("type" => 6, "award" => ["silk" => 2]),
  7 => array("type" => 7, "award" => ["camel" => 3]),
  8 => array("type" => 8, "award" => ["camel" => 2]),
  9 => array("type" => 9, "award" => ["coin" => 5]),
);

$this->city_bonus_types = array(
  0 => array("type" => 0, "award" => ["coin" => 5], "default_location" => 3, "auto" => true),
  1 => array("type" => 1, "award" => ["camel" => 3], "default_location" => 18, "auto" => true),
  2 => array("type" => 2, "award" => ["2_diff_goods" => 1], "default_location" => 12),
  3 => array("type" => 3, "award" => ["trigger_other_city_bonus" => 1], "default_location" => 14, "required" => true),
  4 => array("type" => 4, "award" => ["camel" => 1, "coin" => 3], "default_location" => 23, "auto" => true),
  5 => array("type" => 5, "award" => ["vp" => 3], "default_location" => 24, "auto" => true),
  6 => array("type" => 6, "award" => ["gift" => 1], "expansion" => 0),
);

$this->city_card_types = array(
  0 => array("place" => "city_card", "type" => 0, "num_dice" => 1, "kind" => "multiple", "cost" => [], "award" => ["coin" => 2],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get twice as many coins as the number on the die placed')),
  1 => array("place" => "city_card", "type" => 1, "num_dice" => 1, "kind" => "die", "award" => ["gold" => [1, 1, 1, 1, 1, 3]], "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 3 gold if placing a 6 valued die otherwise 1 gold')),
  2 => array("place" => "city_card", "type" => 2, "num_dice" => 1, "kind" => "multiple", "cost" => ["placed_trading_post" => 1], "award" => ["vp" => 1],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 1VP for each of your placed trading posts (Max number = die value).')),
  3 => array("place" => "city_card", "type" => 3, "num_dice" => 1, "kind" => "multiple", "cost" => ["gold" => 2], "award" => ["vp" => 4],  "allow_multiple" => false, "auto" => false),
  4 => array("place" => "city_card", "type" => 4, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "pepper" => 1], "award" => ["coin" => 2, "vp" => 2],  "allow_multiple" => false, "auto" => false),
  5 => array("place" => "city_card", "type" => 5, "num_dice" => 1, "kind" => "multiple", "cost" => ["silk" => 2], "award" => ["vp" => 3],  "allow_multiple" => false, "auto" => false),
  6 => array("place" => "city_card", "type" => 6, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 3], "award" => ["pepper" => 1, "silk" => 1, "gold" => 1],  "allow_multiple" => false, "auto" => false),
  7 => array("place" => "city_card", "type" => 7, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "silk" => 1], "award" => ["coin" => 8],  "allow_multiple" => false, "auto" => false),
  8 => array("place" => "city_card", "type" => 8, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "gold" => 1], "award" => ["coin" => 9],  "allow_multiple" => false, "auto" => false),
  9 => array("place" => "city_card", "type" => 9, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "silk" => 1], "award" => ["coin" => 4, "vp" => 2],  "allow_multiple" => false, "auto" => false),
  10 => array("place" => "city_card", "type" => 10, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 2, "silk" => 1], "award" => ["vp" => 4],  "allow_multiple" => false, "auto" => false),
  11 => array("place" => "city_card", "type" => 11, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "pepper" => 1], "award" => ["coin" => 7],  "allow_multiple" => false, "auto" => false),
  12 => array("place" => "city_card", "type" => 12, "num_dice" => 1, "kind" => "multiple", "cost" => ["coin" => 2], "award" => ["choice_of_good" => 1],  "allow_multiple" => false, "auto" => false),
  13 => array("place" => "city_card", "type" => 13, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 2], "award" => ["travel" => 1],  "allow_multiple" => false, "auto" => false),
  14 => array("place" => "city_card", "type" => 14, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 2], "award" => ["coin" => 2, "vp" => 1],  "allow_multiple" => false, "auto" => false),
  15 => array("place" => "city_card", "type" => 15, "num_dice" => 1, "kind" => "multiple", "cost" => ["fulfilled_contracts" => 1], "award" => ["vp" => 1],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 1VP per completed contract up to die value')),
  16 => array("place" => "city_card", "type" => 16, "num_dice" => 1, "kind" => "choice", "choice" => array(["cost" => ["camel" => 1], "award" => ["coin" => 3]], ["cost" => ["coin" => 1], "award" => ["camel" => 1]]),  "allow_multiple" => false, "auto" => false, "description" => clienttranslate('Trade in 1 camel for 3 coins or 1 coin for 1 camel as many times as die value.  Must pick the same exchange each time.')),
  17 => array("place" => "city_card", "type" => 17, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1], "award" => ["vp" => 1],  "allow_multiple" => false, "auto" => false),
  18 => array("place" => "city_card", "type" => 18, "num_dice" => 1, "kind" => "die", "award" => ["pepper" => [1, 3, 3, 3, 3, 3]],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 1 pepper if placing a 1 valued die otherwise 3 pepper')),
  19 => array("place" => "city_card", "type" => 19, "num_dice" => 1, "kind" => "exchange", "cost" => ["vp" => 1], "award" => ["coin" => 3],  "allow_multiple" => false, "auto" => false, "description" => clienttranslate('Exchange 1VP for 3 coins or vice versa up to die value')),
  20 => array("place" => "city_card", "type" => 20, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "gold" => 1], "award" => ["coin" => 6, "vp" => 2],  "allow_multiple" => false, "auto" => false),
  21 => array("place" => "city_card", "type" => 21, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 1, "gold" => 1], "award" => ["vp" => 4],  "allow_multiple" => false, "auto" => false),
  22 => array("place" => "city_card", "type" => 22, "num_dice" => 1, "kind" => "multiple", "cost" => ["placed_trading_post" => 1], "award" => ["coin" => 2],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 2 coins per placed trading post up to die value')),
  23 => array("place" => "city_card", "type" => 23, "num_dice" => 1, "kind" => "die", "award" => ["trigger_city_bonus_having_trading_post" => [1, 1, 1, 1, 2, 2]],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('<p>Trigger small city bonus where you have trading posts.  Must be different cities.</p><p>Number of times depends on die value.  Twice if die is 5 or 6 otherwise once.</p>')),
  24 => array("place" => "city_card", "type" => 24, "num_dice" => 1, "kind" => "multiple", "cost" => ["fulfilled_contracts" => 1], "award" => ["coin" => 2],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 2 coins per completed contract up to die value')),
  25 => array("place" => "city_card", "type" => 25, "num_dice" => 1, "kind" => "multiple", "cost" => ["coin" => 1], "award" => ["trigger_city_bonus_having_trading_post" => 1],  "allow_multiple" => false, "auto" => false, "description" => clienttranslate('<p>Trigger small city bonus where you have trading posts.  Must be different cities.</p><p>Spend 1 coin per trigger up to die value</p>')),
  26 => array("place" => "city_card", "type" => 26, "num_dice" => 1, "kind" => "die", "award" => ["silk" => [1, 1, 1, 3, 3, 3]],  "allow_multiple" => false, "auto" => true, "description" => clienttranslate('Get 1 silk if placing a 1-3 valued die otherwise 3 silk')),
  27 => array("place" => "city_card", "type" => 27, "num_dice" => 1, "kind" => "exchange", "cost" => ["choice_of_good" => 1], "award" => ["camel" => 2],  "allow_multiple" => false, "auto" => false, "description" => clienttranslate('Exchange a pepper, silk, or gold for 2 camels or vice versa up to die value')),
  28 => array("place" => "city_card", "type" => 28, "num_dice" => 1, "kind" => "multiple", "cost" => ["pepper" => 2], "award" => ["vp" => 2],  "allow_multiple" => false, "auto" => false),
  29 => array("place" => "city_card", "type" => 29, "num_dice" => 1, "kind" => "multiple", "cost" => ["camel" => 3, "pepper" => 1], "award" => ["vp" => 4],  "allow_multiple" => false, "auto" => false),
  30 => array("place" => "city_card", "type" => 30, "num_dice" => 1, "kind" => "multiple", "cost" => ["2_diff_goods" => 1], "award" => ["travel" => 1],  "allow_multiple" => false, "auto" => false),
);

$this->gift_types = array(
  0 => array("type" => 0, "auto" => true, "award" => ["camel" => 3]),
  1 => array("type" => 1, "award" => ["contract" => 2]),
  2 => array("type" => 2, "award" => ["contract" => 1]),
  3 => array("type" => 3, "auto" => true, "award" => ["blackdie_or_3coins" => 1]),
  4 => array("type" => 4, "description" =>  clienttranslate("Change the value of one die"), "award" => [], "invalid_character_type" => 2),
  5 => array("type" => 5, "auto" => true, "award" => ["camel" => 2]),
  6 => array("type" => 6, "auto" => true, "award" => ["choice_of_good" => 1]),
  7 => array("type" => 7, "description" => clienttranslate("Free dice placement"), "award" => [], "invalid_character_type" => 6),
  8 => array("type" => 8, "auto" => true, "award" => ["coin" => 5]),
  9 => array("type" => 9, "auto" => true, "award" => ["choice_of_good" => 2], "others_award" => ["gold" => 1]),
  10 => array("type" => 10, "description" => clienttranslate("While moving, place a trading post in a city you pass through"), "award" => [], "invalid_character_type" => 4),
  11 => array("type" => 11, "auto" => true, "cost" => ["placed_trading_post" => 1], "award" => ["vp" => 1], "max_times" => 6),
  12 => array("type" => 12, "auto" => true, "cost" => ["fulfilled_contracts" => 1], "award" => ["vp" => 1], "max_times" => 6),
  13 => array("type" => 13, "auto" => true, "award" => ["coin" => 8], "others_award" => ["coin" => 3]),
  14 => array("type" => 14, "award" => ["travel" => 1]),
);

$this->contract_types = array(
  0 => array("type" => 0, "starter" => false, "cost" => ["camel" => 2, "pepper" => 3, "gold" => 2], "award" => ["camel" => 5, "vp" => 7]),
  1 => array("type" => 1, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 1], "award" => ["contract" => 1, "vp" => 6]),
  2 => array("type" => 2, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "gold" => 2], "award" => ["camel" => 4, "vp" => 6]),
  3 => array("type" => 3, "starter" => false, "cost" => ["camel" => 2, "pepper" => 1, "silk" => 2], "award" => ["coin" => 3, "vp" => 4]),
  4 => array("type" => 4, "starter" => false, "cost" => ["camel" => 1, "gold" => 4], "award" => ["camel" => 3, "vp" => 7]),
  5 => array("type" => 5, "starter" => false, "cost" => ["camel" => 2, "silk" => 3, "gold" => 2], "award" => ["camel" => 4, "vp" => 8]),
  6 => array("type" => 6, "starter" => false, "cost" => ["camel" => 1, "silk" => 3], "award" => ["choice_of_good" => 1, "vp" => 3]),
  7 => array("type" => 7, "starter" => false, "cost" => ["camel" => 2, "silk" => 3, "gold" => 2], "award" => ["contract" => 1, "vp" => 8]),
  8 => array("type" => 8, "starter" => false, "cost" => ["camel" => 2, "pepper" => 3, "gold" => 2], "award" => ["coin" => 7, "vp" => 6]),
  9 => array("type" => 9, "starter" => false, "cost" => ["camel" => 2, "pepper" => 2, "gold" => 1], "award" => ["travel" => 1, "vp" => 4]),
  10 => array("type" => 10, "starter" => false, "cost" => ["camel" => 2, "silk" => 3, "pepper" => 2], "award" => ["choice_of_good" => 1, "black_die" => 1, "vp" => 5]),
  11 => array("type" => 11, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 1], "award" => ["travel" => 1, "vp" => 5]),
  12 => array("type" => 12, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 1], "award" => ["camel" => 4, "vp" => 4]),
  13 => array("type" => 13, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 1], "award" => ["choice_of_good" => 1, "vp" => 6]),
  14 => array("type" => 14, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 1], "award" => ["2_diff_goods" => 1, "vp" => 5]),
  15 => array("type" => 15, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 2], "award" => ["travel" => 1, "vp" => 7]),
  16 => array("type" => 16, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "pepper" => 3], "award" => ["travel" => 1, "vp" => 5]),
  17 => array("type" => 17, "starter" => false, "cost" => ["camel" => 2, "pepper" => 2, "gold" => 3], "award" => ["coin" => 7, "vp" => 8]),
  18 => array("type" => 18, "starter" => false, "cost" => ["camel" => 1, "pepper" => 3], "award" => ["black_die" => 1, "vp" => 2]),
  19 => array("type" => 19, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "gold" => 2], "award" => ["black_die" => 1, "vp" => 6]),
  20 => array("type" => 20, "starter" => false, "cost" => ["camel" => 2, "pepper" => 1, "gold" => 2], "award" => ["black_die" => 1, "vp" => 5]),
  21 => array("type" => 21, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 3], "award" => ["coin" => 7, "vp" => 9]),
  22 => array("type" => 22, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 3], "award" => ["travel" => 1, "vp" => 9]),
  23 => array("type" => 23, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 2, "gold" => 1], "award" => ["camel" => 3, "contract" => 1, "vp" => 5]),
  24 => array("type" => 24, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "gold" => 2], "award" => ["coin" => 4, "vp" => 6]),
  25 => array("type" => 25, "starter" => false, "cost" => ["camel" => 2, "silk" => 3, "pepper" => 2], "award" => ["coin" => 6, "vp" => 5]),
  26 => array("type" => 26, "starter" => false, "cost" => ["camel" => 1, "gold" => 3], "award" => ["choice_of_good" => 1, "vp" => 6]),
  27 => array("type" => 27, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "pepper" => 2], "award" => ["coin" => 4, "vp" => 3]),
  28 => array("type" => 28, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "pepper" => 2], "award" => ["2_diff_goods" => 1, "vp" => 3]),
  29 => array("type" => 29, "starter" => true, "cost" => ["camel" => 2, "silk" => 1, "gold" => 1], "award" => ["2_diff_goods" => 1, "vp" => 4]),
  30 => array("type" => 30, "starter" => false, "cost" => ["camel" => 2, "pepper" => 2, "gold" => 1], "award" => ["contract" => 1, "vp" => 4]),
  31 => array("type" => 31, "starter" => false, "cost" => ["camel" => 2, "pepper" => 2, "gold" => 3], "award" => ["2_diff_goods" => 1, "vp" => 9]),
  32 => array("type" => 32, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "pepper" => 2], "award" => ["black_die" => 1, "vp" => 5]),
  33 => array("type" => 33, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 2], "award" => ["coin" => 5, "vp" => 7]),
  34 => array("type" => 34, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 1, "gold" => 1], "award" => ["black_die" => 1, "vp" => 5]),
  35 => array("type" => 35, "starter" => false, "cost" => ["camel" => 3, "pepper" => 1, "silk" => 2, "gold" => 1], "award" => ["camel" => 3, "coin" => 4, "vp" => 5]),
  36 => array("type" => 36, "starter" => false, "cost" => ["camel" => 1, "gold" => 4], "award" => ["travel" => 1, "vp" => 7]),
  37 => array("type" => 37, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 2], "award" => ["contract" => 1, "vp" => 7]),
  38 => array("type" => 38, "starter" => false, "cost" => ["camel" => 1, "pepper" => 4], "award" => ["contract" => 1, "vp" => 3]),
  39 => array("type" => 39, "starter" => true, "cost" => ["camel" => 2, "silk" => 1, "pepper" => 1], "award" => ["contract" => 1, "vp" => 5]),
  40 => array("type" => 40, "starter" => true, "cost" => ["camel" => 2, "pepper" => 1, "gold" => 1], "award" => ["black_die" => 1, "vp" => 4]),
  41 => array("type" => 41, "starter" => true, "cost" => ["camel" => 1, "gold" => 1], "award" => ["camel" => 3, "vp" => 2]),
  42 => array("type" => 42, "starter" => true, "cost" => ["camel" => 1, "pepper" => 3], "award" => ["travel" => 1, "vp" => 1]),
  43 => array("type" => 43, "starter" => true, "cost" => ["camel" => 1, "silk" => 2], "award" => ["coin" => 5, "vp" => 3]),
  44 => array("type" => 44, "starter" => false, "cost" => ["camel" => 2, "silk" => 1, "gold" => 3], "award" => ["coin" => 5, "gift" => 1, "vp" => 6], "expansion" => 0),
  45 => array("type" => 45, "starter" => false, "cost" => ["camel" => 2, "pepper" => 1, "silk" => 1], "award" => ["gift" => 1, "vp" => 3], "expansion" => 0),
  46 => array("type" => 46, "starter" => false, "cost" => ["camel" => 1, "pepper" => 3], "award" => ["camel" => 2, "gift" => 1, "vp" => 2], "expansion" => 0),
  47 => array("type" => 47, "starter" => false, "cost" => ["camel" => 2, "silk" => 2, "gold" => 2], "award" => ["gift" => 1, "choice_of_good" => 1, "vp" => 4], "expansion" => 0),
);

$this->board_map = array(
  0 => array("id" => 0, "name" => "Venezia", "type" => "start"),
  1 => array("id" => 1, "name" => "Oasis", "type" => "oasis"),
  2 => array("id" => 2, "name" => "Moscow", "type" => "large_city"),
  3 => array("id" => 3, "name" => "Anxi", "type" => "small_city"),
  4 => array("id" => 4, "name" => "Karakorum", "type" => "large_city"),
  5 => array("id" => 5, "name" => "Oasis", "type" => "oasis"),
  6 => array("id" => 6, "name" => "Oasis", "type" => "oasis"),
  7 => array("id" => 7, "name" => "Oasis", "type" => "oasis"),
  8 => array("id" => 8, "name" => "Bejing", "type" => "bejing"),
  9 => array("id" => 9, "name" => "Oasis", "type" => "oasis"),
  10 => array("id" => 10, "name" => "Samarcanda", "type" => "large_city"),
  11 => array("id" => 11, "name" => "Oasis", "type" => "oasis"),
  12 => array("id" => 12, "name" => "Kashgar", "type" => "small_city"),
  13 => array("id" => 13, "name" => "Lan-Zhou", "type" => "large_city"),
  14 => array("id" => 14, "name" => "Xian", "type" => "small_city"),
  15 => array("id" => 15, "name" => "Oasis", "type" => "oasis"),
  16 => array("id" => 16, "name" => "Alexandria", "type" => "large_city"),
  17 => array("id" => 17, "name" => "Oasis", "type" => "oasis"),
  18 => array("id" => 18, "name" => "Ormuz", "type" => "small_city"),
  19 => array("id" => 19, "name" => "Karachi", "type" => "large_city"),
  20 => array("id" => 20, "name" => "Oasis", "type" => "oasis"),
  21 => array("id" => 21, "name" => "Sumatra", "type" => "large_city", "num_cards" => 3),
  22 => array("id" => 22, "name" => "Oasis", "type" => "oasis"),
  23 => array("id" => 23, "name" => "Adana", "type" => "small_city"),
  24 => array("id" => 24, "name" => "Kochi", "type" => "small_city"),
);

#only map data in one direction, code can create edge matrix or brute force look-up (not many edges)
$this->board_edges = array(
  array("src" => 0, "dst" => 1, "cost" => []),
  array("src" => 0, "dst" => 9, "cost" => ["camel" => 3]),
  array("src" => 0, "dst" => 16, "cost" => []),
  array("src" => 1, "dst" => 2, "cost" => []),
  array("src" => 2, "dst" => 3, "cost" => ["camel" => 3]),
  array("src" => 3, "dst" => 4, "cost" => ["camel" => 2]),
  array("src" => 4, "dst" => 5, "cost" => []),
  array("src" => 5, "dst" => 6, "cost" => ["coin" => 5]),
  array("src" => 5, "dst" => 7, "cost" => []),
  array("src" => 6, "dst" => 8, "cost" => []),
  array("src" => 7, "dst" => 8, "cost" => ["camel" => 3]),
  array("src" => 7, "dst" => 13, "cost" => []),
  array("src" => 8, "dst" => 14, "cost" => []),
  array("src" => 8, "dst" => 15, "cost" => []),
  array("src" => 9, "dst" => 10, "cost" => []),
  array("src" => 9, "dst" => 17, "cost" => []),
  array("src" => 10, "dst" => 11, "cost" => ["camel" => 2]),
  array("src" => 11, "dst" => 12, "cost" => ["camel" => 4]),
  array("src" => 12, "dst" => 13, "cost" => ["camel" => 2]),
  array("src" => 14, "dst" => 20, "cost" => []),
  array("src" => 15, "dst" => 21, "cost" => ["camel" => 4]),
  array("src" => 16, "dst" => 17, "cost" => ["coin" => 7]),
  array("src" => 16, "dst" => 22, "cost" => []),
  array("src" => 17, "dst" => 18, "cost" => ["camel" => 3]),
  array("src" => 18, "dst" => 19, "cost" => ["camel" => 3]),
  array("src" => 19, "dst" => 20, "cost" => []),
  array("src" => 20, "dst" => 24, "cost" => ["camel" => 4]),
  array("src" => 21, "dst" => 24, "cost" => ["coin" => 10]),
  array("src" => 22, "dst" => 23, "cost" => ["camel" => 2]),
  array("src" => 23, "dst" => 24, "cost" => ["coin" => 15]),
);

$this->board_spots = array(
  array("place" => "coin3", "index" => 0, "name" => clienttranslate("3 coins"), "description" => clienttranslate("3 coins, counts as a bonus action.  No payment necessary."), "num_dice" => 1, "allow_multiple" => true, "is_award_spot" => false, "award" => ["coin" => 3], "ui_location" => "board_spot_coin3_0"),
  array("place" => "coin5", "index" => 0, "name" => clienttranslate("5 coins"), "description" => clienttranslate("5 coins"), "num_dice" => 1, "allow_multiple" => true, "is_award_spot" => false, "award" => ["coin" => 5], "ui_location" => "board_spot_coin5_0"),
  array("place" => "travel", "index" => 0, "name" => clienttranslate("Travel"), "description" =>  clienttranslate("Travel.  Last player to travel goes first next round."), "num_dice" => 2, "allow_multiple" => true, "is_award_spot" => false),
  array("place" => "travel", "index" => 0, "name" => clienttranslate("Travel"), "description" =>  "", "num_dice" => 2, "min_die" => 1, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 3], "award" => ["travel" => 1], "cost" => ["coin" => 3], "ui_location" => "award_spot_travel_0_0"),
  array("place" => "travel", "index" => 1, "name" => clienttranslate("Travel"), "description" => "", "num_dice" => 2, "min_die" => 2, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 7], "award" => ["travel" => 2], "cost" => ["coin" => 7], "ui_location" => "award_spot_travel_0_1"),
  array("place" => "travel", "index" => 2, "name" => clienttranslate("Travel"), "description" => "", "num_dice" => 2, "min_die" => 3, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 12], "award" => ["travel" => 3], "cost" => ["coin" => 12], "ui_location" => "award_spot_travel_0_2"),
  array("place" => "travel", "index" => 3, "name" => clienttranslate("Travel"), "description" => "", "num_dice" => 2, "min_die" => 4, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 12], "award" => ["travel" => 4], "cost" => ["coin" => 12], "ui_location" => "award_spot_travel_0_3"),
  array("place" => "travel", "index" => 4, "name" => clienttranslate("Travel"), "description" => "", "num_dice" => 2, "min_die" => 5, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 18], "award" => ["travel" => 5], "cost" => ["coin" => 18], "ui_location" => "award_spot_travel_0_4"),
  array("place" => "travel", "index" => 5, "name" => clienttranslate("Travel"), "description" => "", "num_dice" => 2, "min_die" => 6, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "cost" => ["coin" => 18], "award" => ["travel" => 6], "cost" => ["coin" => 18], "ui_location" => "award_spot_travel_0_5"),
  array("place" => "bazaar", "index" => 0, "name" => clienttranslate("The grand bazaar"), "description" => clienttranslate("The Grand Bazaar - camels"), "num_dice" => 1, "allow_multiple" => true, "is_award_spot" => false),
  array("place" => "bazaar", "index" => 0, "name" => clienttranslate("The grand bazaar"), "description" => "1 camel", "num_dice" => 1, "min_die" => 1, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 1], "ui_location" => "award_spot_bazaar_0_0"),
  array("place" => "bazaar", "index" => 1, "name" => clienttranslate("The grand bazaar"), "description" => "2 camel", "num_dice" => 1, "min_die" => 2, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 2], "ui_location" => "award_spot_bazaar_0_1"),
  array("place" => "bazaar", "index" => 2, "name" => clienttranslate("The grand bazaar"), "description" => "3 camel", "num_dice" => 1, "min_die" => 3, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 3], "ui_location" => "award_spot_bazaar_0_2"),
  array("place" => "bazaar", "index" => 3, "name" => clienttranslate("The grand bazaar"), "description" => "4 camel", "num_dice" => 1, "min_die" => 4, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 4], "ui_location" => "award_spot_bazaar_0_3"),
  array("place" => "bazaar", "index" => 4, "name" => clienttranslate("The grand bazaar"), "description" => "5 camel", "num_dice" => 1, "min_die" => 5, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 5], "ui_location" => "award_spot_bazaar_0_4"),
  array("place" => "bazaar", "index" => 5, "name" => clienttranslate("The grand bazaar"), "description" => "6 camel", "num_dice" => 1, "min_die" => 6, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 0, "award" => ["camel" => 6], "ui_location" => "award_spot_bazaar_0_5"),
  array("place" => "bazaar", "index" => 1, "name" => clienttranslate("The grand bazaar"), "description" => clienttranslate("The Grand Bazaar - pepper"), "num_dice" => 1, "allow_multiple" => true, "is_award_spot" => false),
  array("place" => "bazaar", "index" => 0, "name" => clienttranslate("The grand bazaar"), "description" => "1 pepper", "num_dice" => 1, "min_die" => 1, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 1], "ui_location" => "award_spot_bazaar_1_0"),
  array("place" => "bazaar", "index" => 1, "name" => clienttranslate("The grand bazaar"), "description" => "2 pepper", "num_dice" => 1, "min_die" => 2, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 2], "ui_location" => "award_spot_bazaar_1_1"),
  array("place" => "bazaar", "index" => 2, "name" => clienttranslate("The grand bazaar"), "description" => "3 pepper", "num_dice" => 1, "min_die" => 3, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 2, "coin" => 1], "ui_location" => "award_spot_bazaar_1_2"),
  array("place" => "bazaar", "index" => 3, "name" => clienttranslate("The grand bazaar"), "description" => "4 pepper", "num_dice" => 1, "min_die" => 4, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 3], "ui_location" => "award_spot_bazaar_1_3"),
  array("place" => "bazaar", "index" => 4, "name" => clienttranslate("The grand bazaar"), "description" => "5 pepper", "num_dice" => 1, "min_die" => 5, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 3, "coin" => 2], "ui_location" => "award_spot_bazaar_1_4"),
  array("place" => "bazaar", "index" => 5, "name" => clienttranslate("The grand bazaar"), "description" => "6 pepper", "num_dice" => 1, "min_die" => 6, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 1, "award" => ["pepper" => 4], "ui_location" => "award_spot_bazaar_1_5"),
  array("place" => "bazaar", "index" => 2, "name" => clienttranslate("The grand bazaar"), "description" => clienttranslate("The Grand Bazaar - silk"), "num_dice" => 2, "allow_multiple" => true, "is_award_spot" => false),
  array("place" => "bazaar", "index" => 0, "name" => clienttranslate("The grand bazaar"), "description" => "1 silk", "num_dice" => 2, "min_die" => 1, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 1], "ui_location" => "award_spot_bazaar_2_0"),
  array("place" => "bazaar", "index" => 1, "name" => clienttranslate("The grand bazaar"), "description" => "2 silk", "num_dice" => 2, "min_die" => 2, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 2], "ui_location" => "award_spot_bazaar_2_1"),
  array("place" => "bazaar", "index" => 2, "name" => clienttranslate("The grand bazaar"), "description" => "3 silk", "num_dice" => 2, "min_die" => 3, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 2, "camel" => 1], "ui_location" => "award_spot_bazaar_2_2"),
  array("place" => "bazaar", "index" => 3, "name" => clienttranslate("The grand bazaar"), "description" => "4 silk", "num_dice" => 2, "min_die" => 4, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 3], "ui_location" => "award_spot_bazaar_2_3"),
  array("place" => "bazaar", "index" => 4, "name" => clienttranslate("The grand bazaar"), "description" => "5 silk", "num_dice" => 2, "min_die" => 5, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 3, "camel" => 1, "coin" => 1], "ui_location" => "award_spot_bazaar_2_4"),
  array("place" => "bazaar", "index" => 5, "name" => clienttranslate("The grand bazaar"), "description" => "6 silk", "num_dice" => 2, "min_die" => 6, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 2, "award" => ["silk" => 4], "ui_location" => "award_spot_bazaar_2_5"),
  array("place" => "bazaar", "index" => 3, "name" => clienttranslate("The grand bazaar"), "description" => clienttranslate("The Grand Bazaar - gold"), "num_dice" => 3, "allow_multiple" => true, "is_award_spot" => false),
  array("place" => "bazaar", "index" => 0, "name" => clienttranslate("The grand bazaar"), "description" => "1 gold", "num_dice" => 3, "min_die" => 1, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 1], "ui_location" => "award_spot_bazaar_3_0"),
  array("place" => "bazaar", "index" => 1, "name" => clienttranslate("The grand bazaar"), "description" => "2 gold", "num_dice" => 3, "min_die" => 2, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 2], "ui_location" => "award_spot_bazaar_3_1"),
  array("place" => "bazaar", "index" => 2, "name" => clienttranslate("The grand bazaar"), "description" => "3 gold", "num_dice" => 3, "min_die" => 3, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 2, "camel" => 2], "ui_location" => "award_spot_bazaar_3_2"),
  array("place" => "bazaar", "index" => 3, "name" => clienttranslate("The grand bazaar"), "description" => "4 gold", "num_dice" => 3, "min_die" => 4, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 3], "ui_location" => "award_spot_bazaar_3_3"),
  array("place" => "bazaar", "index" => 4, "name" => clienttranslate("The grand bazaar"), "description" => "5 gold", "num_dice" => 3, "min_die" => 5, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 3, "travel" => 1], "ui_location" => "award_spot_bazaar_3_4"),
  array("place" => "bazaar", "index" => 5, "name" => clienttranslate("The grand bazaar"), "description" => "6 gold", "num_dice" => 3, "min_die" => 6, "allow_multiple" => true, "is_award_spot" => true, "tied_to_index" => 3, "award" => ["gold" => 4], "ui_location" => "award_spot_bazaar_3_5"),
  array("place" => "khan", "index" => 0, "name" => clienttranslate("Khan`s favor"), "description" => clienttranslate("Khan`s favor - 2 camels and choice of one resource"), "num_dice" => 1, "allow_multiple" => false, "is_award_spot" => false, "award" => ["choice_of_good" => 1, "camel" => 2], "ui_location" => "board_spot_khan_0"),
  array("place" => "khan", "index" => 1, "name" => clienttranslate("Khan`s favor"), "description" => clienttranslate("Khan`s favor - 2 camels and choice of one resource"), "num_dice" => 1, "allow_multiple" => false, "is_award_spot" => false, "award" => ["choice_of_good" => 1, "camel" => 2], "ui_location" => "board_spot_khan_1"),
  array("place" => "khan", "index" => 2, "name" => clienttranslate("Khan`s favor"), "description" => clienttranslate("Khan`s favor - 2 camels and choice of one resource"), "num_dice" => 1, "allow_multiple" => false, "is_award_spot" => false, "award" => ["choice_of_good" => 1, "camel" => 2], "ui_location" => "board_spot_khan_2"),
  array("place" => "khan", "index" => 3, "name" => clienttranslate("Khan`s favor"), "description" => clienttranslate("Khan`s favor - 2 camels and choice of one resource"), "num_dice" => 1, "allow_multiple" => false, "is_award_spot" => false, "award" => ["choice_of_good" => 1, "camel" => 2], "ui_location" => "board_spot_khan_3"),
  array("place" => "contracts", "index" => 0, "name" => clienttranslate("Contracts"), "description" => clienttranslate("Contracts - Select new contracts.  Selecting the 5th contract awards choice of 1 coin or camel.  Selecting the 6th contract awards choice of 2 coins or 2 camels."), "num_dice" => 1, "allow_multiple" => true, "is_award_spot" => false, "award" => ["pick_contract" => 2], "ui_location" => "board_spot_contracts_0"),
);

$this->character_types = array(
  0 => array("type" => 0, "name" => clienttranslate("Mercator ex Tabriz"), "description" => clienttranslate("<p>4 players: Whenever another player chooses to go to the bazaar, you receive one of the goods that the other player chose. This also counts for camels.</p><p>3 players: Same as the 4-player version.  Additionally, whenever another player seeks the Khan’s favor you receive 1 camel</p><p>2 players: Same as the 3-player version.  Additionally, whenever the other player takes 5 coins, you receive 2 coins.</p>"), "default_player" => 4),     //get goods when others go to bazzar
  1 => array("type" => 1, "name" => clienttranslate("Kubilai Khan"), "description" => clienttranslate("<p>You place your figure in Beijing (not Venezia) at the start of the game. This means that your travels will start from there. Additionally, place 1 trading post onto the 10 victory point space in Beijing.</p>")),  //start in beijing
  2 => array("type" => 2, "name" => clienttranslate("Raschid ad-Din Sinan"), "description" => clienttranslate("<p>You do not roll your dice.  Whenever you choose an action, you choose the die values yourself. Note: You cannot receive compensation.</p>"), "default_player" => 1),   //make die any value
  3 => array("type" => 3, "name" => clienttranslate("Johannes Carprini"), "description" => clienttranslate('<p>You can move from one oasis on the map to another during movement.</p>Whenever you choose to travel, you are allowed to move from one oasis to another.</p><p>This counts as one move. You can start your travel on the oasis, or first move to an oasis from a city and then move to another oasis afterward. You are allowed to keep moving to another city afterward. You are only allowed to move if you have paid for the necessary spaces. You also receive 3 coins at the start of each round.</p>'), "award" => ["coin" => 3], "auto" => true),                             //teleport between oasis, 3 coins at beginning of round
  4 => array("type" => 4, "name" => clienttranslate("Wilhelm von Rubruk"), "description" => clienttranslate("<p>You receive 2 black trading posts at the start of the game. Add them to your other trading posts. If you manage to place all 11 of your trading posts by the end of the game, you score an additional 10 victory points. </p><p>You also place trading posts in any city, large or small, that you move through while traveling. This means that you do not need to end your movement in a city to place a trading post there. However, you will only receive the bonuses from trading posts, if any, after ending your movement.</p><p>Note: You are allowed to move back and forth over a city, even one that you have already visited. You may still only have 1 trading post in each city.</p>")),  //two extra trading, place trading posts without stopping
  5 => array("type" => 5, "name" => clienttranslate("Niccolo & Marco Polo"), "description" => clienttranslate("<p>You receive another figure with which to move across the map at the start of the game. Your second figure also starts in Venezia.</p><p>You also receive 1 camel at the start of each round.</p><p>Note: You are allowed to split your movement between the two figures when traveling. However, you must pay all travel and additional costs before moving both figures.</p>"), "award" => ["camel" => 1], "auto" => true),                          //two figures, camel at beginning of round
  6 => array("type" => 6, "name" => clienttranslate("Berke Khan"), "description" => clienttranslate("<p>You do not have to pay anything to use an occupied action space.</p><p>Note: This does not allow you to use occupied city cards.</p>"), "default_player" => 3),             //no need to pay coins to place die
  7 => array("type" => 7, "name" => clienttranslate("Matteo Polo"), "description" => clienttranslate("<p>You receive the white die at the start of each round. Roll it and add it to the remaining dice on your player board. You also receive the topmost contract in the special pile at the start of each round. Look at page 1 of this supplement for more information.</p>"), "default_player" => 2, "award" => ["contract" => 1]),            //white die & contract at beginning of round
  8 => array("type" => 8, "name" => clienttranslate("Fratre Nicolao"), "description" => clienttranslate("<p>At the beginning of each round, you draw 3 gifts. Choose 1 of these gifts to take and discard the other 2 faceup</p><p>You also receive this token, which allows you to instead take 2 of the 3 drawn gifts once during the game.</p>"), "award" => ["pick_gift" => 1], "expansion" => 0),
  9 => array("type" => 9, "name" => clienttranslate("Khan Arghun"), "description" => clienttranslate("<p>Once per turn, you may use 1 of these city cards.  This counts as a bonus action.</p><p>By using a card, you perform the action depicted on the city card as though you had placed a 6 value die on it. No die is required.</p><p>After you have completed the action on the city card, discard it.</p>"), "expansion" => 0),
  10 => array("type" => 10, "name" => clienttranslate("Altan Ord"), "description" => clienttranslate("<p>You receive a bonus each time you place a trading post. When you place your 1st trading post, you receive 1 point. When you place your 2nd trading post, you receive 1 point and 1 coin. When you place your 3rd trading post, you receive 1 point, 1 coin, and 1 camel, etc..</p>"), "expansion" => 0),
  11 => array(
    "type" => 11, "name" => clienttranslate("Gunj Kököchin"), "description" => clienttranslate("<p>You have 2 additional action spaces that only you may use. To use them, simply place 1 of your dice on one of the action spaces. This counts as your turn’s action.</p><p>This die will be retrieved and rolled at the beginning of the next round, as usual. This means that these action spaces are available to be used in each new round</p><p>1st action space: Take 2 camels and 2 goods of your choice. Each of your opponents take 1 good of their choice. These goods are taken from the supply, as usual</p><p>2nd action space: Move your figure 1 space on the map</p>"), "expansion" => 0,
    "places" => array(
      ["place" => "gunj", "index" => 0, "name" => "Gunj Kököchin goods", "description" => "", "owns_place" => true, "num_dice" => 1, "award" => ["camel" => 2, "choice_of_good" => 2], "allow_multiple" => false, "ui_location" => "gunj_0"],
      ["place" => "gunj", "index" => 1, "name" => "Gunj Kököchin travel", "description" => "", "owns_place" => true, "num_dice" => 1, "award" => ["travel" => 1], "allow_multiple" => false, "ui_location" => "gunj_1"]
    )
  ),
);

$this->all_material = array(
  "board" => $this->board_map,
  "spots" => $this->board_spots,
  "edges" => $this->board_edges,
  "contracts" => $this->contract_types,
  "outpost_bonuses" => $this->outpost_bonus_types,
  "city_bonuses" => $this->city_bonus_types,
  "city_cards" => $this->city_card_types,
  "character_types" => $this->character_types,
  "goal_card_types" => $this->goal_card_types,
);