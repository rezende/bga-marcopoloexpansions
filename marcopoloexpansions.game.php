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
 * marcopoloexpansions.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */
require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

class MarcoPoloExpansions extends Table
{
    const EXPANSION_ID_NEW_CHARACTER = '0';


    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            "current_round" => 10,
            "main_player_id" => 11,
            "first_move_of_round" => 12,
            "performed_main_action" => 13,
            "black_die_bought" => 14,
            "can_undo" => 15,
            "last_bid_value" => 16,                 //possible future value for auction
            "can_arghun_use_city_card" => 17,
            "expert_variant" => 100,
            "the_new_charaters_expansion" => 101,
        ));

        $this->deck = self::getNew("module.common.deck");
        $this->deck->init("card");
        $this->deck->autoreshuffle = true;
        $this->deck->autoreshuffle_custom = array('deck' => 'discard', 'gift_deck' => 'gift_discard');
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "marcopoloexpansions";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, camel, coin) VALUES ";
        $player_coins = 7;
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "', 2, $player_coins)";
            $player_coins += 1;
        }
        $sql .= implode(',', $values);
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue('current_round', 0);
        self::setGameStateInitialValue('main_player_id', 0);
        self::setGameStateInitialValue('first_move_of_round', 1);
        self::setGameStateInitialValue('performed_main_action', 0);
        self::setGameStateInitialValue('black_die_bought', 0);
        self::setGameStateInitialValue('can_arghun_use_city_card', 1);
        self::setGameStateInitialValue('last_bid_value', 0);
        self::setGameStateInitialValue('can_undo', 0);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat('table', 'winning_character', 99);    // Init a table statistics
        self::initStat('player', 'player_character', 98);  // Init a player statistics (for all players)
        self::initStat('player', 'main_actions', 0);
        self::initStat('player', 'black_dice_aquired', 0);
        self::initStat('player', 'die_rerolls', 0);
        self::initStat('player', 'die_bump', 0);
        self::initStat('player', 'total_dice', 0);
        self::initStat('player', 'total_dice_value', 0);
        self::initStat('player', 'avg_dice_value', 0);
        self::initStat('player', 'contracts_fulfilled', 0);
        self::initStat('player', 'trading_posts', 0);
        self::initStat('player', 'outpost_bonuses', 0);
        self::initStat('player', 'travel_movements', 0);
        self::initStat('player', 'beijing_points', 0);
        self::initStat('player', 'resource_points', 0);
        self::initStat('player', 'most_contract_points', 0);
        self::initStat('player', 'goal_card_points', 0);
        self::initStat('player', 'coin_points', 0);
        self::initStat('player', 'total_points', 0);
        self::initStat('player', 'contract_points', 0);
        self::initStat('player', 'city_card_points', 0);
        self::initStat('player', 'city_bonus_points', 0);
        self::initStat('player', 'trading_post_points', 0);
        self::initStat('player', 'compensation_receive_coin', 0);
        self::initStat('player', 'compensation_receive_camel', 0);

        $this->createGoalCards();
        $this->createDealAndShuffleContracts($players);
        $this->createGifts();
        $this->createDefaultGamePieces($players);
        $this->assignCityBonuses();
        $this->assignCityCards();
        $this->assignOutpostBonuses();
        $this->placeNewContractsOutOnBoard(6, "round_" . 1);

        $this->activeNextPlayer();                                      // Activate first player (which is in general a good idea :) )
        $this->setHourGlass($this->getActivePlayerId());
        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        $player_sql = "SELECT player_id id, player_score score, vp, coin, camel, pepper, silk, gold, character_type, hourglass FROM player";
        $result['players'] = self::getCollectionFromDb($player_sql);
        $piece_sql = "SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg, piece_location_position location_position FROM piece WHERE piece_location <> 'box'";
        $result['pieces'] = self::getCollectionFromDB($piece_sql);
        $die_sql = "SELECT die_id id, die_type type, die_location location, die_location_arg location_arg, die_location_height location_height, die_player_id player_id, die_value value FROM die";
        $result['dice'] = self::getCollectionFromDB($die_sql);
        $result['contracts'] = $this->deck->getCardsInLocation("board");
        $result['player_contracts'] = $this->deck->getCardsInLocation("hand");
        $result['player_contracts_completed'] = self::getCollectionFromDB("SELECT card_location_arg location_arg, COUNT(card_id) num_of FROM card WHERE card_location = 'complete' GROUP BY card_location_arg");
        $result['player_gifts'] = $this->deck->getCardsInLocation("gift_hand");
        $result['expansions'] = $this->getExpansionsEnabled();
        if ($this->gamestate->state() && $this->gamestate->state()["name"] == "gameEnd") {
            $result['goal_cards'] = $this->deck->getCardsInLocation("goal_hand"); //replay bug?
        } else {
            $result['goal_cards'] = $this->deck->getCardsInLocation("goal_hand", $current_player_id);
        }

        $result['material'] = $this->all_material;
        $result['current_round'] = self::getGameStateValue("current_round");

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $total_dice = self::getUniqueValueFromDB("SELECT count(die_id) FROM die WHERE die_type <> 'fixed'");
        $unplayed_dice = self::getUniqueValueFromDB("SELECT count(die_id) FROM die WHERE die_location = 'player_mat' OR ( die_type = 'black' AND die_location = 'board' )");

        if (self::getGameStateValue("first_move_of_round") == 1) {
            $total_dice = 1;
            $unplayed_dice = 1;
        }

        return max(0, self::getGameStateValue("current_round") - 1) * 20 + ((($total_dice - $unplayed_dice) / $total_dice) * 20);
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function setNewUndoPoint()
    {
        if ($this->gamestate->state()["type"] != "multipleactiveplayer") {
            $this->undoSavepoint();
            self::setGameStateValue("can_undo", 0);
        }
    }

    function isExpansionEnabled($expansion)
    {
        if ($expansion == self::EXPANSION_ID_NEW_CHARACTER)      //new characters
        {
            return self::getGameStateValue("the_new_charaters_expansion") == 1;
        }
        return false;
    }

    function getExpansionsEnabled()
    {
        $expansions = [];
        if ($this->isExpansionEnabled(self::EXPANSION_ID_NEW_CHARACTER))       //new characters
        {
            array_push($expansions, self::EXPANSION_ID_NEW_CHARACTER);
        }
        return $expansions;
    }

    function isOnlyRemainingPlayer($player_id)
    {
        $player_ids_with_dice_left = self::getObjectListFromDB("SELECT DISTINCT die_player_id FROM die WHERE die_location = 'player_mat'");
        $player_ids_with_dice_left = array_map(function ($p) {
            return $p["die_player_id"];
        }, $player_ids_with_dice_left);        //map to static list of player ids
        return sizeof($player_ids_with_dice_left) == 1 && in_array($player_id, $player_ids_with_dice_left);
    }

    function getLastPlayerId()
    {
        $player_order = self::getNextPlayerTable();
        $last_player_id = null;
        $next_player_id = $player_order[0];
        do {
            $last_player_id = $next_player_id;
            {
                $next_player_id = $player_order[$next_player_id];
            }
        } while ($next_player_id != $player_order[0]);
        return $last_player_id;
    }

    function getRequestingPlayerId()
    {
        $player_id = self::getActivePlayerId();
        if ($this->gamestate->state()["name"] == "playerBonus" || $this->gamestate->state()["name"] == "playerGunjBonus") {
            $player_id = self::getCurrentPlayerId();
        }
        return $player_id;
    }

    function setHourGlass($player_id)
    {
        self::DbQuery("UPDATE player SET hourglass = 0");
        self::DbQuery("UPDATE player SET hourglass = 1 WHERE player_id = {$player_id}");
        self::notifyAllPlayers("moveHourGlass", '', array("player_id" => $player_id));
    }

    function onlyGetExpCharacters($material_type)
    {
        $include = false;
        $expansions = $this->getExpansionsEnabled();
        if (array_key_exists("expansion", $material_type)) {
            $include = in_array($material_type["expansion"], $expansions);
        }
        return $include;
    }

    function filterExpansionFromMaterialTypes($material_type)
    {
        $include = false;
        $expansions = $this->getExpansionsEnabled();
        if (array_key_exists("expansion", $material_type)) {
            $include = in_array($material_type["expansion"], $expansions);
        } else {
            $include = true;
        }
        return $include;
    }

    function createGoalCards()
    {
        $goal_cards = array();
        foreach ($this->goal_card_types as $goal_card_id => $goal_card_type) {
            $goal_cards[] = array('type' => $goal_card_id, 'type_arg' => $goal_card_id, 'nbr' => 1);
        }
        $this->deck->createCards($goal_cards, 'goal_deck');
        $this->deck->shuffle('goal_deck');
    }

    function dealGoalCards($players, $num_of)
    {
        self::notifyAllPlayers("message", clienttranslate('Each player receives ${num_of} goal cards'), array("num_of" => $num_of));
        foreach ($players as $player_id => $player) {
            $cards = $this->deck->pickCardsForLocation($num_of, 'goal_deck', 'goal_hand', $player_id);
            self::notifyPlayer($player_id, "goalCard", '', array("cards" => $cards));
        }
    }

    function createDealAndShuffleContracts($players)
    {
        $valid_contract_types = array_filter($this->contract_types, array("MarcoPoloExpansions", "filterExpansionFromMaterialTypes"));
        $create_contract_callback = function ($c) {
            return ['type' => $c["type"], 'type_arg' => $c["type"], 'nbr' => 1];
        };
        $starter_contracts = array_map($create_contract_callback, array_filter($valid_contract_types, function ($c) {
            return $c["starter"] == true;
        }));
        $remaining_contracts = array_map($create_contract_callback, array_filter($valid_contract_types, function ($c) {
            return $c["starter"] == false;
        }));
        $this->deck->createCards($starter_contracts, 'deck');
        $this->deck->shuffle('deck');
        foreach ($players as $player_id => $player) {
            $this->deck->pickCard('deck', $player_id);
        }
        $this->deck->moveAllCardsInLocation('deck', 'box'); //move remaining starter contracts to the box
        $this->deck->createCards($remaining_contracts, 'deck');
        $this->deck->shuffle('deck');
    }

    function createGifts()
    {
        if ($this->isExpansionEnabled(self::EXPANSION_ID_NEW_CHARACTER)) {
            $create_gift_callback = function ($g) {
                return ['type' => 'gift', 'type_arg' => $g["type"], 'nbr' => 1];
            };
            $gifts = array_map($create_gift_callback, $this->gift_types);
            $this->deck->createCards($gifts, 'gift_deck');
        }
        $this->deck->shuffle('gift_deck');
    }

    function placeNewContractsOutOnBoard($num_of, $trigger_by)
    {
        $this->deck->moveAllCardsInLocation("board", "discard");
        $this->deck->pickCardsForLocation($num_of, "deck", "board");
        $this->slideRemainingContracts($trigger_by);
    }

    function createDefaultGamePieces($players)
    {
        $piece_values = array();
        $die_values = array();
        $sql = "INSERT INTO piece (piece_type, piece_type_arg, piece_player_id, piece_location, piece_location_arg) VALUES";
        $die_sql = "INSERT INTO die (die_type, die_value, die_location, die_location_arg, die_player_id) VALUES";

        foreach ($players as $player_id => $player) {
            for ($i = 0; $i < 9; $i++)        //9 trading_posts
            {
                $piece_values[] = "('trading_post', 'regular', '{$player_id}', 'player_mat', '{$i}')";
            }
            for ($j = 0; $j < 5; $j++) {
                $die_values[] = "('regular', 1, 'player_mat', '', '{$player_id}')";
            }
            $piece_values[] = "('figure', 'regular', '{$player_id}', 'board', '0')";           //0 = Venezia (start figure here)
        }
        for ($k = 0; $k < sizeof($players) + 1; $k++) {
            $die_values[] = "('black', 1, 'avail_black_die', 0, NULL)";
        }
        for ($l = 0; $l < 4 - sizeof($players); $l++) {
            $die_values[] = "('fixed', 1, 'khan', $l, NULL)";
        }

        if (sizeof($players) == 2) {
            $die_values[] = "('fixed', 1, 'coin5', 0, NULL)";
        }

        $sql .= implode(',', $piece_values);
        $die_sql .= implode(',', $die_values);
        self::DbQuery($sql);
        self::DbQuery($die_sql);
    }

    function randomlyAssignBonusPieces($piece_type, $bonus_type_materials, $matching_map_type)
    {
        shuffle($bonus_type_materials);
        $bonus_index = 0;
        $piece_values = [];
        $sql = "INSERT INTO piece (piece_type, piece_type_arg, piece_location, piece_location_arg, piece_location_position) VALUES";

        foreach ($this->board_map as $map_node_id => $map_node) {
            if ($map_node["type"] == $matching_map_type) {
                $num_of = 1; //number_of_cards_to_place_in_city
                // random city cards on board
                if ($piece_type == "city_card" && array_key_exists("num_cards", $map_node)) { //Sumatra
                    $num_of = $map_node["num_cards"];
                }
                for ($i = 0; $i < $num_of; $i++) { //this loop is basically for sumatra
                    $bonus_type = $bonus_type_materials[$bonus_index]["type"];
                    $piece_values[] = "('{$piece_type}', '{$bonus_type}', 'board', '{$map_node_id}', '{$i}')";
                    $bonus_index += 1;
                }
            }
        }
        $sql .= implode(',', $piece_values);
        self::DbQuery($sql);
    }

    // TODO: Make sure trigger other cities is always selected
    function assignCityBonuses()
    {
        $valid_city_bonus_types = array_filter($this->city_bonus_types, array("MarcoPoloExpansions", "filterExpansionFromMaterialTypes"));
        if (self::getGameStateValue("expert_variant") == 1) //use random
        {
            $this->randomlyAssignBonusPieces('city_bonus', $valid_city_bonus_types, "small_city");
        } else {
            $sql = "INSERT INTO piece (piece_type, piece_type_arg, piece_location, piece_location_arg) VALUES";
            $piece_values = [];
            foreach ($valid_city_bonus_types as $city_bonus_type) {
                $city_type = $city_bonus_type["type"];
                $map_node_id = $city_bonus_type["default_location"];
                $piece_values[] = "('city_bonus', '{$city_type}', 'board', '{$map_node_id}')";
            }
            $sql .= implode(',', $piece_values);
            self::DbQuery($sql);
        }
    }

    function assignCityCards()
    {
        $this->randomlyAssignBonusPieces('city_card', $this->city_card_types, "large_city");      //TODO valid city card in other expansion
    }

    function assignOutpostBonuses()
    {
        $this->randomlyAssignBonusPieces('outpost', $this->outpost_bonus_types, "large_city");
    }

    function presetupCharacter($character_type)
    {
        if ($character_type == 9)     //Khan Arghun
        {
            $current_count = 0;
            $shuffled_card_index = 0;
            $current_city_cards = self::getObjectListFromDB("SELECT piece_type_arg FROM piece WHERE piece_location = 'board' AND piece_type = 'city_card'");
            $current_city_cards = array_map(function ($c) {
                return $c["piece_type_arg"];
            }, $current_city_cards);
            $shuffled_city_cards = $this->city_card_types;
            shuffle($shuffled_city_cards);
            while ($current_count < 6) {
                $shuffled_city_type = $shuffled_city_cards[$shuffled_card_index]["type"];
                if (in_array($shuffled_city_type, $current_city_cards) == false) { //if the random card is not a selected city card already
                    $current_count += 1;
                    /* this is to make the pieces show on the player character select screen  */
                    self::DbQuery("INSERT INTO piece (piece_type, piece_type_arg, piece_location) VALUES ('city_card', '{$shuffled_city_type}', 'pick_character')");
                }
                $shuffled_card_index += 1;
            }
        }
    }

    function setupCharacter($character_type, $player_id)
    {
        self::DbQuery("UPDATE player SET character_type = {$character_type} WHERE player_id = {$player_id}");
        if ($character_type == 1)             //1 = khan
        {
            $piece_id = self::getUniqueValueFromDB("SELECT piece_id FROM piece WHERE piece_type = 'figure' AND piece_player_id = {$player_id}");
            self::DbQuery("UPDATE piece SET piece_location_arg = '8' WHERE piece_id = {$piece_id}");
            $this->addToPendingTable("travel", "kubilai_khan", "", 1, "", $player_id);        //create "fake" pending action
            $pending_action = $this->getNextPendingAction($player_id);
            $this->placeTradingPostAndGiveAward(8, $pending_action, $player_id);
            $this->deletePendingAction($pending_action["pending_id"]);
            self::notifyAllPlayers("travel", "", array("figure_id" => $piece_id, "dst_id" => 8));
        } else if ($character_type == 4)        //4 = Withelm
        {
            self::DbQuery("INSERT INTO piece (piece_type, piece_type_arg, piece_player_id, piece_location, piece_location_arg) VALUES ('trading_post', '1', '{$player_id}', 'player_mat', '9')");
            self::DbQuery("INSERT INTO piece (piece_type, piece_type_arg, piece_player_id, piece_location, piece_location_arg) VALUES ('trading_post', '1', '{$player_id}', 'player_mat', '10')");
            $pieces = self::getObjectListFromDB("SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg
                FROM piece WHERE piece_type = 'trading_post' AND piece_type_arg = '1' AND piece_player_id = {$player_id}");
            self::notifyAllPlayers("characterUpdate", "", array("new_type" => "trading_post", "data" => $pieces, "player_id" => $player_id));
        } else if ($character_type == 5)        //5 = Niccolo
        {
            self::DbQuery("INSERT INTO piece (piece_type, piece_type_arg, piece_player_id, piece_location, piece_location_arg) VALUES ('figure', '1', '{$player_id}', 'board', '0')");           //0 = Venezia
            $piece = self::getObjectFromDB("SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg
                        FROM piece WHERE piece_type = 'figure' AND piece_type_arg = '1' AND piece_player_id = {$player_id}");
            self::notifyAllPlayers("characterUpdate", "", array("new_type" => "figure", "data" => [$piece], "player_id" => $player_id));
        } else if ($character_type == 7)       //7 = Matteo Polo
        {
            self::DbQuery("INSERT INTO die(die_type, die_value, die_location, die_player_id) VALUES ('white', 1, 'player_mat', '{$player_id}')");     //create white die
            $die = self::getObjectFromDB("SELECT die_id id, die_type type, die_location location, die_location_arg location_arg, die_location_height location_height, die_player_id player_id, die_value value FROM die WHERE die_type = 'white'");
            self::notifyAllPlayers("characterUpdate", "", array("new_type" => "die", "data" => [$die], "player_id" => $player_id));
        } else if ($character_type == 8)        //8 = Fratre Nicolao
        {
            self::DbQuery("INSERT INTO piece (piece_type, piece_type_arg, piece_player_id, piece_location, piece_location_arg) VALUES ('1x_gift', '0', '${player_id}', 'player_mat', '0')");
            $piece = self::getObjectFromDB("SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg
                FROM piece WHERE piece_type = '1x_gift' AND piece_type_arg = '0' AND piece_player_id = {$player_id}");
            self::notifyAllPlayers("characterUpdate", "", array("new_type" => "1x_gift", "data" => [$piece], "player_id" => $player_id));
        } else if ($character_type == 9)       //9 = Khan Arghun
        {
            /* move cards from player select to the 'player_mat' of the player playing Arghun */
            self::DbQuery("UPDATE piece SET piece_player_id = '${player_id}', piece_location = 'player_mat' WHERE piece_type = 'city_card' AND piece_location = 'pick_character'");
            $city_cards = self::getObjectListFromDB("SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg
                FROM piece WHERE piece_type = 'city_card' AND piece_location = 'player_mat' AND piece_player_id = {$player_id}");
            self::notifyAllPlayers("characterUpdate", "", array("new_type" => "city_card", "data" => $city_cards, "player_id" => $player_id));
        }
        self::setStat($character_type, "player_character", $player_id);
    }

    function getPlayerResources($player_id)
    {
        return self::getObjectFromDB("SELECT coin, camel, pepper, silk, gold, vp FROM player WHERE player_id = {$player_id}");
    }

    function getPlayerName($player_id)
    {
        return self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = {$player_id}");
    }

    function getPlayerIdByCharacterType($character_type)
    {
        return self::getUniqueValueFromDB("SELECT player_id FROM player WHERE character_type = {$character_type}");
    }

    // ui_location: serves to animate where resources come from / go to
    function changePlayerResources($resource_changes, $negate, $ui_location, $player_id)
    {
        $sql_changes = [];
        $resources_args = [];
        $resources_string = [];
        $valid_resource_changes = [];
        $i = 0;

        foreach ($resource_changes as $resource_type => $amount) {
            $valid_resources = ["coin", "camel", "pepper", "silk", "gold", "vp"];
            $sql_amount = " + ";
            if (in_array($resource_type, $valid_resources)) {
                $valid_resource_changes[$resource_type] = $amount;
                if ($negate)
                    $sql_amount = " - ";

                $sql_changes[] = $resource_type . " = " . $resource_type . $sql_amount . $amount;
                $resources_string[] = 'resource' . $i;
                $resources_args["resource" . $i] = ["log" => '${num_of} ${resource_type}', "args" => array("num_of" => $amount, "resource_type" => $resource_type)];
                $i += 1;
            }
            if ($resource_type == "vp") {
                $sql_changes[] = "player_score = player_score " . $sql_amount . $amount;
            }
        }

        if (sizeof($sql_changes) > 0 && sizeof($resources_string) > 0) {
            $sql_changes = join(",", $sql_changes);
            self::DbQuery("UPDATE player SET {$sql_changes} WHERE player_id = {$player_id}");
            $absolute_resources = self::getObjectFromDB("SELECT " . implode(",", $valid_resources) . " FROM player WHERE player_id = {$player_id}");
            $resource_break_down = ["log" => '${' . join('}, ${', $resources_string) . '}', "args" => $resources_args];
            $message = $negate ? clienttranslate('${player_name} pays ${resources}') : clienttranslate('${player_name} receives ${resources}');
            self::notifyAllPlayers("resourceChange", $message, array("player_id" => $player_id, "player_name" => $this->getPlayerName($player_id), "negate" => $negate,
                "location" => $ui_location, "resource_changes" => $valid_resource_changes, "resources" => $resource_break_down, "absolute_resources" => $absolute_resources));
        }
    }

    function cleanUpDice()
    {
        self::DbQuery("UPDATE die SET die_player_id = NULL, die_location = 'avail_black_die', die_location_arg = 0, die_location_height = 0, die_value = 1 WHERE die_type = 'black'");
        self::DbQuery("UPDATE die SET die_location = 'player_mat', die_location_arg = '', die_location_height = 0, die_value = 1 WHERE die_type = 'regular' OR die_type = 'white'");
        $dice = self::getCollectionFromDB("SELECT die_id, die_type, die_player_id, die_location, die_location_arg, die_location_height, die_value FROM die");
        self::notifyAllPlayers("updateDice", "", array('dice' => $dice, "shake" => false));
    }

    function rollDie($die_id, $player_id)
    {
        $die_value = bga_rand(1, 6);
        if ($this->getPlayerIdByCharacterType(2) == $player_id) {
            $die_value = 1;
        }
        self::DbQuery("UPDATE die SET die_value = {$die_value} WHERE die_id = {$die_id}");
        return $die_value;
    }

    function getPlayerIdsOnDiePlace($place, $index, $regular_only)        //returns array of playerids on the current place order by dice_height
    {
        $die_type_clause = $regular_only ? " AND die_type = 'regular'" : "";
        $result = self::getObjectListFromDB("SELECT die_player_id FROM die WHERE die_location = '{$place}' AND die_location_arg = '{$index}' {$die_type_clause} ORDER BY die_location_height");
        return array_map(function ($d) {
            return $d["die_player_id"];
        }, $result);
    }

    function getNextDiceHeight($place, $index)
    {
        $result = self::getUniqueValueFromDB("SELECT max(die_location_height) FROM die WHERE die_location = '{$place}' AND die_location_arg = '{$index}'");
        return $result == null ? 0 : $result + 1;
    }

    function getCurrentDiceValueOnPlace($place, $index)
    {
        $height = $this->getNextDiceHeight($place, $index) - 1;
        return self::getUniqueValueFromDB("SELECT min(die_value) FROM die WHERE die_location = '{$place}' AND die_location_arg = '{$index}' AND die_location_height = '{$height}'");
    }

    function getNextAvailableBlackDie()
    {
        return self::getUniqueValueFromDB("SELECT die_id FROM die WHERE die_type = 'black' AND die_location = 'avail_black_die' ORDER BY die_id DESC LIMIT 1");
    }

    function moveDice($dice_ids, $location, $location_arg, $location_height, $player_id)
    {
        $dice_info = [];
        foreach ($dice_ids as $die_id) {
            self::DbQuery("UPDATE die SET die_location = '{$location}', die_location_arg = '{$location_arg}', die_location_height = {$location_height} WHERE die_id = {$die_id}");
            $dice_info[] = ["die_id" => $die_id, "die_location" => $location, "die_location_arg" => $location_arg, "die_location_height" => $location_height, "die_player_id" => $player_id];
        }
        return $dice_info;
    }

    function getDiceByIds($dice_ids)
    {
        if (sizeof($dice_ids) > 0) {
            $dice_ids = implode(" , ", $dice_ids);
            return self::getCollectionFromDb("SELECT die_id, die_value, die_type, die_location, die_location_arg, die_location_height, die_player_id FROM die WHERE die_id IN ( {$dice_ids} )");
        }
        return [];
    }

    function givePlayerBlackDie($die_id, $via_buy, $player_id)
    {
        $die_value = $this->rollDie($die_id, $player_id);
        self::DbQuery("UPDATE die SET die_location = 'player_mat', die_player_id = '{$player_id}' WHERE die_id = {$die_id}");
        $dice = $this->getDiceByIds([$die_id]);
        $shake = $player_id == $this->getPlayerIdByCharacterType(2) ? false : true;
        $message = clienttranslate('${player_name} gets a black die and rolls a ${die_value}');
        if ($shake && $via_buy) {
            $message = clienttranslate('${player_name} buys a black die and rolls a ${die_value}');
        } else if ($shake == false && $via_buy) {
            $message = clienttranslate('${player_name} buys a black die');
        } else if ($shake == false) {
            $message = clienttranslate('${player_name} gets a black die');
        }
        self::notifyAllPlayers("updateDice", $message, array("player_id" => $player_id, "player_name" => $this->getPlayerName($player_id), "dice" => $dice, "die_value" => $die_value, "shake" => $shake));
        self::incStat(1, "black_dice_aquired", $player_id);
        if ($shake) {
            $this->setNewUndoPoint();
        } else {
            self::setGameStateValue("can_undo", 1);
        }
    }

    function getNumberOfTradingPostsPlaced($player_id)
    {
        return self::getUniqueValueFromDB("SELECT COUNT(piece_id) FROM piece WHERE piece_location ='board' AND piece_type ='trading_post' AND piece_player_id = '{$player_id}'");
    }

    function getNumberOfTimesCostSatisfied($costs, $player_id)
    {
        $num_times = 99;
        $current_resources = $this->getPlayerResources($player_id);
        foreach ($costs as $cost_type => $cost_amount) {
            $current_times = 99;
            if ($cost_type == "fulfilled_contracts") {
                $current_times = floor($this->deck->countCardInLocation("complete", $player_id) / $cost_amount);
            } else if ($cost_type == "placed_trading_post") {
                $current_times = floor($this->getNumberOfTradingPostsPlaced($player_id) / $cost_amount);
            } else if ($cost_type == "2_diff_goods")        //only need to check if at least 1 since many combinations
            {
                $sub_total = $current_resources["pepper"] > 0 ? 1 : 0;
                $sub_total += $current_resources["silk"] > 0 ? 1 : 0;
                $sub_total += $current_resources["gold"] > 0 ? 1 : 0;
                $current_times = $sub_total > 1 ? 1 : 0;
            } else if ($cost_type == "choice_of_good") {
                $current_times = $current_resources["pepper"] + $current_resources["silk"] + $current_resources["gold"];
            } else if ($cost_type == "vp")      //let vp go negative
            {
                $current_times = $num_times;
            } else {
                $current_times = floor($current_resources[$cost_type] / $cost_amount);
            }

            $num_times = min($num_times, $current_times);
        }
        return $num_times;
    }

    function giveCityAward($city_card_info, $dice, $player_id)
    {
        $awards = [];
        $die_value = array_shift($dice)["die_value"];
        $location = "city_card_" . $city_card_info["type"];
        if ($city_card_info["kind"] == "die" && $city_card_info["auto"] == true) {
            foreach ($city_card_info["award"] as $reward_type => $reward_amount) {
                $awards[$reward_type] = $reward_amount[$die_value - 1];
            }
            $this->giveAward($location, $awards, $player_id);
        } else if ($city_card_info["kind"] == "multiple") {
            $num_times = min($die_value, $this->getNumberOfTimesCostSatisfied($city_card_info["cost"], $player_id));

            if ($city_card_info["auto"] == true) {
                foreach ($city_card_info["award"] as $reward_type => $reward_amount) {
                    $awards[$reward_type] = $reward_amount * $num_times;
                }
                $this->giveAward($location, $awards, $player_id);
            } else {
                if ($city_card_info["type"] == 30)     //travel one add pending travel
                {
                    $this->addToPendingTable("travel", "", "", 0, $location, $player_id);
                    $num_times = $die_value;
                } else if ($city_card_info["type"] == 25)   //trigger_city_bonus_having_trading_post limit num times to # of different trading posts
                {
                    $num_times = min(sizeof($this->getCityBonuses($player_id)), $num_times);
                }

                $this->addToPendingTable("city_card", $city_card_info["type"], "", $num_times, $location, $player_id);
            }
        } else if ($city_card_info["kind"] == "exchange" || $city_card_info["kind"] == "choice") {
            $this->addToPendingTable("city_card", $city_card_info["type"], "", $die_value, $location, $player_id);
        }
    }

    function isAutoCostAward($cost_awards)    //determine if the awards/cost can be performed automatically or require player decision
    {
        $result = true;
        foreach ($cost_awards as $cost_awards_type => $amount) {
            $result &= !in_array($cost_awards_type, ["choice_of_good", "camel_coin", "2_diff_goods", "travel", "trigger_other_city_bonus"]);
        }
        return $result;
    }

    function getPlayerCurrentContractIdsString($player_id)
    {
        $valid_contract_ids = $this->deck->getCardsInLocation("hand", $player_id);
        $valid_contract_ids = implode("_", array_map(function ($c) {
            return $c["id"];
        }, $valid_contract_ids));
        return $valid_contract_ids;
    }

    function checkAndTriggerFulfillContract($pending_action, $player_id)
    {
        if ($pending_action["remaining_count"] == 1 && strpos($pending_action["location"], "contract_") === 0)       //trigger contract fulfill now
        {
            $contract_id = str_replace("contract_", "", $pending_action["location"]);
            self::notifyAllPlayers("fulfillContract", '', array("player_id" => $player_id, "contract_id" => $contract_id, "resources_awarded" => true));
        }
    }

    function checkAndTriggerFulfillArghun($pending_action, $player_id)
    {
        if ($pending_action["remaining_count"] == 1 && strpos($pending_action["location"], "city_card_") === 0)       //trigger arghun fulfil now
        {
            $contract_id = str_replace("city_card_", "", $pending_action["location"]);
            self::notifyAllPlayers("fulfillArghun", '', array("player_id" => $player_id, "city_card_id" => $contract_id, "resources_awarded" => true));
        }
    }

    function checkAndTriggerDiscardGift($pending_action, $player_id)
    {
        if ($pending_action["remaining_count"] == 1 && strpos($pending_action["location"], "gift_") === 0) {
            $gift_id = str_replace("gift_", "", $pending_action["location"]);
            $this->discardGift($gift_id, true, $player_id);
        }
    }

    function awardGift($gift_id, $gift_data, $player_id)
    {
        $award = $gift_data["award"];
        if (array_key_exists("cost", $gift_data))       //vp award driven by # of times "cost" satisfied
        {
            $num_times = $this->getNumberOfTimesCostSatisfied($gift_data["cost"], $player_id);
            $num_times = min($num_times, $gift_data["max_times"]);
            $award["vp"] = $award["vp"] * $num_times;
        }
        if (array_key_exists("others_award", $gift_data)) {
            $other_players = self::loadPlayersBasicInfos();
            foreach ($other_players as $other_player_id => $other_player) {
                if ($player_id != $other_player_id) {
                    $this->giveAward("gift_" . $gift_id, $gift_data["others_award"], $other_player_id);
                }
            }
        }
        $this->giveAward("gift_" . $gift_id, $award, $player_id);
    }

    function triggerImmediateGifts($player_id)
    {
        $gifts = $this->deck->getCardsInLocation("gift_hand", $player_id);
        foreach ($gifts as $gift) {
            $gift_data = $this->gift_types[$gift["type_arg"]];
            if (array_key_exists("auto", $gift_data) && $gift_data["auto"]) {
                $this->useGift($gift["id"], false, $player_id);
                $this->awardGift($gift["id"], $gift_data, $player_id);
                $pending_action = $this->getNextPendingAction($player_id);
                if ($pending_action == null || strpos($pending_action["location"], "gift_") !== 0) {
                    $this->discardGift($gift["id"], true, $player_id);
                }
            }
        }
    }

    function drawContract($amount, $player_id)
    {
        $contracts = $this->deck->pickCards($amount, "deck", $player_id);
        if (sizeof($this->deck->getCardsInLocation("hand", $player_id)) > 2) {
            $num_to_drop = sizeof($this->deck->getCardsInLocation("hand", $player_id)) - 2;
            $this->addToPendingTable("discard_contract", "", "", $num_to_drop, "", $player_id);
        }

        foreach ($contracts as $contract) {
            self::notifyAllPlayers("contract", clienttranslate('${player_name} draws a contract'), array("player_id" => $player_id,
                    "player_name" => $this->getPlayerName($player_id), "is_new" => true, "contract_id" => $contract["id"], "contract_data" => $contract)
            );
        }
        $this->setNewUndoPoint();
    }

    function drawGifts($amount, $player_id)
    {
        $gift_count = $this->deck->countCardInLocation("gift_discard") + $this->deck->countCardInLocation("gift_deck");
        if ($amount > $gift_count) {
            $amount = $gift_count;
        }
        $final_gifts = [];
        $gifts = $this->deck->pickCardsForLocation($amount, "gift_deck", "gift_hand", $player_id);

        foreach ($gifts as $gift) {
            $gift_data = $this->gift_types[$gift["type_arg"]];
            if (array_key_exists("invalid_character_type", $gift_data) && $this->getPlayerIdByCharacterType($gift_data["invalid_character_type"]) == $player_id) {
                if ($gift_count > $amount + 1) {
                    $this->deck->moveCard($gift["id"], "gift_discard");
                    $new_gift = $this->deck->pickCard("gift_deck", "gift_hand", $player_id);
                    array_push($final_gifts, $new_gift);
                }
            } else {
                array_push($final_gifts, $gift);
            }
        }

        $message = clienttranslate('${player_name} draws a gift');
        if (sizeof($final_gifts) > 1) {
            $message = clienttranslate('${player_name} draws ${amount} gifts');
        }
        self::notifyAllPlayers("gift", $message, array("player_id" => $player_id, "player_name" => $this->getPlayerName($player_id),
                "amount" => $amount, "gifts_data" => $final_gifts)
        );

        $this->setNewUndoPoint();
        return $final_gifts;
    }

    function giveGunjGoodAward($place_info, $player_id)
    {
        if ($place_info["place"] == "gunj" && $place_info["index"] == 0 && $this->getPlayerIdByCharacterType(11) == $player_id) {
            $players = self::loadPlayersBasicInfos();
            $this->addToPendingTable("switch_to_gunj_bonus", "gunj", "", 1, "gunj", $player_id);
            foreach ($players as $other_player_id => $player) {
                if ($player_id != $other_player_id) {
                    $this->addToPendingTable("choice_of_good", "", "", 1, "gunj_0", $other_player_id);
                }
            }
        }
    }

    /* Function that gives the player bonuses that are not resources */
    function giveAward($location, $awards, $player_id)
    {
        foreach ($awards as $award_type => $amount) {
            if ($award_type == "contract") {
                $this->drawContract($amount, $player_id);
            } else if ($award_type == "black_die") {
                $black_die_id = $this->getNextAvailableBlackDie();
                if ($black_die_id != null) {
                    $this->givePlayerBlackDie($black_die_id, false, $player_id);
                }
            } else if ($award_type == "trigger_city_bonus_having_trading_post" && sizeof($this->getCityBonuses($player_id)) > 0) {
                $amount = min($amount, sizeof($this->getCityBonuses($player_id)));
                $this->addToPendingTable($award_type, '', '', $amount, $location, $player_id);
            } else if ($award_type == "gift") {
                $this->drawGifts($amount, $player_id);
                $this->triggerImmediateGifts($player_id);
            } else if ($award_type == "pick_gift")      //get 3 gifts, pick 1 (Fratre Nicolao award)
            {
                $gift_ids = $this->drawGifts(3, $player_id);
                $gift_ids = implode("_", array_map(function ($g) {
                    return $g["id"];
                }, $gift_ids));
                $this->addToPendingTable("discard_gift", $gift_ids, "", 2, "", $player_id);
            } else if ($award_type == "pick_contract") {
                $die_value = $this->getCurrentDiceValueOnPlace("contracts", 0);
                $amount = min($die_value, $amount);       //die_value = 1 only can pick one
                $this->addToPendingTable($award_type, $this->getPlayerCurrentContractIdsString($player_id), '', $amount, $location, $player_id);
            } else if ($award_type == "trigger_other_city_bonus") {
                $this->addToPendingTable($award_type, 3, '', $amount, $location, $player_id);     //3 = this city bonus, can't trigger self
            } else if ($award_type == "blackdie_or_3coins") {
                $no_black_die = $this->getNextAvailableBlackDie() == null ? "no_black_die" : "";
                $this->addToPendingTable("blackdie_or_3coins", $no_black_die, '', 1, $location, $player_id);
            } else if (in_array($award_type, ["choice_of_good", "camel_coin", "2_diff_goods", "travel"])) {
                $this->addToPendingTable($award_type, '', '', $amount, $location, $player_id);
            }
        }

        if (strpos($location, "city_card_") === 0 && array_key_exists("vp", $awards)) {
            self::incStat($awards["vp"], "city_card_points", $player_id);
        } else if (strpos($location, "contract_") === 0 && array_key_exists("vp", $awards)) {
            self::incStat($awards["vp"], "contract_points", $player_id);
        }
        // else if ( strpos( $location, "gift_") === 0 && array_key_exists( "vp", $awards ) )           //TODO - GIFT stats
        // {
        //     self::incStat( $awards["vp"], "gift_points", $player_id );
        // }

        $this->changePlayerResources($awards, false, $location, $player_id);
    }

    function giveMercatorBonus($place_info, $player_id)
    {
        $mercator_player_id = $this->getPlayerIdByCharacterType(0);      //mercator
        if ($mercator_player_id != null && $player_id != $mercator_player_id) {
            $resources = [];
            $player_num = sizeof(self::loadPlayersBasicInfos());
            if ($place_info["place"] == "bazaar") {
                reset($place_info["award"]);
                $resource_type = key($place_info["award"]);
                $resources[$resource_type] = 1;
            } else if ($place_info["place"] == "khan" && $player_num <= 3) {
                $resources["camel"] = 1;
            } else if ($place_info["place"] == "coin5" && $player_num == 2) {
                $resources["coin"] = 2;
            }

            if (sizeof($resources) > 0) {
                self::notifyAllPlayers("message", clienttranslate('${player_name} is Mercator ex Tabriz and collects resources'), array("player_name" => $this->getPlayerName($mercator_player_id)));
                $this->changePlayerResources($resources, false, $place_info["ui_location"], $mercator_player_id);
            }
        }
    }

    function hasTradingPostOnBoardSpot($board_id, $player_id)
    {
        $posts = self::getCollectionFromDb("SELECT piece_id FROM piece WHERE piece_type = 'trading_post' AND piece_location = 'board' AND piece_location_arg = {$board_id} AND piece_player_id = {$player_id}");
        return $posts != null;
    }

    function getNextTradingPostByPlayerId($player_id)
    {
        return self::getObjectFromDB("SELECT piece_id id, piece_location location, piece_location_arg location_arg FROM piece
            WHERE piece_player_id = '{$player_id}' AND piece_type = 'trading_post' AND piece_location = 'player_mat' ORDER BY piece_location_arg LIMIT 1");
    }

    function updateTravelPendingActionMarkTradePost(&$pending_action, $board_id, $player_id)
    {
        $pending_action_id = $pending_action["pending_id"];
        $pending_action["type_arg1"] = $pending_action["type_arg1"] == "" ? $board_id : $board_id . "_" . $pending_action["type_arg1"];
        $pending_type_arg1 = $pending_action["type_arg1"];

        if ($pending_action["type"] == "travel") {
            self::DbQuery("UPDATE pending_action SET pending_type_arg1 = '{$pending_type_arg1}' WHERE pending_id = '{$pending_action_id}'");
        } else        //in case pending_action is 'move_trading_post'
        {
            self::DbQuery("UPDATE pending_action SET pending_type_arg = '{$pending_type_arg1}' WHERE pending_type = 'travel' AND pending_player_id = {$player_id}");
        }
    }

    function getAltanOrdResourcesForTradingPost($number_of_trading_posts): array
    {
        $resources = [];
        $altan_ord_bonus_table = [
            1 => ['vp' => 1],
            2 => ['coin' => 1],
            3 => ['camel' => 1],
            4 => ['pepper' => 1],
            5 => ['silk' => 1],
            6 => ['gold' => 1],
        ];
        for ($i = 1; $i <= $number_of_trading_posts; $i++) {
            $resources = array_merge($resources, $altan_ord_bonus_table[$i]);
        }
        return $resources;
    }

    function scoreAltanOrdPlacement($board_id, $player_id, $num_trading_posts)
    {
        if ($num_trading_posts > 7 || $num_trading_posts < 1) {
            return;
        }
        if($num_trading_posts < 7)  {
            $resources = $this->getAltanOrdResourcesForTradingPost($num_trading_posts);
            $this->changePlayerResources($resources, false, "map_node_" . $board_id, $player_id);
        }
        if ($num_trading_posts == 7) {
            $resources = $this->getAltanOrdResourcesForTradingPost(6);
            $this->changePlayerResources($resources, false, "map_node_" . $board_id, $player_id);
            $this->giveAward("", ["black_die" => 1], $player_id);
        }
        self::notifyAllPlayers("message", clienttranslate('${player_name} scores for placing trading post number ${num_trading_post}'), array(
            'player_id' => $player_id, 'player_name' => $this->getPlayerName($player_id), 'num_trading_post' => $num_trading_posts
        ));
    }

    function scoreTradePostPlacement($board_id, $player_id, $num_trading_posts)      //bonus points for placing 8th, 9th, 11th trade post
    {
        $num_vp = 0;
        if ($num_trading_posts == 8) {
            $num_vp = 5;
        }
        if ($num_trading_posts == 9) {
            $num_vp = 10;
        }
        if ($num_trading_posts == 11) {
            $num_vp = 10;
        }

        if ($num_vp > 0) {
            self::notifyAllPlayers("message", clienttranslate('${player_name} scores for placing trading post number ${num_trading_post}'), array(
                'player_id' => $player_id, 'player_name' => $this->getPlayerName($player_id), 'num_trading_post' => $num_trading_posts
            ));
            $this->changePlayerResources(["vp" => $num_vp], false, "map_node_" . $board_id, $player_id);
            self::incStat($num_vp, "trading_post_points", $player_id);
        }
    }

    function hasTravelActionsPending($player_id)
    {
        return self::getUniqueValueFromDB("SELECT pending_id FROM pending_action WHERE pending_type = 'travel' AND pending_player_id = {$player_id}") != null;
    }

    function placeTradingPost($board_id, $via_move_trading_post, $player_id)
    {
        $success = true;
        $board_info = $this->board_map[$board_id];

        if ($board_info["type"] == "oasis" || $board_info["type"] == "start" || $this->hasTradingPostOnBoardSpot($board_id, $player_id))      //cannot place trading house here
            return false;

        $next_trading_post = $this->getNextTradingPostByPlayerId($player_id);
        if ($next_trading_post == null) {
            $this->addToPendingTable("move_trading_post", $board_id, '', 1, '', $player_id);
            $success = false;
        } else {
            $next_position = self::getUniqueValueFromDB("SELECT COUNT(piece_id) FROM piece WHERE piece_type = 'trading_post' AND piece_location = 'board' AND piece_location_arg = {$board_id}");
            $piece_id = $next_trading_post["id"];

            self::DbQuery("UPDATE piece SET piece_location = 'board', piece_location_arg = '{$board_id}', piece_location_position = '{$next_position}' WHERE piece_id = '{$piece_id}'");
            self::notifyAllPlayers("placeTradingPost", clienttranslate('${player_name} places a trading post in ${city_name}'), array("player_id" => $player_id,
                    "player_name" => $this->getPlayerName($player_id), "trading_post_id" => $piece_id, "location" => "board", "location_arg" => $board_id,
                    "location_position" => $next_position, "city_name" => $this->board_map[$board_id]["name"])
            );
        }

        if ($success && $via_move_trading_post == false) {
            self::incStat(1, "trading_posts", $player_id);
            $num_trading_posts = $this->getNumberOfTradingPostsPlaced($player_id);
            $this->scoreTradePostPlacement($board_id, $player_id, $num_trading_posts);
            if ($this->getPlayerIdByCharacterType(10) == $player_id) // Altan Ord bonuses
                $this->scoreAltanOrdPlacement($board_id, $player_id, $num_trading_posts);
        }

        return $success;
    }

    function processMarcoPoloExpansionsExtraTradePost($current_board_id, $player_id)
    {
        if ($this->getPlayerIdByCharacterType(5) != $player_id)     //look at both figures in case it ends in city
            return;

        $current_figure_locations = self::getObjectListFromDB("SELECT piece_location_arg FROM piece WHERE piece_type = 'figure' AND piece_player_id = '{$player_id}'");
        foreach ($current_figure_locations as $id => $figure_location) {
            $figure_board_id = $figure_location["piece_location_arg"];
            if ($figure_board_id != $current_board_id) {
                $success = $this->placeTradingPost($figure_board_id, false, $player_id);
                if ($success) {
                    $this->giveTradingPostBonuses([$figure_board_id], $player_id);
                }
            }
        }
    }

    function placeTradingPostAndGiveAward($board_id, $pending_action, $player_id)
    {
        if ($pending_action["remaining_count"] != 1 && $this->getPlayerIdByCharacterType(4) != $player_id)        //let Withelm character place trading posts
            return;

        $success = $this->placeTradingPost($board_id, $pending_action["type"] == "move_trading_post", $player_id);        //try to place trading post
        if ($success) {
            $this->updateTravelPendingActionMarkTradePost($pending_action, $board_id, $player_id);        //needed to trigger awards after travel complete (for Withelm)
        }

        if ($pending_action["type"] == "travel" && $pending_action["remaining_count"] == 1 || $this->hasTravelActionsPending($player_id) == false)     //last trading post placed give award
        {
            $this->giveTradingPostBonuses(explode("_", $pending_action["type_arg1"]), $player_id);
            if ($pending_action["type"] == "travel") {
                $this->processMarcoPoloExpansionsExtraTradePost($board_id, $player_id);         //check if another trading post should be placed for marco polo character
            }
        }
    }

    function getCityBonuses($player_id)
    {
        $city_bonuses = self::getObjectListFromDB("SELECT p1.piece_type_arg city_bonus_type, p1.piece_location_arg board_id FROM piece p1, piece p2 WHERE
            p2.piece_type = 'trading_post' AND p1.piece_location = p2.piece_location AND p1.piece_location_arg = p2.piece_location_arg
            AND p2.piece_player_id = '{$player_id}' and p1.piece_type = 'city_bonus'");
        return $city_bonuses;
    }

    function awardBonus($bonus_type, $bonus_data, $location_id, $queue_bonus, $player_id)
    {
        if (array_key_exists("award", $bonus_data) == false)       //no award just return
            return;

        if ((array_key_exists("auto", $bonus_data) && $bonus_data["auto"] == true) || !$queue_bonus) {
            $ui_location = "";
            $from_bonus_name = "";
            if ($bonus_type == "city_bonus") {
                $ui_location = "map_node_" . $location_id;
                $from_bonus_name = $this->board_map[$location_id]["name"];
                if (array_key_exists("vp", $bonus_data["award"])) {
                    self::incStat($bonus_data["award"]["vp"], "city_bonus_points", $player_id);
                }
            } else if ($bonus_type == "character") {
                $ui_location = "character_" . $player_id;
                $from_bonus_name = clienttranslate("character");
            }

            self::notifyAllPlayers("message", clienttranslate('${player_name} collects ${from_bonus_name} bonus'), array('i18n' => array('from_bonus_name'),
                'player_id' => $player_id, 'player_name' => $this->getPlayerName($player_id), 'from_bonus_name' => $from_bonus_name
            ));
            $this->giveAward($ui_location, $bonus_data["award"], $player_id);
        } else {
            foreach ($bonus_data["award"] as $award_type => $award_amount) {
                $this->addToPendingBonusTable($bonus_type, $award_type, $bonus_data["type"], $player_id);
            }
        }
    }

    function givePlayerRoundBonuses()
    {
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $character_type = self::getUniqueValueFromDB("SELECT character_type FROM player WHERE player_id = {$player_id}");
            $this->awardBonus("character", $this->character_types[$character_type], $character_type, true, $player_id);

            $city_bonuses = $this->getCityBonuses($player_id);
            if ($city_bonuses != null) {
                foreach ($city_bonuses as $city_bonus) {
                    $bonus_type = $city_bonus["city_bonus_type"];
                    $this->awardBonus("city_bonus", $this->city_bonus_types[$bonus_type], $city_bonus["board_id"], true, $player_id);
                }
            }
        }
    }

    function giveTradingPostBonuses($board_ids, $player_id)
    {
        foreach ($board_ids as $board_id) {
            $piece_info = self::getObjectFromDB("SELECT piece_id, piece_type, piece_type_arg FROM piece WHERE piece_location = 'board'
                 AND piece_location_arg = '{$board_id}' AND piece_type IN ('city_bonus', 'outpost')");

            if ($piece_info) {
                $bonus_type = $piece_info["piece_type_arg"];
                $piece_type = $piece_info["piece_type"];
                $reward = $piece_type == "city_bonus" ? $this->city_bonus_types : $this->outpost_bonus_types;
                $this->giveAward('map_node_' . $board_id, $reward[$bonus_type]["award"], $player_id);

                if ($piece_type == "city_bonus") {
                    if (array_key_exists("vp", $reward[$bonus_type]["award"])) {
                        self::incStat($reward[$bonus_type]["award"]["vp"], "city_bonus_points", $player_id);
                    }
                } else if ($piece_type == "outpost") {
                    $piece_id = $piece_info["piece_id"];
                    self::DbQuery("UPDATE piece SET piece_location = 'box' WHERE piece_id = {$piece_id}");
                    self::notifyAllPlayers("boxPiece", '', array("piece_type" => "outpost", "piece_id" => $piece_id, "location" => "box"));
                    self::incStat(1, "outpost_bonuses", $player_id);
                }
            }
        }
    }

    // Returns the item data as listed on the materials list.
    // Example: getBoardSpot('city_card', 0) gets the first city card
    function getBoardSpot($place, $index, $award_index)
    {
        if ($place == "city_card") {
            foreach ($this->city_card_types as $card_type) {
                if ($card_type["type"] == $index) {
                    $card_type["name"] = clienttranslate("city card ") . $index;
                    return $card_type;
                }
            }
        } else if ($place == "gunj") {
            return $this->character_types[11]["places"][$index];
        } else {
            foreach ($this->board_spots as $board_spot) {
                if ($place == "bazaar" || $place == "travel") {
                    if ($board_spot["is_award_spot"] && $board_spot["place"] == $place && $board_spot["tied_to_index"] == $index && $board_spot["index"] == $award_index) {
                        return $board_spot;
                    }
                } else if ($board_spot["place"] == $place && $board_spot["index"] == $index) {
                    return $board_spot;
                }
            }
        }

        return null;
    }

    function payDiePlacement($diceOnSpot, $dice, $place_info, $free_dice_placement_gift_id, $player_id)
    {
        if ((!$diceOnSpot || $place_info["place"] == "coin3") && $free_dice_placement_gift_id != null)         //don't use gift in these scenarios
            throw new BgaUserException(self::_("This die placement is free, you should not use the free die placement gift"));

        if ($place_info["place"] == "coin3")      //no payment
            return;

        if ($diceOnSpot && $this->getPlayerIdByCharacterType(6) != $player_id && $free_dice_placement_gift_id == null)      //Berke does not have to pay coins
        {
            $num_coins = array_reduce($dice, function ($lowest, $die) {
                return min($lowest, $die["die_value"]);
            }, 99);
            if ($this->validateCost(["coin" => $num_coins], $player_id) == false)
                throw new BgaUserException(self::_("Insufficent coins to place die here"));

            $this->changePlayerResources(["coin" => $num_coins], true, $place_info["ui_location"], $player_id);
        }

        if ($place_info["place"] == "travel") {
            if ($this->validateCost($place_info["cost"], $player_id) == false)
                throw new BgaUserException(self::_("Insufficient coins to place die here"));

            self::notifyAllPlayers("message", clienttranslate('${player_name} chooses to travel upto ${steps} steps'), array("player_id" => $player_id,
                "player_name" => self::getActivePlayerName(), "steps" => $place_info["index"] + 1));
            $this->changePlayerResources($place_info["cost"], true, $place_info["ui_location"], $player_id);
        }
    }

    function validateCost($cost, $player_id)
    {
        $valid_resources = ["coin", "camel", "pepper", "silk", "gold"];
        $is_vaild = true;
        $resources = $this->getPlayerResources($player_id);
        foreach ($cost as $cost_id => $amount) {
            if ($cost_id == "choice_of_good") {
                $is_vaild &= $resources["pepper"] > 0 || $resources["silk"] > 0 || $resources["gold"] > 0;
            } else if (in_array($cost_id, $valid_resources) && $resources[$cost_id] < $amount) {
                $is_vaild &= false;
            }
        }
        return $is_vaild;
    }

    function convertTwoDiffGoods($detail)      //detail is the string that the UI gives representing the
    {
        $result = null;
        if ($detail == "pepper_silk") {
            $result = ["pepper" => 1, "silk" => 1];
        } else if ($detail == "pepper_gold") {
            $result = ["pepper" => 1, "gold" => 1];
        } else if ($detail == "silk_gold") {
            $result = ["silk" => 1, "gold" => 1];
        }
        return $result;
    }

    function validateKhanDicePlacement($dice, $player_id)
    {
        $dice_on_khan = self::getObjectListFromDB("SELECT die_player_id, die_value, die_type FROM die WHERE die_location = 'khan'");
        $die_played = array_pop($dice);

        foreach ($dice_on_khan as $die) {
            if ($die["die_player_id"] == $player_id && $die["die_type"] == "regular" && $die_played["die_type"] == "regular")
                throw new BgaVisibleSystemException("khan die placement: already played here");

            if ($die["die_value"] > $die_played["die_value"])
                throw new BgaVisibleSystemException("khan die placement: value not high enough");
        }
    }

    function getAwardOrCostValuesFromCityCard($city_card_info, $key)
    {
        $values = [];
        if ($city_card_info["kind"] == "choice") {
            foreach ($city_card_info["choice"] as $choice) {
                array_push($values, $choice[$key]);
            }
        } else if ($city_card_info["kind"] == "exchange")       //key doesn't matter
        {
            $values[] = $city_card_info["cost"];
            $values[] = $city_card_info["award"];
        } else if (array_key_exists($key, $city_card_info)) {
            array_push($values, $city_card_info[$key]);
        }
        return $values;
    }

    function validateOwnsPlace($place_info, $player_id)
    {
        if ($place_info["place"] == "gunj" && $this->getPlayerIdByCharacterType(11) != $player_id)
            throw new BgaVisibleSystemException("own place: character spot only available to player with character");
    }

    function validateCityCard($city_card_info, $player_id)
    {
        $card_type = $city_card_info["type"];
        $board_id = self::getUniqueValueFromDB("SELECT piece_location_arg FROM piece WHERE piece_type_arg = {$card_type} AND piece_type = 'city_card'");

        if ($this->hasTradingPostOnBoardSpot($board_id, $player_id) == false)
            throw new BgaVisibleSystemException("die placement:  must have trading post at city");

        $costs = $this->getAwardOrCostValuesFromCityCard($city_card_info, "cost");
        $is_vaild_cost = sizeof($costs) == 0 ? true : false;              //default to true if no costs
        foreach ($costs as $cost) {
            $is_vaild_cost |= $this->getNumberOfTimesCostSatisfied($cost, $player_id) > 0;
        }

        if ($is_vaild_cost == false)
            throw new BgaUserException(self::_("Can't activate city card, must be able to activate at least once"));

        $awards = $this->getAwardOrCostValuesFromCityCard($city_card_info, "award");
        foreach ($awards as $award) {
            if (array_key_exists("trigger_city_bonus_having_trading_post", $award) && sizeof($this->getCityBonuses($player_id)) == 0)
                throw new BgaUserException(self::_("Can't activate city card, must be able to activate at least once"));
        }
    }

    function validateGiftTypeAndOwner($gift_id, $gift_type, $player_id)
    {
        $gift = $this->deck->getCard($gift_id);

        if ($gift["location"] != "gift_hand")
            throw new BgaVisibleSystemException("gift validate: not in player hand");

        if ($gift["location_arg"] != $player_id)
            throw new BgaVisibleSystemException("gift validate: does not match player");

        if ($gift_type != null && $gift["type_arg"] != $gift_type)
            throw new BgaVisibleSystemException("gift validate: does not match type required");
    }

    function validateGiftAgainstAllowable($gift_id, $string_valid_gift_ids, $player_id)
    {
        $gift = $this->deck->getCard($gift_id);
        $valid_gift_ids = explode("_", $string_valid_gift_ids);

        if ($gift["location"] != "gift_hand" && $gift["location_arg"] != $player_id)
            throw new BgaVisibleSystemException("discard gift: cannot discard this gift, not yours");

        if ($valid_gift_ids[0] != "" && in_array($gift_id, $valid_gift_ids) == false)
            throw new BgaVisibleSystemException("discard gift: cannot discard this gift, must discard a picked one");
    }

    function validateDicePlacement($place_info, $dice, $playersOnDieSpot, $diceOnSpot, $gift_free_dice_placement_id, $player_id)
    {
        if ($place_info == null)
            throw new BgaVisibleSystemException("die placement:  invalid place and index given");

        if (self::getGameStateValue('performed_main_action') == 1 && $place_info["place"] != "coin3")
            throw new BgaVisibleSystemException("die placement: already did a main action");

        if (sizeof($dice) == 0)
            throw new BgaVisibleSystemException("die placement:  no dice given");

        if ($place_info["num_dice"] != sizeof($dice))
            throw new BgaVisibleSystemException("die placement:  not enough dice");

        if ($place_info["allow_multiple"] == false && sizeof($playersOnDieSpot) > 0)
            throw new BgaVisibleSystemException("die placement:  spot already taken");

        if ($place_info["place"] == "khan")
            $this->validateKhanDicePlacement($dice, $player_id);

        if ($place_info["place"] == "city_card")
            $this->validateCityCard($place_info, $player_id);

        if (array_key_exists("owns_place", $place_info))
            $this->validateOwnsPlace($place_info, $player_id);

        $is_using_regular_dice = false;
        $lowest_value = 99;
        foreach ($dice as $die) {
            if ($die["die_player_id"] != $player_id || $die["die_location"] != "player_mat")
                throw new BgaVisibleSystemException("die placement:  cannot place these dice");

            $lowest_value = min($lowest_value, $die["die_value"]);
            $is_using_regular_dice = $is_using_regular_dice || $die["die_type"] == "regular";
        }

        if ($is_using_regular_dice && in_array($player_id, $playersOnDieSpot) && $place_info["place"] != "coin3")
            throw new BgaVisibleSystemException("die placement:  cannot go to same spot");

        if ($diceOnSpot && $lowest_value > $this->getPlayerResources($player_id)["coin"] && $this->getPlayerIdByCharacterType(6) != $player_id && $place_info["place"] != "coin3" && $gift_free_dice_placement_id != null)
            throw new BgaVisibleSystemException("die placement:  not enough coins");

        if (array_key_exists("min_die", $place_info) && $place_info["min_die"] > $lowest_value)
            throw new BgaVisibleSystemException("die placement:  die value to low for this award");
    }

    function slideRemainingContracts($trigger_by)
    {
        $cards = $this->deck->getCardsInLocation("board", null, "card_location_arg");
        $position = 0;
        foreach ($cards as $card) {
            if ($card["location_arg"] != $position) {
                $this->deck->moveCard($card["id"], "board", $position);
            }
            $position++;
        }
        $contracts = $this->deck->getCardsInLocation("board");
        self::notifyAllPlayers("slideContracts", "", array("contracts" => $contracts, "trigger_by" => $trigger_by));
    }

    function discardContract($contract_id, $string_valid_contract_ids, $player_id)
    {
        $contract = $this->deck->getCard($contract_id);
        $valid_contract_ids = explode("_", $string_valid_contract_ids);
        $location = "discard";

        if ($contract["location"] != "hand" && $contract["location_arg"] != $player_id)
            throw new BgaVisibleSystemException("discard contract: cannot discard this contract");

        if ($valid_contract_ids[0] != "" && in_array($contract_id, $valid_contract_ids) == false)
            throw new BgaVisibleSystemException("discard contract: cannot discard this contract, must discard an old one");

        if ($this->contract_types[$contract["type"]]["starter"] == false)
            $location = "box";

        $this->deck->moveCard($contract_id, $location);
        self::notifyAllPlayers("contract", clienttranslate('${player_name} discards a contract'), array("player_id" => $player_id,
            "player_name" => $this->getPlayerName($player_id), "discard_contract_id" => $contract_id));
    }

    function useGift($gift_id, $useAndDiscard, $player_id)
    {
        $gift = $this->deck->getCard($gift_id);
        if ($useAndDiscard) {
            $this->deck->moveCard($gift_id, "gift_discard");
        }
        $notifType = $useAndDiscard ? "boxPiece" : "message";
        self::notifyAllPlayers($notifType, clienttranslate('${player_name} uses gift ${gift_type}'), array("player_id" => $player_id, "piece_type" => "gift", "piece_id" => $gift_id,
            "player_name" => $this->getPlayerName($player_id), "gift_type" => $gift["type_arg"],
        ));
    }

    function discardGift($gift_id, $bypassMessage, $player_id)
    {
        $this->deck->moveCard($gift_id, "gift_discard");
        $message = $bypassMessage ? "" : clienttranslate('${player_name} discards a gift');
        self::notifyAllPlayers("boxPiece", $message, array("player_id" => $player_id,
            "player_name" => $this->getPlayerName($player_id), "piece_type" => "gift", "piece_id" => $gift_id
        ));
    }

    function findMatchingBoardEdge($src_id, $dst_id, $player_id)
    {
        $matching_edge = null;
        $johannesPlayerId = $this->getPlayerIdByCharacterType(3);  //can teleport
        foreach ($this->board_edges as $edge) {
            if (($edge["src"] == $src_id && $edge["dst"] == $dst_id) || ($edge["src"] == $dst_id && $edge["dst"] == $src_id)) {
                $matching_edge = $edge;
                break;
            } else if ($player_id == $johannesPlayerId && $this->board_map[$src_id]["type"] == "oasis" && $this->board_map[$dst_id]["type"] == "oasis") {
                $matching_edge = array("src" => $src_id, "dst" => $dst_id, "cost" => []);     //johannes can teleport between oasis (no cost).  create fake edge to represent
                break;
            }
        }
        return $matching_edge;
    }

    function computeMostContractsPoints($player_id)
    {
        $completed_contracts = $this->deck->getCardsInLocation("complete", $player_id);
        $max_completed_contracts = self::getUniqueValueFromDB("SELECT count(card_id) FROM card WHERE card_location = 'complete' GROUP BY card_location_arg ORDER BY COUNT(card_id) DESC LIMIT 1");
        if (sizeof($completed_contracts) == $max_completed_contracts) {
            return 7;
        }
        return 0;
    }

    function computeUniqueCityGoalCardPoints($player_id)
    {
        $all_cities = [];
        $goal_cards = $this->deck->getCardsInLocation("goal_hand", $player_id);
        foreach ($goal_cards as $goal_card) {
            $goal_info = $this->goal_card_types[$goal_card["type"]];
            $all_cities = array_merge($all_cities, $goal_info["cities"]);
        }
        $sql_all_cities = implode(", ", array_unique($all_cities));
        $num_uniq_posts = self::getUniqueValueFromDB("SELECT count(piece_id) FROM piece WHERE piece_type = 'trading_post' AND piece_location = 'board' AND piece_player_id = {$player_id} AND piece_location_arg IN ( $sql_all_cities )");
        return [0, 1, 3, 6, 10][$num_uniq_posts];;
    }

    function computeReachedGoalCardPoints($player_id)
    {
        $goal_card_points = 0;
        $goal_cards = $this->deck->getCardsInLocation("goal_hand", $player_id);
        foreach ($goal_cards as $goal_card) {
            $goal_info = $this->goal_card_types[$goal_card["type"]];
            $sql_cities = implode(", ", $goal_info["cities"]);
            $num_posts = self::getUniqueValueFromDB("SELECT count(piece_id) FROM piece WHERE piece_type = 'trading_post' AND piece_location = 'board' AND piece_player_id = {$player_id} AND piece_location_arg IN ( $sql_cities )");
            if ($num_posts == sizeof($goal_info["cities"])) {
                $goal_card_points += $goal_info["vp"];
            }
        }
        return $goal_card_points;
    }

    function addToPendingTable($pending_type, $pending_type_arg, $pending_type_arg1, $pending_remaining_count, $location, $player_id)
    {
        self::DbQuery("INSERT INTO pending_action(pending_type, pending_type_arg, pending_type_arg1, pending_remaining_count, pending_location, pending_player_id) VALUES
                       ( '{$pending_type}', '{$pending_type_arg}', '{$pending_type_arg1}', '{$pending_remaining_count}', '{$location}', '{$player_id}' )");
    }

    function getNextPendingAction($player_id)
    {
        return self::getObjectFromDB("SELECT pending_id, pending_type type, pending_type_arg type_arg, pending_type_arg1 type_arg1, pending_remaining_count remaining_count,
            pending_location location FROM pending_action WHERE pending_player_id = {$player_id} AND pending_type <> 'bonus' ORDER BY pending_id DESC LIMIT 1");
    }

    function getNextPendingActions()
    {
        $actions = [];
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $player_action = self::getObjectFromDB("SELECT pending_id, pending_type type, pending_type_arg type_arg, pending_type_arg1 type_arg1, pending_remaining_count remaining_count,
                pending_location location, pending_player_id FROM pending_action WHERE pending_type <> 'bonus' AND pending_player_id = {$player_id} ORDER BY pending_id DESC LIMIT 1");
            if ($player_action != null) {
                array_push($actions, $player_action);
            }
        }
        return $actions;
    }

    function updateNextPendingAction($delta, $pending_action)
    {
        $pending_id = $pending_action["pending_id"];
        if ($pending_action["remaining_count"] + $delta < 1) {
            $this->deletePendingAction($pending_id);
        } else {
            self::DbQuery("UPDATE pending_action SET pending_remaining_count = pending_remaining_count + {$delta} WHERE pending_id = {$pending_id}");
        }
    }

    function deletePendingAction($pending_action_id)
    {
        self::DbQuery("DELETE FROM pending_action WHERE pending_id = {$pending_action_id}");
    }

    function getNextTransitionBasedOnPendingActions($player_id)
    {
        $transition_map = [
            "pick_contract" => "pickContract",
            "travel" => "travel",
            "discard_contract" => "chooseResource", "choice_of_good" => "chooseResource", "camel_coin" => "chooseResource", "2_diff_goods" => "chooseResource",
            "trigger_other_city_bonus" => "triggerOtherCityBonus", "trigger_city_bonus_having_trading_post" => "triggerOtherCityBonus",
            "move_trading_post" => "moveTradingPost",
            "city_card" => "chooseCityCardAward",
            "switch_to_gunj_bonus" => "gunj_bonus",
        ];


        $transition_to = "continue";
        $pending_action = $this->getNextPendingAction($player_id);
        self::dump( 'pending_action', $pending_action );
        self::dump( 'game_state', $this->gamestate->state() );
        if ($this->gamestate->state()["name"] == "playerBonus" || $this->gamestate->state()["name"] == "playerGunjBonus")       //always continue in player bonus
        {
            $transition_to = "continue";
        } else if ($pending_action != null && array_key_exists($pending_action["type"], $transition_map)) {
            self::debug( 'enteredif' );
            $transition_to = $transition_map[$pending_action["type"]];
        }
        return $transition_to;
    }

    function addToPendingBonusTable($bonus_type, $bonus_type_arg, $location, $player_id)
    {
        $this->addToPendingTable("bonus", $bonus_type, $bonus_type_arg, 1, $location, $player_id);
    }

    function getPendingBonuses()
    {
        return self::getObjectListFromDB("SELECT pending_id id, pending_type_arg bonus_type, pending_type_arg1 bonus_type_arg, pending_location bonus_location, pending_player_id player_id
                FROM pending_action WHERE pending_type = 'bonus'");
    }

    function getPendingBonusesByPlayerId($player_id)
    {
        return self::getObjectListFromDB("SELECT pending_id id, pending_type_arg bonus_type, pending_type_arg1 bonus_type_arg, pending_location bonus_location, pending_player_id player_id
                FROM pending_action WHERE pending_type = 'bonus' AND pending_player_id = {$player_id}");
    }

    function getPendingBonusById($bonus_id)
    {
        return self::getObjectFromDB("SELECT pending_id id, pending_type_arg bonus_type, pending_type_arg1 bonus_type_arg, pending_location bonus_location, pending_player_id player_id
                FROM pending_action WHERE pending_id = {$bonus_id}");
    }

    function deletePendingBonusById($bonus_id)
    {
        self::DbQuery("DELETE from pending_action WHERE pending_id = {$bonus_id}");
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////
    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in marcopoloexpansions.action.php)
    */
    function undo()
    {
        self::checkAction("undo");
        if (self::getGameStateValue("can_undo") == 1) {
            $this->undoRestorePoint();
        }
    }

    function pickCharacter($character_type)
    {
        self::checkAction('pickCharacter');
        $player_id = self::getActivePlayerId();
        $pending_id = self::getUniqueValueFromDB("SELECT pending_id FROM pending_action WHERE pending_type = 'character' AND pending_type_arg = {$character_type}");
        if ($pending_id == null)
            throw new BgaUserException(self::_("Oops something went wrong, this character is not available"));

        self::notifyAllPlayers("pickCharacter", clienttranslate('${player_name} picks character ${character_name}'), array('i18n' => array('character_name'), 'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(), 'character_type' => $character_type, 'character_name' => $this->character_types[$character_type]["name"])
        );
        $this->setupCharacter($character_type, $player_id);
        self::DbQuery("DELETE FROM pending_action WHERE pending_id = {$pending_id}");
        $this->gamestate->nextState("");
    }

    function pickGoalCards($card_ids)
    {
        self::checkAction('pickGoalCards');
        if (sizeof($card_ids) != 2)
            throw new BgaUserException(self::_("Must pick exactly two cards"));

        $player_id = self::getCurrentPlayerId();
        $cards = $this->deck->getCardsInLocation("goal_hand", $player_id);
        $success_count = 0;

        foreach ($cards as $card) {
            if (in_array($card["id"], $card_ids) == false) {
                $this->deck->moveCard($card["id"], "goal_discard");
            } else {
                $success_count += 1;
            }
        }

        if ($success_count != 2)
            throw new BgaVisibleSystemException("Invalid goal cards selected");

        self::notifyAllPlayers("pickedGoalCards", clienttranslate('${player_name} has picked their goal cards'), array('player_id' => $player_id,
            'player_name' => $this->getPlayerName($player_id)));

        $this->gamestate->setPlayerNonMultiactive($player_id, "done");
    }

    function rerollDie($die_id)
    {
        self::checkAction('rerollDie');
        $player_id = self::getActivePlayerId();
        $dice = $this->getDiceByIds([$die_id]);

        if (sizeof($dice) == 0 || $dice[$die_id]["die_player_id"] != $player_id || $dice[$die_id]["die_location"] != "player_mat")
            throw new BgaVisibleSystemException("error on re-roll, bad die id");

        if ($this->validateCost(["camel" => 1], $player_id) == false)
            throw new BgaUserException(self::_("You need 1 camel to re-roll a die"));

        if ($this->getPlayerIdByCharacterType(2) == $player_id)
            throw new BgaUserException(self::_("Your character can change die values, no need to re-roll"));

        $die_value = $this->rollDie($die_id, $player_id);
        $dice = $this->getDiceByIds([$die_id]);
        self::notifyAllPlayers("updateDice", clienttranslate('${player_name} re-rolls a die and rolls a ${die_value}'), array("player_id" => $player_id,
            "player_name" => self::getActivePlayerName(), "dice" => $dice, "die_value" => $die_value, "shake" => true));

        $this->changePlayerResources(["camel" => 1], true, "board", $player_id);

        $this->setNewUndoPoint();
        self::incStat(1, "die_rerolls", $player_id);
        $this->gamestate->nextState("continue");
    }

    function pickCompensation($num_camel, $num_coin)
    {
        self::checkAction('pickCompensation');
        $player_id = self::getCurrentPlayerId();
        $die_totals = self::getUniqueValueFromDB("SELECT sum(die_value) FROM die WHERE die_player_id = {$player_id}");
        $comp = 15 - $die_totals;

        if ($num_camel + $num_coin != $comp)
            throw new BgaUserException(self::_("Compensation selected is not a valid combination"));

        self::notifyAllPlayers("message", clienttranslate('${player_name} picks compensation'), array("player_name" => $this->getPlayerName($player_id)));
        $this->changePlayerResources(["camel" => $num_camel, "coin" => $num_coin], false, "board", $player_id);
        self::incStat($num_camel, "compensation_receive_camel", $player_id);
        self::incStat($num_coin, "compensation_receive_coin", $player_id);

        $this->gamestate->setPlayerNonMultiactive($player_id, "done");
    }

    function buyBlackDie()
    {
        self::checkAction('buyBlackDie');
        $player_id = self::getActivePlayerId();
        $die_id = $this->getNextAvailableBlackDie();

        if ($die_id == null)
            throw new BgaVisibleSystemException("none remaining");

        if ($this->validateCost(["camel" => 3], $player_id) == false)
            throw new BgaVisibleSystemException("not enough resources");

        if (self::getGameStateValue("black_die_bought") == 1)
            throw new BgaUserException(self::_("can only buy one black die per turn"));

        self::setGameStateValue("black_die_bought", 1);
        $this->givePlayerBlackDie($die_id, true, $player_id);
        $this->changePlayerResources(["camel" => 3], true, "board_spot_avail_black_die_0", $player_id);
        $this->gamestate->nextState("continue");
    }

    function bumpDie($die_id, $up_down)
    {
        self::checkAction("bumpDie");
        self::setGameStateInitialValue("can_undo", 1);
        $player_id = self::getActivePlayerId();
        $dice = $this->getDiceByIds([$die_id]);

        if (sizeof($dice) == 0 || $dice[$die_id]["die_player_id"] != $player_id || $dice[$die_id]["die_location"] != "player_mat")
            throw new BgaVisibleSystemException("error cannot bump, bad die id");

        if ($this->validateCost(["camel" => 2], $player_id) == false)
            throw new BgaUserException(self::_("You need 2 camels to bump die value"));

        $die_value = $dice[$die_id]["die_value"];
        $prev_die_value = $die_value;
        $die_value = $up_down == "up" ? $die_value + 1 : $die_value - 1;

        if ($die_value > 6 || $die_value < 1)
            throw new BgaVisibleSystemException("error invalid bump");

        self::DbQuery("UPDATE die SET die_value = {$die_value} WHERE die_id = {$die_id}");
        $dice = $this->getDiceByIds([$die_id]);
        self::notifyAllPlayers("updateDice", clienttranslate('${player_name} adjusts a die from ${prev_die_value} to ${die_value}'), array("player_id" => $player_id,
            "player_name" => self::getActivePlayerName(), "dice" => $dice, "die_value" => $die_value, "prev_die_value" => $prev_die_value, "shake" => false));

        $this->changePlayerResources(["camel" => 2], true, "board", $player_id);
        self::incStat(1, "die_bump", $player_id);
        $this->gamestate->nextState("continue");
    }

    function changeDice($dice_ids, $new_value, $gift_id)
    {
        self::checkAction("changeDice");
        self::setGameStateInitialValue("can_undo", 1);
        $player_id = self::getActivePlayerId();
        if ($this->getPlayerIdByCharacterType(2) != $player_id && $gift_id == null)
            throw new  BgaVisibleSystemException("change dice: cannot change dice");

        if ($new_value < 1 || $new_value > 6)
            throw new  BgaVisibleSystemException("change dice: invalid value");

        if ($gift_id != null) {
            $this->validateGiftTypeAndOwner($gift_id, 4, $player_id);
        }

        $dice = $this->getDiceByIds($dice_ids);
        foreach ($dice as &$die) {
            if ($die["die_player_id"] != $player_id)
                throw new BgaVisibleSystemException("change dice: can only change your own dice");

            $die["die_value"] = $new_value;
            $die_id = $die["die_id"];
            self::DbQuery("UPDATE die SET die_value = {$new_value} WHERE die_id = {$die_id}");
        }

        $change_die_sentence = clienttranslate('${player_name} changes some of their dice to a ${die_value} using Raschid ad-Din Sinan');

        if ($gift_id != null) {
            $change_die_sentence = clienttranslate('${player_name} changes one of their dice to a ${die_value}');
            $this->useGift($gift_id, true, $player_id);
        }

        self::notifyAllPlayers("updateDice", $change_die_sentence, array(
            "player_id" => $player_id, "player_name" => self::getActivePlayerName(), "dice" => $dice, "die_value" => $new_value, "shake" => false
        ));

        $this->gamestate->nextState("continue");
    }

    function placeDie($place, $index, $award_index, $dice_ids, $free_dice_placement_gift_id)
    {
        self::checkAction('placeDie');
        self::setGameStateInitialValue("can_undo", 1);
        $player_id = self::getActivePlayerId();
        $place_info = $this->getBoardSpot($place, $index, $award_index);
        $dice = $this->getDiceByIds($dice_ids);
        $playersOnDieSpot = $this->getPlayerIdsOnDiePlace($place, $index, true);
        $diceOnSpot = sizeof($this->getPlayerIdsOnDiePlace($place, $index, false)) > 0;
        $num_coins = 0;

        if ($free_dice_placement_gift_id != null) {
            $this->validateGiftTypeAndOwner($free_dice_placement_gift_id, 7, $player_id);
        }
        $this->validateDicePlacement($place_info, $dice, $playersOnDieSpot, $diceOnSpot, $free_dice_placement_gift_id, $player_id);
        $dice_info = $this->moveDice($dice_ids, $place, $index, $this->getNextDiceHeight($place, $index), $player_id);
        $place_name = $place_info["name"];
        self::notifyAllPlayers("updateDice", clienttranslate('${player_name} places dice and activates ${place_name}'), array("player_id" => $player_id,
                "player_name" => self::getActivePlayerName(), "place_name" => $place_name, "num_coins" => $num_coins, "dice" => $dice_info, "shake" => false)
        );

        if ($free_dice_placement_gift_id != null) {
            $this->useGift($free_dice_placement_gift_id, true, $player_id);
        }
        $this->payDiePlacement($diceOnSpot, $dice, $place_info, $free_dice_placement_gift_id, $player_id);

        if ($place != "coin3") {
            self::setGameStateValue('performed_main_action', 1);
            self::incStat(1, "main_actions", $player_id);
        }

        if ($place == "travel") {
            $this->setHourGlass($player_id);
        }

        if ($place == "city_card") {
            $this->giveCityAward($place_info, $dice, $player_id);
        } else {
            $this->giveGunjGoodAward($place_info, $player_id);
            $this->giveAward($place_info["ui_location"], $place_info["award"], $player_id);
            $this->giveMercatorBonus($place_info, $player_id);
        }
        self::incStat(sizeof($dice), "total_dice", $player_id);
        self::incStat(array_reduce(array_map(function ($d) {
            return $d["die_value"];
        }, $dice), function ($c, $v) {
            return $c + $v;
        }, 0), "total_dice_value", $player_id);
        self::setStat(self::getStat("total_dice_value", $player_id) / self::getStat("total_dice", $player_id), "avg_dice_value", $player_id);
        $transition_to = $this->getNextTransitionBasedOnPendingActions($player_id);
        $this->gamestate->nextState($transition_to);
    }

    function triggerBonus($bonus_id)
    {
        self::checkAction('triggerBonus');
        $player_id = self::getCurrentPlayerId();
        $bonus_action = $this->getPendingBonusById($bonus_id);

        if ($bonus_action == null)
            throw new BgaVisibleSystemException("trigger bonus : invalid bonus id");

        if ($bonus_action["player_id"] != $player_id)
            throw new BgaVisibleSystemException("trigger bonus : invalid player id");

        $bonus_type = $bonus_action["bonus_type"];
        $bonus_type_type = $bonus_action["bonus_location"];     //type in given sub-bonus type (i.e. character type or card type)
        $type_data = $bonus_type . "_types";
        $bonus_data = $this->$type_data[$bonus_type_type];
        $location_id = $bonus_type_type;
        if ($bonus_type == "city_bonus") {
            $location_id = self::getUniqueValueFromDB("SELECT piece_location_arg FROM piece WHERE piece_type = 'city_bonus' AND piece_type_arg = {$bonus_type_type}");
        }
        $this->awardBonus($bonus_type, $bonus_data, $location_id, false, $player_id);

        $this->deletePendingBonusById($bonus_id);
        $this->gamestate->nextState("continue");
    }

    function chooseResource($choice)
    {
        self::checkAction('chooseResource');
        $player_id = $this->getRequestingPlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $resources = null;
        $drop_by = -1;
        $negate = false;

        if ($pending_action == null)
            throw new BgaVisibleSystemException("choose resource : invalid state");

        if ($pending_action["type"] == "choice_of_good" && in_array($choice, ["pepper", "silk", "gold"]) == false)
            throw new BgaVisibleSystemException("choose resource : invalid good - choice");

        if ($pending_action["type"] == "2_diff_goods" && in_array($choice, ["pepper_silk", "pepper_gold", "silk_gold"]) == false)
            throw new BgaVisibleSystemException("choose resource : invalid good - 2diff");

        if ($pending_action["type"] == "camel_coin" && in_array($choice, ["camel", "coin"]) == false)
            throw new BgaVisibleSystemException("choose resource : invalid good - camelcoin");

        if ($pending_action["type"] == "choice_of_good" && $pending_action["type_arg"] == "pay" && $this->validateCost([$choice => 1], $player_id) == false)
            throw new BgaVisibleSystemException("choose resource : can't pay this resource don't have");

        if ($pending_action["type"] == "choice_of_good") {
            $resources = [$choice => 1];
            $negate = $pending_action["type_arg"] == "pay";
        } else if ($pending_action["type"] == "2_diff_goods") {
            $resources = $this->convertTwoDiffGoods($choice);
        } else if ($pending_action["type"] == "camel_coin")     //for contract
        {
            $resources = [$choice => $pending_action["remaining_count"]];
            $drop_by = -1 * $pending_action["remaining_count"];
        } else if ($pending_action["type"] == "blackdie_or_3coins" && $choice == "black_die") {
            $this->giveAward("", ["black_die" => 1], $player_id);
        } else if ($pending_action["type"] == "blackdie_or_3coins" && $choice == "coin") {
            $resources = ["coin" => 3];
        } else if ($pending_action["type"] == "discard_contract") {
            $this->discardContract($choice, $pending_action["type_arg"], $player_id);
        } else if ($pending_action["type"] == "discard_gift") {
            $this->validateGiftAgainstAllowable($choice, $pending_action["type_arg"], $player_id);
            $this->discardGift($choice, false, $player_id);
            if ($pending_action["remaining_count"] == 1) {
                $this->triggerImmediateGifts($player_id);
            }
        }

        if ($resources != null) {
            $this->changePlayerResources($resources, $negate, $pending_action["location"], $player_id);
        }

        $this->checkAndTriggerDiscardGift($pending_action, $player_id);
        $this->checkAndTriggerFulfillContract($pending_action, $player_id);
        //$this->checkAndTriggerFulfillArghun($pending_action, $player_id);
        $this->updateNextPendingAction($drop_by, $pending_action);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function pickContract($contract_id, $replaced_contract_id)
    {
        self::checkAction("pickContract");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $contract = $this->deck->getCard($contract_id);
        $die_value = $this->getCurrentDiceValueOnPlace("contracts", 0);

        if ($pending_action == null)
            throw new BgaVisibleSystemException("pick contract: invalid action");

        if ($pending_action["type"] != "pick_contract")
            throw new BgaVisibleSystemException("pick contract: invalid state");

        if ($contract["location"] != "board" || $contract["location_arg"] > $die_value)
            throw new BgaVisibleSystemException("pick contract: not a valid choice");

        if ($replaced_contract_id != null) {
            $this->discardContract($replaced_contract_id, $pending_action["type_arg"], $player_id);
        }

        if ($contract["location_arg"] > 3) {
            $count = $contract["location_arg"] == 4 ? 1 : 2;
            $this->addToPendingTable("camel_coin", "contracts", '', $count, "board_spot_contract_award_" . $contract["location_arg"], $player_id);
        }

        $this->deck->moveCard($contract_id, "hand", $player_id);

        if ($this->deck->countCardInLocation("hand", $player_id) > 2)
            throw new BgaVisibleSystemException("pick contract: no space for another contract");

        self::notifyAllPlayers("contract", clienttranslate('${player_name} picks a contract'), array("player_id" => $player_id,
                "player_name" => $this->getPlayerName($player_id), "is_new" => false, "contract_id" => $contract_id)
        );
        if ($pending_action["remaining_count"] == 1) {
            $this->slideRemainingContracts("pickContract");
        }
        $this->updateNextPendingAction(-1, $pending_action);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function skipContract()
    {
        self::checkAction("skipContract");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $this->updateNextPendingAction(-10, $pending_action);
        $this->slideRemainingContracts("skipContract");
        $this->gamestate->nextState("continue");
    }

    function fulfillContract($contract_id)
    {
        self::checkAction("fulfillContract");
        $player_id = self::getActivePlayerId();
        $contract = $this->deck->getCard($contract_id);
        self::setGameStateValue("can_undo", 1);

        if ($contract == null || $contract["location"] != "hand" || $contract["location_arg"] != $player_id)
            throw new BgaVisibleSystemException(self::_("fulfill contract: no contract or does not belong to you"));

        $contract_data = $this->contract_types[$contract["type"]];
        $fulfill_notif_type = $this->isAutoCostAward($contract_data["award"]) ? "fulfillContract" : "message";

        if ($this->validateCost($contract_data["cost"], $player_id) == false)
            throw new BgaUserException (self::_("You don't have the resources to fulfill this contract"));

        $this->deck->moveCard($contract_id, "complete", $player_id);
        $this->changePlayerResources($contract_data["cost"], true, "contract_" . $contract_id, $player_id);
        $this->giveAward("contract_" . $contract_id, $contract_data["award"], $player_id);
        self::notifyAllPlayers($fulfill_notif_type, clienttranslate('${player_name} fulfills contract ${contract_type}'), array("player_id" => $player_id,
            "player_name" => self::getActivePlayerName(), "contract_id" => $contract_id, "resources_awarded" => false, "contract_type" => $contract_data["type"]));
        self::incStat(1, "contracts_fulfilled", $player_id);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function fulfillArghun($city_card_id) {
        self::debug("before_php");
        self::checkAction("fulfillArghun");
        self::debug("after_php");
        $player_id = self::getActivePlayerId();
        $city_card_piece_db = self::getObjectFromDB("SELECT piece_id id, piece_type type, piece_type_arg type_arg, piece_player_id player_id, piece_location location, piece_location_arg location_arg
                FROM piece WHERE piece_id = {$city_card_id}");
        self::setGameStateValue("can_undo", 1);

        if ($city_card_piece_db == null || $city_card_piece_db['player_id'] != $player_id || $city_card_piece_db['location'] != 'player_mat')
            throw new BgaVisibleSystemException(self::_("fulfill city_card: no city_card or does not belong to you"));

        $city_card_type = $this->city_card_types[$city_card_piece_db["type_arg"]];
        $this->giveCityAward($city_card_type, [['die_value' => '6']], $player_id);
        self::DbQuery("UPDATE piece SET piece_location = 'box' WHERE piece_id = '{$city_card_piece_db['id']}'");
        self::setGameStateValue("can_arghun_use_city_card", 0);

        self::notifyAllPlayers("fulfillArghun", clienttranslate('${player_name} uses city card ${city_card_type} with a 6 as a bonus action'), array("player_id" => $player_id,
            "player_name" => self::getActivePlayerName(), "city_card_type" => $city_card_type, "resources_awarded" => false, "city_card_id" => $city_card_id));
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function fulfillGift($gift_id, $board_id)
    {
        self::checkAction("fulfillGift");
        $player_id = self::getActivePlayerId();
        $gift = $this->deck->getCard($gift_id);
        $gift_data = $this->gift_types[$gift["type_arg"]];

        $this->validateGiftAgainstAllowable($gift_id, '', $player_id);
        $this->useGift($gift_id, true, $player_id);
        if ($gift["type_arg"] == 10 && $this->gamestate->state()["name"] == "playerTravel") {
            $pending_action = $this->getNextPendingAction($player_id);
            $success = $this->placeTradingPost($board_id, false, $player_id);
            if ($success) {
                $this->updateTravelPendingActionMarkTradePost($pending_action, $board_id, $player_id);
            }        //award gifts after movements if any
        } else if ($gift["type_arg"] == 10)      //10 == place trading post gift
        {
            throw new BgaUserException(self::_("You can only use this gift while traveling"));
        } else {
            $this->awardGift($gift_id, $gift_data, $player_id);
        }
        //TODO - inc stat here?
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function travel($figure_id, $dst_id)
    {
        self::checkAction("travel");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $figure = self::getObjectFromDB("SELECT piece_player_id player_id, piece_location location, piece_location_arg location_arg FROM piece WHERE piece_id = {$figure_id} AND piece_type = 'figure'");

        if ($pending_action == null || $pending_action["type"] != "travel")
            throw new BgaVisibleSystemException("travel: invalid state");

        if ($figure == null || $figure["player_id"] != $player_id)
            throw new BgaVisibleSystemException("travel: invalid figure");

        $matching_edge = $this->findMatchingBoardEdge($figure["location_arg"], $dst_id, $player_id);

        if ($matching_edge == null)
            throw new BgaVisibleSystemException("travel: invalid destination");

        if ($this->validateCost($matching_edge["cost"], $player_id) == false)
            throw new BgaUserException(self::_("You don't have the resources to travel across this path"));

        self::notifyAllPlayers("travel", clienttranslate('${player_name} travels to ${map_name}'), array("player_id" => $player_id,
            "player_name" => self::getActivePlayerName(), "figure_id" => $figure_id, "map_name" => $this->board_map[$dst_id]["name"], "dst_id" => $dst_id));

        $this->changePlayerResources($matching_edge["cost"], true, "map_node_" . $dst_id, $player_id);
        self::DbQuery("UPDATE piece SET piece_location_arg = {$dst_id} WHERE piece_id = {$figure_id}");

        $this->placeTradingPostAndGiveAward($dst_id, $pending_action, $player_id);
        $this->checkAndTriggerFulfillContract($pending_action, $player_id);
        $this->updateNextPendingAction(-1, $pending_action);
        self::incStat(1, "travel_movements", $player_id);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function skipTravel()
    {
        self::checkAction("skipTravel");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $pending_action["remaining_count"] = 1;
        $figures = self::getObjectListFromDB("SELECT piece_location_arg FROM piece WHERE piece_type = 'figure' AND piece_player_id = '{$player_id}'");
        foreach ($figures as $figure) {
            $this->placeTradingPostAndGiveAward($figure["piece_location_arg"], $pending_action, $player_id);
        }
        $this->checkAndTriggerFulfillContract($pending_action, $player_id);
        $this->checkAndTriggerFulfillArghun($pending_action, $player_id);
        $this->deletePendingAction($pending_action["pending_id"]);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function skipChooseCityAward()
    {
        self::checkAction("skipChooseCityAward");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);

        if ($pending_action == null || $pending_action["type"] != "city_card")
            throw new BgaVisibleSystemException("no city card action pending");

        $this->deletePendingAction($pending_action["pending_id"]);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function activateMultipleCityCardType30($payment_details, $city_card_info, $pending_action, $player_id)
    {
        $payment_details = $this->convertTwoDiffGoods($payment_details);
        if ($payment_details == null || $this->validateCost($payment_details, $player_id) == false)
            throw new BgaUserException(self::_("Not enough resources to pay this cost"));

        $this->changePlayerResources($payment_details, true, "city_card_" . $pending_action["type_arg"], $player_id);
        self::DbQuery("UPDATE pending_action SET pending_remaining_count = pending_remaining_count + 1 WHERE pending_type = 'travel' AND pending_player_id = '{$player_id}'");
        if ($this->getNumberOfTimesCostSatisfied($city_card_info["cost"], $player_id) > 0) {
            $this->updateNextPendingAction(-1, $pending_action);
        } else {
            $this->deletePendingAction($pending_action["pending_id"]);
        }
    }

    function activateMultipleCityCard($num_times, $payment_details)
    {
        self::checkAction("activateMultipleCityCard");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $city_card_info = $this->getBoardSpot("city_card", $pending_action["type_arg"], null);
        $location = "city_card_" . $city_card_info["type"];

        if ($pending_action == null || $pending_action["type"] != "city_card")
            throw new BgaVisibleSystemException("activate city is in invalid state");

        if ($pending_action["remaining_count"] < $num_times)
            throw new BgaVisibleSystemException("cannot activate so many times");

        if ($city_card_info["type"] == 30)            //assume payment will go through and give reward.  player can undo if stuck. only one at a time
        {
            $this->activateMultipleCityCardType30($payment_details, $city_card_info, $pending_action, $player_id);
        } else {
            $cost = $city_card_info["kind"] == "choice" ? $city_card_info["choice"][$payment_details]["cost"] : $city_card_info["cost"];
            $award = $city_card_info["kind"] == "choice" ? $city_card_info["choice"][$payment_details]["award"] : $city_card_info["award"];
            $modified_costs = array_map(function ($c) use ($num_times) {
                return $c * $num_times;
            }, $cost);
            $modified_award = array_map(function ($a) use ($num_times) {
                return $a * $num_times;
            }, $award);

            if ($this->validateCost($modified_costs, $player_id) == false)
                throw new BgaUserException(self::_("Not enough resources to activate card this # of times"));

            $this->changePlayerResources($modified_costs, true, $location, $player_id);
            $this->giveAward($location, $modified_award, $player_id);
            $this->deletePendingAction($pending_action["pending_id"]);
        }

        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function activateExchangeCityCard($exchange_type)
    {
        self::checkAction("activateExchangeCityCard");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $city_card_info = $this->getBoardSpot("city_card", $pending_action["type_arg"], null);
        $cost = $city_card_info["cost"];
        $reward = $city_card_info["award"];

        if ($exchange_type == "award_to_cost") {
            $cost = $city_card_info["award"];
            $reward = $city_card_info["cost"];
        }

        if ($this->validateCost($cost, $player_id) == false)
            throw new BgaUserException(self::_("You don't have the resources of this exchange"));

        $this->updateNextPendingAction(-1, $pending_action);
        $this->changePlayerResources($cost, true, 'city_card_' . $pending_action["type_arg"], $player_id);
        $this->changePlayerResources($reward, false, 'city_card_' . $pending_action["type_arg"], $player_id);

        if (array_key_exists("vp", $cost)) {
            self::incStat(-1 * $cost["vp"], "city_card_points", $player_id);
        }
        if (array_key_exists("vp", $reward)) {
            self::incStat($reward["vp"], "city_card_points", $player_id);
        }

        if (array_key_exists("choice_of_good", $cost)) {
            $this->addToPendingTable("choice_of_good", "pay", "", 1, 'city_card_' . $pending_action["type_arg"], $player_id);
        } else if (array_key_exists("choice_of_good", $reward)) {
            $this->addToPendingTable("choice_of_good", "", "", 1, 'city_card_' . $pending_action["type_arg"], $player_id);
        }

        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function triggerOtherCityBonus($city_bonus_type_arg)
    {
        self::checkAction("triggerOtherCityBonus");
        $player_id = $this->getRequestingPlayerId();
        $pending_action = $this->getNextPendingAction($player_id);
        $pending_action_id = $pending_action["pending_id"];

        $num_trading_posts_on_city_bonuses = sizeof($this->getCityBonuses($player_id));
        if ($num_trading_posts_on_city_bonuses > 1 && $city_bonus_type_arg == $pending_action["type_arg"])
            throw new BgaVisibleSystemException("invalid state:  must select a different city bonus");

        self::DbQuery("UPDATE pending_action SET pending_type_arg = CONCAT(pending_type_arg, ',', $city_bonus_type_arg) WHERE pending_id = {$pending_action_id}");
        $board_id = self::getUniqueValueFromDB("SELECT piece_location_arg FROM piece WHERE piece_type = 'city_bonus' AND piece_type_arg = {$city_bonus_type_arg} LIMIT 1");
        $this->awardBonus("city_bonus", $this->city_bonus_types[$city_bonus_type_arg], $board_id, false, $player_id);
        $this->updateNextPendingAction(-1, $pending_action);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function skipTriggerOtherCityBonus()
    {
        self::checkAction("skipTriggerOtherCityBonus");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);

        if ($pending_action == null)
            throw new BgaVisibleSystemException("invalid state no city card action pending");

        $this->deletePendingAction($pending_action["pending_id"]);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function moveTradingPost($trading_post_id)
    {
        self::checkAction("moveTradingPost");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);

        if ($pending_action == null || $pending_action["type"] != "move_trading_post")
            throw new BgaVisibleSystemException("invalid state cannot move trading post");

        $trading_post = self::getObjectFromDB("SELECT piece_player_id, piece_location FROM piece WHERE piece_id = {$trading_post_id}");
        if ($trading_post == null || $trading_post["piece_location"] != "board" || $trading_post["piece_player_id"] != $player_id)
            throw new BgaVisibleSystemException("invalid state cannot move selected trading post");

        self::DbQuery("UPDATE piece SET piece_location = 'player_mat' WHERE piece_id = {$trading_post_id}");  //temp move trading post back to player mat
        $this->placeTradingPostAndGiveAward($pending_action["type_arg"], $pending_action, $player_id);
        $this->updateNextPendingAction(-1, $pending_action);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function skipMoveTradingPost()
    {
        self::checkAction("skipMoveTradingPost");
        $player_id = self::getActivePlayerId();
        $pending_action = $this->getNextPendingAction($player_id);

        if ($pending_action == null || $pending_action["type"] != "move_trading_post")
            throw new BgaVisibleSystemException("invalid state cannot move trading post");

        $this->deletePendingAction($pending_action["pending_id"]);
        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function usePlayerPiece($piece_id)
    {
        self::checkAction("usePlayerPiece");
        $player_id = $this->getRequestingPlayerId();
        $piece = self::getObjectFromDB("SELECT piece_type, piece_location, piece_player_id FROM piece WHERE piece_id = '{$piece_id}'");

        if ($piece["piece_player_id"] != $player_id)
            throw new BgaVisibleSystemException("invalid state: piece belongs to other player");

        if ($piece["piece_location"] != "player_mat")
            throw new BgaVisibleSystemException("invalid state: piece not on player mat");

        if ($piece["piece_type"] == "1x_gift") {
            $pending_action = $this->getNextPendingAction($player_id);
            if ($pending_action["type"] != "discard_gift")
                throw new BgaVisibleSystemException("invalid state: can only use when discarding gift");

            $this->updateNextPendingAction(-1, $pending_action);
            if ($pending_action["remaining_count"] == 1) {
                $this->triggerImmediateGifts($player_id);
            }
            self::DbQuery("UPDATE piece SET piece_location = 'box' WHERE piece_id = '{$piece_id}'");
            self::notifyAllPlayers("boxPiece", '', array("player_id" => $player_id, "piece_type" => "1x_gift", "piece_id" => $piece_id, "location" => "box"));
        }

        $this->gamestate->nextState($this->getNextTransitionBasedOnPendingActions($player_id));
    }

    function pass()
    {
        self::checkAction("pass");
        self::notifyAllPlayers("message", clienttranslate('${player_name} passes'), array("player_name" => self::getActivePlayerName()));
        $this->gamestate->nextState("pass");
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////
    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
    function argPickCharacter()
    {
        $characters = self::getObjectListFromDB("SELECT pending_type_arg character_type FROM pending_action WHERE pending_type = 'character'");
        return array(
            'characters' => $characters
        );
    }

    function argPlayerDieCompensation()
    {
        $compensation_amounts = [];
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $die_totals = self::getUniqueValueFromDB("SELECT sum(die_value) FROM die WHERE die_player_id = {$player_id}");
            if (15 - $die_totals > 0) {
                $compensation_amounts[$player_id] = 15 - $die_totals;
            }
        }
        return array(
            'compensation_amount' => $compensation_amounts
        );
    }

    function argPlayerBonus()
    {
        $pending_bonuses = $this->getPendingBonuses();
        $pending_actions = $this->getNextPendingActions();
        return array(
            'pending_bonuses' => $pending_bonuses,
            'pending_actions' => $pending_actions
        );
    }

    function argPlayerGunjBonus()
    {
        $pending_actions = $this->getNextPendingActions();
        return array(
            'pending_bonuses' => [],
            'pending_actions' => $pending_actions
        );
    }

    function argPlayerTurn()
    {
        $player_id = self::getActivePlayerId();
        $num_dice_left = self::getUniqueValueFromDB("SELECT count(die_id) FROM die WHERE die_player_id = {$player_id} AND die_location = 'player_mat'");
        $main_action_available = !self::getGameStateValue('performed_main_action') && $num_dice_left > 0;
        $description = $main_action_available ? clienttranslate("must perform an action or bonus action") : clienttranslate("may pass or perform a bonus action");

        return array(
            'description' => $description,
            'main_action_available' => $main_action_available,
            'only_remaining_player' => $this->isOnlyRemainingPlayer($player_id),
            'can_buy_black_die' => !self::getGameStateValue('black_die_bought') && $this->getNextAvailableBlackDie() != null,
            'can_undo' => self::getGameStateValue("can_undo"),
            'can_arghun_use_personal_city_card' => self::getGameStateValue('can_arghun_use_city_card'),
        );
    }

    function argPlayerChooseResource()
    {
        $player_id = self::getActivePlayerId();
        return array(
            'action' => $this->getNextPendingAction($player_id),
            'can_undo' => self::getGameStateValue("can_undo"),
        );
    }

    function argPlayerChooseCityCardAward()
    {
        self::debug("ARGCHOOSECITYAWARD");
        $player_id = self::getActivePlayerId();
        $next_action = $this->getNextPendingAction($player_id);
        $die_value = $this->getCurrentDiceValueOnPlace("city_card", $next_action["type_arg"]);
        $card_type = $next_action["type_arg"];
        $can_skip = ($this->city_card_types[$card_type]["kind"] != "multiple" || $card_type == 30) && $next_action["remaining_count"] != $die_value;
        self::debug("ARGCHOOSECITYAWARDEXIT");
        return array(
            'card_type' => $card_type,
            'num_remaining' => $next_action["remaining_count"],
            'can_skip' => $can_skip,
            'can_undo' => self::getGameStateValue("can_undo"),
        );
    }

    function argPlayerPickContract()
    {
        $player_id = self::getActivePlayerId();
        $next_action = $this->getNextPendingAction($player_id);
        $die_value = $this->getCurrentDiceValueOnPlace("contracts", 0);
        return array(
            'die_value' => $die_value,
            'valid_contract_ids' => $next_action["type_arg"],
            'num_remaining' => $next_action["remaining_count"],
            'can_undo' => self::getGameStateValue("can_undo"),
            'can_skip' => $next_action["remaining_count"] == 1,
        );
    }

    function argPlayerTravel()
    {
        $player_id = self::getActivePlayerId();
        $next_action = $this->getNextPendingAction($player_id);
        return array(
            'num_remaining' => $next_action["remaining_count"],
            'can_undo' => self::getGameStateValue("can_undo"),
        );
    }

    function argPlayerTriggerOtherCityBonus()
    {
        $player_id = self::getActivePlayerId();
        $next_action = $this->getNextPendingAction($player_id);
        return array(
            'can_skip' => true,
            'trading_post_only' => $next_action["type"] == "trigger_city_bonus_having_trading_post",
            'offlimit_city_bonuses' => $next_action["type_arg"]
        );
    }

    function argPlayerMoveTradingPost()
    {
        $player_id = self::getActivePlayerId();
        $next_action = $this->getNextPendingAction($player_id);
        return array(
            'can_skip' => true,
            'map_node_id' => $next_action["type_arg"],
        );
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    function stGamePickCharacter()
    {
        $players = self::loadPlayersBasicInfos();
        // go back to where we were before
        $valid_character_types = array_filter($this->character_types, array("MarcoPoloExpansions", "onlyGetExpCharacters")); // rzdTODO: change it back
        if (self::getGameStateValue("expert_variant") == 0)     //use default values
        {
            foreach ($players as $player_id => $player) {
                foreach ($valid_character_types as $character_id => $character) {
                    if (array_key_exists("default_player", $character) && $character["default_player"] == $player["player_no"]) {
                        $this->presetupCharacter($character["type"]);
                        $this->setupCharacter($character["type"], $player_id);
                    }
                }
            }
            $this->gamestate->nextState("setupGoals");
        } else {
            $characters_randomly_drawn_already = self::getUniqueValueFromDB("SELECT pending_id FROM pending_action LIMIT 1");
            $next_player_id = self::getPlayerBefore(self::getActivePlayerId());
            $last_player_id = $this->getLastPlayerId();
            if ($characters_randomly_drawn_already == null) {
                $count = sizeof($players) + 1;            //n+1 characters to select from
                shuffle($valid_character_types);
                for ($i = 0; $i < $count; $i++) {
                    $random_character_type = $valid_character_types[$i]["type"];
                    $this->presetupCharacter($random_character_type);
                    self::DbQuery("INSERT INTO pending_action(pending_type, pending_type_arg) VALUES ('character', {$random_character_type})");
                }
                $next_player_id = $last_player_id;
            }

            if ($characters_randomly_drawn_already == null || $next_player_id != $last_player_id)      //loop till hit last player again
            {
                $this->gamestate->changeActivePlayer($next_player_id);
                $this->gamestate->nextState("pickCharacter");
            } else {
                $this->gamestate->nextState("setupGoals");
            }
        }
    }

    function stGamePickGoals()
    {
        $players = self::loadPlayersBasicInfos();
        $first_player_id = self::getNextPlayerTable()[0];
        $this->gamestate->changeActivePlayer($first_player_id);       //change back to first player
        self::DbQuery("DELETE FROM pending_action WHERE pending_type = 'character'");     //clear character selection from pending action

        if (self::getGameStateValue("expert_variant") == 1)     //pick goal cards
        {
            $this->dealGoalCards($players, 4);
            foreach ($players as $player_id => $player) {
                $this->giveExtraTime($player_id);
            }
            $this->gamestate->setAllPlayersMultiactive();
            $this->gamestate->nextState("pickGoals");
        } else {
            $this->dealGoalCards($players, 2);
            $this->gamestate->nextState("start");
        }
    }

    function stGameNewRound()
    {
        self::setGameStateValue("current_round", self::getGameStateValue("current_round") + 1);
        self::setGameStateValue("first_move_of_round", 1);
        self::setGameStateValue("performed_main_action", 0);
        self::setGameStateValue("black_die_bought", 0);
        self::setGameStateValue("can_arghun_use_city_card", 1);
        self::notifyAllPlayers("message", clienttranslate("A new round begins!"), array());
        $this->cleanUpDice();

        if (self::getGameStateValue("current_round") != 1 || $this->deck->countCardInLocation("board") == 0) {
            $this->placeNewContractsOutOnBoard(6, "round_" . self::getGameStateValue("current_round"));
        } else {
            $this->slideRemainingContracts("round_1");
        }

        $this->givePlayerRoundBonuses();
        $player_ids_with_choices = self::getObjectListFromDB("SELECT pending_player_id FROM pending_action WHERE pending_type = 'bonus' GROUP BY pending_player_id");
        if (sizeof($player_ids_with_choices) > 0) {
            $player_ids_with_choices = array_map(function ($p) {
                return $p["pending_player_id"];
            }, $player_ids_with_choices);        //map to static list of player ids
            foreach ($player_ids_with_choices as $player_id) {
                $this->giveExtraTime($player_id);
            }
            $this->gamestate->setPlayersMultiactive($player_ids_with_choices, "rollDice");
            $this->gamestate->nextState("collectBonus");
        } else {
            $this->gamestate->nextState("rollDice");
        }
    }

    function stPlayerBonus()
    {
        $active_players = $this->gamestate->getActivePlayerList();
        foreach ($active_players as $player_id) {
            if (sizeof($this->getPendingBonusesByPlayerId($player_id)) == 0 && $this->getNextPendingAction($player_id) == null) {
                $this->gamestate->setPlayerNonMultiactive($player_id, "done");
            }
        }
    }

    function stGamePlayerGunjBonusStart()
    {
        $active_player_id = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        $player_ids = [];
        foreach ($players as $player_id => $player) {
            if ($player_id != $active_player_id) {
                //todo give extra time?
                array_push($player_ids, $player_id);
            }
        }
        $this->gamestate->setPlayersMultiactive($player_ids, "", true);
        $this->gamestate->nextState("");
    }

    function stPlayerGunjBonus()
    {
        $active_players = $this->gamestate->getActivePlayerList();
        foreach ($active_players as $player_id) {
            if ($this->getNextPendingAction($player_id) == null) {
                $this->gamestate->setPlayerNonMultiactive($player_id, "done");
            }
        }
    }

    function stGamePlayerGunjBonusFinish()
    {
        $actions = $this->getNextPendingActions();
        foreach ($actions as $action) {
            if ($action["type"] == "switch_to_gunj_bonus") {
                $this->gamestate->changeActivePlayer($action["pending_player_id"]);
                $this->deletePendingAction($action["pending_id"]);
            }
        }
        $this->setNewUndoPoint();
        $this->gamestate->nextState("");
    }

    function stGameRollAllDice()
    {
        $running_die_total = [];

        //PATCH - for in-flight games clean up does this as well, can remove once all games are off old version//
        self::DbQuery("UPDATE die SET die_player_id = NULL, die_location = 'avail_black_die', die_location_arg = 0, die_location_height = 0, die_value = 1 WHERE die_type = 'black'");
        self::DbQuery("UPDATE die SET die_location = 'player_mat', die_location_arg = '', die_location_height = 0, die_value = 1 WHERE die_type = 'regular' OR die_type = 'white'");
        //TODO - remove eventually

        $raschid_player_id = $this->getPlayerIdByCharacterType(2);
        $dice = self::getCollectionFromDB("SELECT die_id, die_type, die_player_id, die_location, die_location_arg, die_location_height, die_value FROM die WHERE (die_type <> 'black' AND die_type <> 'fixed')");
        $rolled_dice = [];
        foreach ($dice as $die_id => &$die) {
            if ($die["die_location"] == "player_mat") {
                $value = $this->rollDie($die_id, $die["die_player_id"]);
                $die["die_value"] = $value;
                if (array_key_exists($die["die_player_id"], $running_die_total) == false) {
                    $running_die_total[$die["die_player_id"]] = 0;
                }
                $running_die_total[$die["die_player_id"]] += $value;
            }
            if ($die["die_player_id"] != $raschid_player_id) {
                $rolled_dice[$die_id] = $die;
            }
        }

        $comp_player_ids = [];
        foreach ($running_die_total as $player_id => $total) {
            if ($total < 15 && $player_id != $raschid_player_id) {
                array_push($comp_player_ids, $player_id);
            }
        }

        self::notifyAllPlayers("updateDice", clienttranslate('All players re-roll their dice'), array('dice' => $rolled_dice, "shake" => true));

        if (sizeof($comp_player_ids) > 0) {
            $this->gamestate->setPlayersMultiactive($comp_player_ids, "next", true);
            $this->gamestate->nextState("collectCompensation");
        } else {
            $this->gamestate->nextState("next");
        }
    }

    function stGameNext()
    {
        $player_ids_with_dice_left = self::getObjectListFromDB("SELECT DISTINCT die_player_id FROM die WHERE die_location = 'player_mat'");
        $player_ids_with_dice_left = array_map(function ($p) {
            return $p["die_player_id"];
        }, $player_ids_with_dice_left);        //map to static list of player ids

        if (self::getGameStateValue("first_move_of_round") == 1) {
            $start_player_id = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE hourglass = 1");
            $this->gamestate->changeActivePlayer($start_player_id);
            self::setGameStateValue("first_move_of_round", 0);
            $this->setNewUndoPoint();
            $this->giveExtraTime($start_player_id);
            $this->gamestate->nextState("nextPlayer");
        } else if (sizeof($player_ids_with_dice_left) > 0) {
            $this->activeNextPlayer();
            while (in_array($this->getActivePlayerId(), $player_ids_with_dice_left) == false) {
                $this->activeNextPlayer();
            }
            self::setGameStateValue("performed_main_action", 0);
            self::setGameStateValue("black_die_bought", 0);
            if ($this->deck->countCardInLocation("board") == 0)     //deal new contracts if none left
            {
                $this->placeNewContractsOutOnBoard(2, "contract_special_pile");
            }
            $this->setNewUndoPoint();
            $this->giveExtraTime($this->getActivePlayerId());
            $this->gamestate->nextState("nextPlayer");
        } else if (self::getGameStateValue("current_round") < 5) {
            $this->gamestate->nextState("nextRound");
        } else {
            $this->gamestate->nextState("gameover");
        }
    }

    function stGameover()
    {
        $players = self::loadPlayersBasicInfos();

        $summary_table = array();
        $summary_player_names = array(array('str' => '${player_name}', 'args' => array('player_name' => ""), 'type' => 'header'));
        $summary_base_points = array(array('type' => '', 'str' => clienttranslate("Base points"), 'args' => array()));
        $summary_bejing_points = array(array('type' => '', 'str' => clienttranslate("Beijing points"), 'args' => array()));
        $summary_resource_points = array(array('type' => '', 'str' => clienttranslate("Remaining resources points"), 'args' => array()));
        $summary_goal_points = array(array('type' => '', 'str' => clienttranslate("Matching cities goal points"), 'args' => array()));
        $summary_unique_goal_points = array(array('type' => '', 'str' => clienttranslate("Multiple unique cities goal points"), 'args' => array()));
        $summary_most_contract_points = array(array('type' => '', 'str' => clienttranslate("Most contracts points"), 'args' => array()));
        $summary_coin_points = array(array('type' => '', 'str' => clienttranslate("Remaining coins points"), 'args' => array()));
        $summary_total_points = array(array('type' => '', 'str' => clienttranslate("Total points"), 'args' => array()));

        foreach ($players as $player_id => $player) {
            $base_points = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = {$player_id}");
            $coin_points = floor(self::getUniqueValueFromDB("SELECT coin FROM player WHERE player_id = {$player_id}") / 10);
            $bejing_points = 0;
            $goods_points = 0;
            $bejing_post_position = self::getUniqueValueFromDB("SELECT piece_location_position FROM piece WHERE piece_type = 'trading_post' AND piece_location = 'board' AND piece_location_arg = '8' AND piece_player_id = {$player_id}");
            if ($bejing_post_position !== null) {
                $bejing_points = [10, 7, 4, 1, 0][$bejing_post_position];
                $goods = $this->getPlayerResources($player_id);
                $goods_points = floor(($goods["pepper"] + $goods["silk"] + $goods["gold"]) / 2);
            }
            $goal_reached_points = $this->computeReachedGoalCardPoints($player_id);
            $goal_uniq_cities_points = $this->computeUniqueCityGoalCardPoints($player_id);
            $most_contract_points = $this->computeMostContractsPoints($player_id);
            $extra_points = $bejing_points + $goods_points + $goal_reached_points + $goal_uniq_cities_points + $most_contract_points + $coin_points;
            $total_points = $base_points + $extra_points;

            $goal_cards = $this->deck->getCardsInLocation("goal_hand", $player_id);
            self::DbQuery("UPDATE player SET player_score = {$total_points}, player_score_aux = camel WHERE player_id = {$player_id}");
            self::notifyAllPlayers("revealGoalCard", clienttranslate('${player_name} scores ${extra_points} end game points'), array("player_id" => $player_id,
                "player_name" => $this->getPlayerName($player_id), "extra_points" => $extra_points, "cards" => $goal_cards
            ));

            self::setStat($bejing_points, "beijing_points", $player_id);
            self::setStat($goods_points, "resource_points", $player_id);
            self::setStat($most_contract_points, "most_contract_points", $player_id);
            self::setStat($goal_reached_points + $goal_uniq_cities_points, "goal_card_points", $player_id);
            self::setStat($coin_points, "coin_points", $player_id);
            self::setStat($total_points, "total_points", $player_id);

            $summary_player_names[] = array('str' => '${player_name}', 'args' => array('player_name' => $player['player_name']), 'type' => 'header');
            $summary_base_points[] = $base_points;
            $summary_bejing_points[] = $bejing_points;
            $summary_resource_points[] = $goods_points;
            $summary_goal_points[] = $goal_reached_points;
            $summary_unique_goal_points[] = $goal_uniq_cities_points;
            $summary_most_contract_points[] = $most_contract_points;
            $summary_coin_points[] = $coin_points;
            $summary_total_points[] = array('str' => "<span style='font-weight:bold;'>${total_points}</span>", 'args' => array('total_points' => $total_points));
        }

        $summary_table[] = $summary_player_names;
        $summary_table[] = $summary_base_points;
        $summary_table[] = $summary_bejing_points;
        $summary_table[] = $summary_resource_points;
        $summary_table[] = $summary_goal_points;
        $summary_table[] = $summary_unique_goal_points;
        $summary_table[] = $summary_most_contract_points;
        $summary_table[] = $summary_coin_points;
        $summary_table[] = $summary_total_points;

        $this->notifyAllPlayers("tableWindow", '', array(
            "id" => 'finalScoring',
            "title" => clienttranslate("Final score"),
            "table" => $summary_table,
            "closing" => clienttranslate("Close")
        ));

        $winning_char_types = self::getObjectListFromDB("SELECT character_type FROM player where (player_score * 100 + player_score_aux) = (select max(player_score * 100 + player_score_aux) from player)");
        if (sizeof($winning_char_types) == 1) {
            self::setStat($winning_char_types[0]["character_type"], "winning_character");
        }
        $this->gamestate->nextState("");
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                case "pickCharacter":
                    $this->gamestate->nextState("");
                    break;
                case "playerTurn":
                    self::DbQuery("UPDATE die SET die_location = 'zombie_mat' WHERE die_location = 'player_mat' AND die_player_id = {$active_player}");
                    $this->gamestate->nextState("pass");
                    break;
                case "playerTravel":
                case "playerChooseResource":
                case "playerChooseCityCardAward":
                case "playerPickContract":
                case "playerMoveTradingPost":
                case "playerTriggerOtherCityBonus":
                    self::DbQuery("DELETE from pending_action WHERE pending_player_id = {$active_player}");
                    $this->gamestate->nextState("continue");
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            switch ($statename) {
                case "pickGoals":
                    $this->deck->goal_deck->moveAllCardsInLocation("goal_hand", "goal_discard", $active_player);
                    $this->gamestate->setPlayerNonMultiactive($active_player, 'done');
                    break;
                case "playerBonus":
                case "playerDieCompensation":
                    $this->gamestate->setPlayerNonMultiactive($active_player, 'done');
                    break;
            }

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//
    }
}
