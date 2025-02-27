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
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\DicePyramid;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");
require_once("Players.php");
require_once("Cards.php");
require_once("Dice.php");
require_once("states/SetupPyramid.php");
require_once("states/PlayerBeforeInitialRoll.php");
require_once("states/InitialDiceRoll.php");
require_once("states/PlayerTurn.php");
require_once("states/EndTurn.php");
require_once("states/PlayerPlayingRelic.php");
require_once("states/PlayerDiscardRelic.php");

class Game extends \Table
{
    use PlayersTrait;
    use CardsTrait;
    use DiceTrait;
    use StateGameSetupPyramidTrait;
    use StatePlayerBeforeInitialRollTrait;
    use StateGameInitialDiceRollTrait;
    use StatePlayerTurnTrait;
    use StateGameEndTurnTrait;
    use StatePlayerPlayingRelicTrait;
    use StatePlayerDiscardRelicTrait;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels([
            "rule_powerrelics" => 110,
            "rule_greatpyramid" => 120,
            "rule_faceup" => 130,
        ]);

        $this->cards = $this->getNew("module.common.deck");
        $this->cards->init("card");
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        $totalCards = $this->getGameStateValue('rule_greatpyramid') == 1 ? 15 : 10;
        $completedCards =  ($totalCards - $this->cards->countCardsInLocation('pyramid'));

        return min(100, max(0, ($completedCards / $totalCards) * 100));
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
        //       if ($from_version <= 1404301345)
        //       {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
        //
        //       if ($from_version <= 1405061421)
        //       {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas(): array
    {
        $result = [];

        $result["CARD_DATA"] = CARD_DATA;

        $result["players"] = $this->getCollectionFromDb("SELECT `player_id` `id`, `player_score` `score` FROM `player`");
        foreach ($result["players"] as &$player) {
            $player['id'] = intval($player['id']);
        }

        $pyramidCards = $this->getCardsInLocation('pyramid') ?? [];
        $result["pyramid"] = count($pyramidCards) == 0 ? [] : array_map(fn($c) => $c['flipped'] ? $c : $this->cardIdOnly($c), $pyramidCards);
        $result["relics"] = $this->getCardsInLocation('relics');
        $result["dice"] = $this->getDice();
        $result["maxRelics"] = $this->globals->get('max_relics');
        $result["powerRelics"] = $this->getGameStateValue('rule_powerrelics') == 1;
        $result["greatPyramid"] = $this->getGameStateValue('rule_greatpyramid') == 1;
        $result["faceUp"] = $this->getGameStateValue('rule_faceup') == 1;

        $activeRelicId = $this->getActiveRelicId();
        if ($activeRelicId) {
            $result["activeRelic"] = $this->getCard($activeRelicId);
        }

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "dicepyramid";
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        // TODO: award different number of points based on game variant?

        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        $this->globals->set('max_relics', $this->getGameStateValue('rule_powerrelics') == 1 ? 2 : 5);

        // Dummy content.
        //$this->setGameStateInitialValue("my_first_global_variable", 0);

        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.

        // Dummy content.
        $this->initStat("table", "turns_count", 0);
        $this->initStat("player", "turns_count", 0);
        $this->initStat("player", "rooms_taken", 0);
        $this->initStat("player", "relics_used", 0);
        $this->initStat("player", "relics_used_unforced", 0);
        $this->initStat("player", "relics_discarded", 0);
        $this->initStat("player", "relics_lost", 0);

        $cards = array();
        for ($value = 1; $value <= 15; $value++) {
            $cards[] = array('type' => $value, 'type_arg' => 1, 'nbr' => 1);
        }

        $this->cards->createCards($cards, 'deck');
        $this->rollDice();

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();

        $this->dbSetScore($this->getActivePlayerId(), -30);
    }

    protected function dbSetScore($player_id, $count)
    {
        $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default: {
                        $this->gamestate->nextState("zombiePass");
                        break;
                    }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
