<?php

namespace Bga\Games\DicePyramid;

trait DiceTrait
{
    public function getDice()
    {
        return $this->globals->get('dice', []);
    }
    public function setDice($dice)
    {
        return $this->globals->set('dice', $dice);
    }

    public function rollDice($idsToRoll = [0, 1, 2, 3, 4])
    {
        $dice = $this->rollDiceWithoutStorage(
            $this->globals->get('dice'),
            $idsToRoll
        );

        $this->globals->set('dice', $dice);
        return $dice;
    }

    public function rollDiceWithoutStorage($dice = [], $idsToRoll = [0, 1, 2, 3, 4])
    {
        for ($i = 0; $i < 5; $i++) {
            if (in_array($i, $idsToRoll)) {
                $dice[$i] = ['id' => $i, 'value' => bga_rand(1, 6)];
            }
        }
        return $dice;
    }
}
