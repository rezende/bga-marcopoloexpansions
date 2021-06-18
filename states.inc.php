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
 * states.inc.php
 *
 * MarcoPoloExpansions game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 20)
    ),

    4 => array(
        "name" => "newRound",
        "type" => "game",
        "action" => "stGameNewRound",
        "transitions" => array("rollDice" => 5, "collectBonus" => 7)
    ),

    5 => array(
        "name" => "rollAllDice",
        "type" => "game",
        "action" => "stGameRollAllDice",
        "transitions" => array("collectCompensation" => 8, "next" => 6)
    ),

    6 => array(
        "name" => "next",
        "type" => "game",
        "action" => "stGameNext",
        "transitions" => array("nextPlayer" => 10, "nextRound" => 4, "gameover" => 98)
    ),

    7 => array(
        "name" => "playerBonus",
        "description" => clienttranslate('All players must collect their bonuses'),
        "descriptionmyturn" => clienttranslate('${you} must collect your bonuses'),
        "type" => "multipleactiveplayer",
        "args" => "argPlayerBonus",
        "action" => "stPlayerBonus",
        "possibleactions" => array("triggerBonus", "chooseResource", "triggerOtherCityBonus", "usePlayerPiece"),
        "transitions" => array("done" => 5, "continue" => 7)
    ),

    8 => array(
        "name" => "playerDieCompensation",
        "description" => clienttranslate('All players may receive dice compensation'),
        "descriptionmyturn" => clienttranslate('${you} must pick dice compensation'),
        "type" => "multipleactiveplayer",
        "args" => "argPlayerDieCompensation",
        "possibleactions" => array("pickCompensation"),
        "transitions" => array("done" => 6),
    ),

    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must perform an action or bonus action'),
        "descriptionmyturn" => clienttranslate('${you} must perform an action or bonus action'),
        "descriptionmyturn_bonus" => clienttranslate('${you} must perform a bonus action or pass'),
        "description_bonus" => clienttranslate('${actplayer} must perform a bonus action or pass'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "updateGameProgression" => true,
        "possibleactions" => array(
            "placeDie", "rerollDie", "bumpDie", "buyBlackDie", "changeDice", "fulfillContract", "fulfillGift", "fulfillArghun", "pass", "undo"
        ),
        "transitions" => array(
            "travel" => 11, "chooseResource" => 12, "chooseCityCardAward" => 13, "pickContract" => 14,
            "triggerOtherCityBonus" => 16, "pass" => 6, "continue" => 10
        )
    ),

    11 => array(
        "name" => "playerTravel",
        "description" => clienttranslate('${actplayer} may travel (${num_remaining} steps remaining)'),
        "descriptionmyturn" => clienttranslate('${you} may travel (${num_remaining} steps remaining)'),
        "type" => "activeplayer",
        "args" => "argPlayerTravel",
        "possibleactions" => array("travel", "skipTravel", "fulfillGift", "undo"),
        "transitions" => array("continue" => 10, "travel" => 11, "chooseResource" => 12, "moveTradingPost" => 15, "triggerOtherCityBonus" => 16)
    ),

    12 => array(
        "name" => "playerChooseResource",
        "description" => clienttranslate('${actplayer} must choose resources'),
        "descriptionmyturn" => clienttranslate('${you} must choose resources'),
        "type" => "activeplayer",
        "args" => "argPlayerChooseResource",
        "possibleactions" => array("chooseResource", "undo"),
        "transitions" => array("continue" => 10, "gunj_bonus" => 30, "travel" => 11, "chooseResource" => 12, "chooseCityCardAward" => 13, "pickContract" => 14, "moveTradingPost" => 15, "triggerOtherCityBonus" => 16),
    ),

    13 => array(
        "name" => "playerChooseCityCardAward",
        "description" => clienttranslate('${actplayer} may activate city card up to ${num_remaining} times'),
        "descriptionmyturn" => clienttranslate('${you} may activate city card up to ${num_remaining} times'),
        "type" => "activeplayer",
        "args" => "argPlayerChooseCityCardAward",
        "possibleactions" => array("activateExchangeCityCard", "activateMultipleCityCard", "skipChooseCityAward", "undo"),
        "transitions" => array("continue" => 10, "travel" => 11, "chooseResource" => 12, "chooseCityCardAward" => 13, "triggerOtherCityBonus" => 16),
    ),

    14 => array(
        "name" => "playerPickContract",
        "description" => clienttranslate('${actplayer} may pick a contract (${num_remaining} remaining)'),
        "descriptionmyturn" => clienttranslate('${you} may pick a contract (${num_remaining} remaining)'),
        "type" => "activeplayer",
        "args" => "argPlayerPickContract",
        "possibleactions" => array("pickContract", "skipContract", "undo"),
        "transitions" => array("chooseResource" => 12, "pickContract" => 14, "continue" => 10),
    ),

    15 => array(
        "name" => "playerMoveTradingPost",
        "description" => clienttranslate('Out of trading posts, ${actplayer} may move a trading post'),
        "descriptionmyturn" => clienttranslate('Out of trading posts, ${you} may move a trading post'),
        "type" => "activeplayer",
        "args" => "argPlayerMoveTradingPost",
        "possibleactions" => array("moveTradingPost", "skipMoveTradingPost", "undo"),
        "transitions" => array("continue" => 10, "chooseResource" => 12, "travel" => 11, "moveTradingPost" => 15, "triggerOtherCityBonus" => 16),
    ),

    16 => array(
        "name" => "playerTriggerOtherCityBonus",
        "description" => clienttranslate('${actplayer} must select another city bonus to activate'),
        "descriptionmyturn" => clienttranslate('${you} must select another city bonus to activate'),
        "type" => "activeplayer",
        "args" => "argPlayerTriggerOtherCityBonus",
        "possibleactions" => array("triggerOtherCityBonus", "skipTriggerOtherCityBonus", "undo"),
        "transitions" => array("continue" => 10, "travel" => 11, "chooseResource" => 12, "moveTradingPost" => 15, "triggerOtherCityBonus" => 16),
    ),

    20 => array(
        "name" => "gamePickCharacter",
        "type" => "game",
        "action" => "stGamePickCharacter",
        "transitions" => array("pickCharacter" => 21, "setupGoals" => 22),
    ),

    21 => array(
        "name" => "pickCharacter",
        "description" => clienttranslate('${actplayer} must pick a character'),
        "descriptionmyturn" => clienttranslate('${you} must pick a character'),
        "type" => "activeplayer",
        "args" => "argPickCharacter",
        "possibleactions" => array("pickCharacter"),
        "transitions" => array("" => 20)
    ),

    22 => array(
        "name" => "gamePickGoals",
        "type" => "game",
        "action" => "stGamePickGoals",
        "transitions" => array("pickGoals" => 23, "start" => 4),
    ),

    23 => array(
        "name" => "pickGoals",
        "description" => clienttranslate('All players must pick their goal cards'),
        "descriptionmyturn" => clienttranslate('${you} must pick your goal cards'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("pickGoalCards"),
        "transitions" => array("done" => 4)
    ),

    30 => array(
        "name" => "gamePlayerGunjBonusStart",
        "type" => "game",
        "action" => "stGamePlayerGunjBonusStart",
        "transitions" => array("" => 31),
    ),

    31 => array(
        "name" => "playerGunjBonus",
        "description" => clienttranslate('All players must choose a good'),
        "descriptionmyturn" => clienttranslate('${you} must pick a good'),
        "type" => "multipleactiveplayer",
        "args" => "argPlayerGunjBonus",
        "action" => "stPlayerGunjBonus",
        "possibleactions" => array("chooseResource"),
        "transitions" => array("continue" => 31, "done" => 32),
    ),

    32 => array(
        "name" => "gamePlayerGunjBonusFinish",
        "type" => "game",
        "action" => "stGamePlayerGunjBonusFinish",
        "transitions" => array("" => 10),
    ),

    98 => array(
        "name" => "gameover",
        "type" => "game",
        "action" => "stGameover",
        "transitions" => array("" => 99),
    ),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);
