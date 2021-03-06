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
 * gameoptions.inc.php
 *
 * MarcoPoloExpansions game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in marcopoloexpansions.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
        'name' => totranslate('Expert Variant'),
        'values' => array(
            0 => array( 'name' => totranslate('No'), 'tmdisplay' => totranslate('Beginner') ),
            1 => array( 'name' => totranslate('Yes'), 'tmdisplay' => totranslate('Expert') ),
        )
    ),
    101 => array(
        'name' => totranslate('The New Characters Expansion'),
        'values' => array(
            0 => array( 'name' => totranslate('Disabled'), 'tmdisplay' => '' ),
            1 => array( 'name' => totranslate('Enabled'), 'tmdisplay' => totranslate('The New Characters Expansion'), 'beta' => true ),
        )
    ),
    102 => array(
        'name' => totranslate('Force new characters and gift city bonus'),
        'values' => array(
            0 => array( 'name' => totranslate('Off') ),
            1 => array(
                'name' => totranslate('On'),
                'description' => "ALPHA only - Forces new characters to be drawn on character selection. Forces the Gift bonus city card to appear."
            )
        ),
        'displaycondition' => array(
            array(
                'type' => 'otheroption',
                'id' => 101,
                'value' => 1
            )
        )
    )
);

$game_preferences = array(
    100 => array(
        'name' => totranslate('Highlight goal on board'),
        'values' => array(
            0 => array( 'name' => totranslate('Yes') ),
            1 => array( 'name' => totranslate('No') ),
        )
    ),
    101 => array(
        'name' => totranslate("Confirmation prompts"),
        'values' => array(
            0 => array( 'name' => totranslate('Yes') ),
            1 => array( 'name' => totranslate('No') ),
        ),
    )
);
