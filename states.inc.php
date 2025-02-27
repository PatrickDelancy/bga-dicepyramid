<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DicePyramid implementation : © Patrick Delancy <patrick.delancy@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * DicePyramid game states description
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
                      method on both client side (Javacript: this.checkAction) and server side (PHP: $this->checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

if (!defined('ST_GAME_SETUP')) {
    define('ST_GAME_SETUP', 1);
    define('ST_SETUP_PYRAMID', 2);

    define('ST_PLAYER_BEFORE_INITIAL_ROLL', 10);
    define('ST_INITIAL_DICE_ROLL', 11);
    define('ST_PLAYER_TURN', 12);
    define('ST_PLAYER_RESOLVE_DICE', 13);
    define('ST_PLAYER_USE_RELIC', 14);
    define('ST_PLAYER_DISCARD_RELIC_TO_LIMIT', 15);
    define('ST_PLAYER_DISCARD_RELIC_FOR_NEW_TURN', 16);
    define('ST_END_TURN', 19);

    define('ST_GAME_END', 99);
}

$machinestates = [

    ST_GAME_SETUP => [
        "name" => "gameSetup",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => ST_SETUP_PYRAMID]
    ],
    ST_SETUP_PYRAMID => [
        "name" => "setupPyramid",
        "type" => "game",
        "action" => "stSetupPyramid",
        "args" => "argSetupPyramid",
        "transitions" => ["" => ST_PLAYER_BEFORE_INITIAL_ROLL]
    ],

    ST_PLAYER_BEFORE_INITIAL_ROLL => [
        "name" => "playerBeforeInitialRoll",
        "description" => clienttranslate('${actplayer} is starting a new turn and must roll the dice'),
        "descriptionmyturn" => clienttranslate('${you} are starting a new turn and must roll the dice'),
        "type" => "activeplayer",
        "args" => "argPlayerBeforeInitialRoll",
        "possibleactions" => [
            "actPlayInitialRoll"
        ],
        "transitions" => [
            "" => ST_INITIAL_DICE_ROLL
        ]
    ],

    ST_INITIAL_DICE_ROLL => [
        "name" => "initialDiceRoll",
        "type" => "game",
        "action" => "stInitialDiceRoll",
        "transitions" => ["" => ST_PLAYER_TURN]
    ],

    ST_PLAYER_TURN => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} can reroll dice, take a room, use a relic or discard a relic'),
        "descriptionmyturn" => clienttranslate('${you} can reroll dice, take a room, use a relic or discard a relic'),
        "descriptionmyturnnorerolls" => clienttranslate('${you} can take a room, use a relic or discard a relic'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => [
            "actRerollDice",
            "actTakeRoom",
            "actPlayRelic",
            "actTurnDiscardRelic",
            "actGiveUp"
        ],
        "transitions" => [
            "roll" => ST_PLAYER_TURN,
            "useRelic" => ST_PLAYER_USE_RELIC,
            "discardRelic" => ST_PLAYER_DISCARD_RELIC_FOR_NEW_TURN,
            "forcedDiscardRelic" => ST_PLAYER_DISCARD_RELIC_TO_LIMIT,
            "endTurn" => ST_END_TURN,
            "endGame" => ST_GAME_END,
        ]
    ],

    ST_PLAYER_USE_RELIC => [
        "name" => "playerPlayingRelic",
        "description" => clienttranslate('${actplayer} must select dice to use the ${relic_name} relic'),
        "descriptionmyturn" => clienttranslate('${you} must select dice to use the ${relic_name} relic'),
        "type" => "activeplayer",
        "args" => "argPlayerPlayingRelic",
        "possibleactions" => [
            "actPlayRelicOnDice",
            "actCancelPlayingRelic"
        ],
        "transitions" => [
            "" => ST_PLAYER_TURN
        ]
    ],

    ST_PLAYER_DISCARD_RELIC_TO_LIMIT => [
        "name" => "playerDiscardRelic",
        "description" => clienttranslate('${actplayer} must discard down to ${maxRelics} relics'),
        "descriptionmyturn" => clienttranslate('${you} must discard down to ${maxRelics} relics'),
        "type" => "activeplayer",
        "args" => "argPlayerDiscardRelic",
        "possibleactions" => [
            "actDiscardRelic"
        ],
        "transitions" => [
            "" => ST_END_TURN
        ]
    ],

    ST_PLAYER_DISCARD_RELIC_FOR_NEW_TURN => [
        "name" => "playerDiscardRelicForNewTurn",
        "description" => clienttranslate('${actplayer} must choose a relic to discard'),
        "descriptionmyturn" => clienttranslate('${you} must choose a relic to discard'),
        "type" => "activeplayer",
        "args" => "argPlayerDiscardRelicForNewTurn",
        "possibleactions" => [
            "actDiscardRelicForNewTurn",
            "actCancelDiscardRelic"
        ],
        "transitions" => [
            "endTurn" => ST_END_TURN,
            "cancel" => ST_PLAYER_TURN
        ]
    ],

    ST_END_TURN => [
        "name" => "endTurn",
        "description" => '',
        "type" => "game",
        "action" => "stEndTurn",
        "updateGameProgression" => true,
        "transitions" => ["endGame" => ST_GAME_END, "nextTurn" => ST_PLAYER_BEFORE_INITIAL_ROLL]
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_GAME_END => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ]

];
