<?php

namespace Bga\Games\DicePyramid;

trait StatePlayerBeforeInitialRollTrait
{
    public function argPlayerBeforeInitialRoll()
    {
        return [];
    }

    public function actPlayInitialRoll()
    {
        $this->gamestate->nextState("");
    }
}
