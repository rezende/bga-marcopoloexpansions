<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * MarcoPoloExpansions game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.

    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")

    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean

    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.

    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress

    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players

*/

$stats_type = array(

    // Statistics global to table
    "table" => array(
        "winning_character" => array("id" => 10,
            "name" => totranslate("Winning Character"),
            "type" => "int" ),
    ),

    // Statistics existing for each player
    "player" => array(
        "player_character" => array("id"=> 10,
            "name" => totranslate("Character"),
            "type" => "int" ),

        "main_actions" => array("id"=> 11,
            "name" => totranslate("Number of main actions"),
            "type" => "int" ),

        "black_dice_aquired" => array("id"=> 12,
            "name" => totranslate("Number of black dice aquired"),
            "type" => "int" ),

        "die_rerolls" => array("id"=> 13,
            "name" => totranslate("Number of dice re-rolled"),
            "type" => "int" ),

        "die_bump" => array("id"=> 14,
            "name" => totranslate("Number of dice bumped"),
            "type" => "int" ),

        "total_dice" => array("id"=> 15,
            "name" => totranslate("Total number of dice placed"),
            "type" => "int" ),

        "total_dice_value" => array("id"=> 16,
            "name" => totranslate("Total value of dice placed"),
            "type" => "int" ),

        "avg_dice_value" => array("id"=> 17,
            "name" => totranslate("Average value of dice placed"),
            "type" => "float" ),

        "compensation_receive_camel" => array("id" => 33,
            "name" => totranslate("Camels received as dice compensation"),
            "type" => "int" ),

        "compensation_receive_coin" => array("id" => 34,
            "name" => totranslate("Coins received as dice compensation"),
            "type" => "int" ),

        "contracts_fulfilled" => array("id"=> 18,
            "name" => totranslate("Number of contracts fulfilled"),
            "type" => "int" ),

        "trading_posts" => array("id"=> 19,
            "name" => totranslate("Number of trading posts placed"),
            "type" => "int" ),

        "outpost_bonuses" => array("id"=> 20,
            "name" => totranslate("Number of outpost bonuses received"),
            "type" => "int" ),

        "travel_movements" => array("id"=> 22,
            "name" => totranslate("Number of travel movements"),
            "type" => "int" ),

        "contract_points" => array("id" => 29,
            "name" => totranslate("Number of points from contracts"),
            "type" => "int" ),

        "city_card_points" => array("id" => 30,
            "name" => totranslate("Number of points from city cards"),
            "type" => "int" ),

        "city_bonus_points" => array("id" => 31,
            "name" => totranslate("Number of points from city bonuses"),
            "type" => "int" ),

        "gift_points" => array("id" => 32,
            "name" => totranslate("Number of points from gifts"),
            "type" => "int" ),

        "trading_post_points" => array("id" => 32,
            "name" => totranslate("Number of points from placing trading posts"),
            "type" => "int" ),

        "beijing_points" => array("id"=> 23,
            "name" => totranslate("Number of points from Beijing"),
            "type" => "int" ),

        "resource_points" => array("id"=> 24,
            "name" => totranslate("Number of points from resources"),
            "type" => "int" ),

        "most_contract_points" => array("id"=> 25,
            "name" => totranslate("Number of points from most completed contracts"),
            "type" => "int" ),

        "goal_card_points" => array("id"=> 26,
            "name" => totranslate("Number of points from goal cards"),
            "type" => "int" ),

        "coin_points" => array("id"=> 27,
            "name" => totranslate("Number of points from coins"),
            "type" => "int" ),

        "total_points" => array("id"=> 28,
            "name" => totranslate("Total points"),
            "type" => "int" ),
    ),

    "value_labels" => array(
        10 => array(
            0 => clienttranslate( "Mercator ex Tabriz" ),
            1 => clienttranslate( "Kubilai Khan" ),
            2 => clienttranslate( "Raschid ad-Din Sinan" ),
            3 => clienttranslate( "Johannes Carprini" ),
            4 => clienttranslate( "Wilhelm von Rubruk" ),
            5 => clienttranslate( "Niccolo & Marco Polo" ),
            6 => clienttranslate( "Berke Khan" ),
            7 => clienttranslate( "Matteo Polo" ),
            8 => clienttranslate( "Fratre Nicolao" ),
            9 => clienttranslate( "Khan Arghun" ),
            10 => clienttranslate( "Altan Ord" ),
            11 => clienttranslate( "Gunj Kököchin" ),
            98 => clienttranslate( "Not set" ),
            99 => clienttranslate( "Tied" ),
        )
    )
);
