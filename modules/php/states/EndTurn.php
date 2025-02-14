<?php

namespace Bga\Games\DicePyramid;

trait StateGameEndTurnTrait
{
    public function stEndTurn(): void
    {
        $treasureRoom = $this->getCardsOfTypeInLocation(CARD_2_TREASURE_CHAMBER, 'relics');

        $this->incStat(1, 'turns_count');
        $this->incStat(1, 'turns_count', $this->getActivePlayerId());
        if ($treasureRoom == null || count($treasureRoom) == 0) {
            $this->giveExtraTime($this->getActivePlayerId());
            $this->gamestate->nextState("nextTurn");
        } else {
            $this->dbSetScore($this->getActivePlayerId(), 30);
            $this->gamestate->nextState("endGame");
        }
    }
}
