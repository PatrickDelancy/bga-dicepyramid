<?php

namespace Bga\Games\DicePyramid;

trait PlayersTrait
{
    function dbGetScore($player_id)
    {
        return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
    }
    function dbSetScore($player_id, $count)
    {
        $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    }
    function dbSetAuxScore($player_id, $score)
    {
        $this->DbQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
    }
    function dbIncScore($player_id, $inc)
    {
        $count = $this->dbGetScore($player_id);
        if ($inc != 0) {
            $count += $inc;
            $this->dbSetScore($player_id, $count);
        }
        return $count;
    }
}
