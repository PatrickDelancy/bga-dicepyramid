<?php

namespace Bga\Games\DicePyramid;

trait StateGameInitialDiceRollTrait
{
    public function stInitialDiceRoll(): void
    {
        $dice = $this->rollDice();
        $this->setDice($dice);
        $this->globals->set('rerolls', 2);
        $this->clearCardsUsed();

        $this->notifyAllPlayers('initialRoll', clienttranslate('Initial roll is ${rolled_values}'), [
            "rolled_values" => implode(', ', array_map(fn($d) => $d['value'], $dice)),
            "dice" => $dice,
        ]);

        $this->gamestate->nextState("");
    }
}
