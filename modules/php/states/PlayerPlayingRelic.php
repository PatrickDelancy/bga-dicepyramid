<?php

namespace Bga\Games\DicePyramid;

trait StatePlayerPlayingRelicTrait
{
    public function argPlayerPlayingRelic()
    {
        $card = $this->getCard($this->getActiveRelicId());

        return [
            'relic_name' => CARD_DATA[$card['type']]['relic']['name'],
            'i18n' => ['relic_name'],

            'card' => $card,
            'actionName' => CARD_DATA[$card['type']]['relic']['action'],
            'diceCount' => CARD_DATA[$card['type']]['relic']['diceCount'] ?? null,
            'diceValues' => CARD_DATA[$card['type']]['relic']['diceValues'] ?? null,
        ];
    }

    public function actPlayRelicOnDice(string $diceIds, string $actionName)
    {
        $card = $this->getCard($this->getActiveRelicId());
        if ($card['location'] != 'relics')
            throw new \BgaUserException(self::_('You can only play relics you have acquired'));
        if ($card['used'])
            throw new \BgaUserException(self::_('This relic has already been used this turn'));

        $dice = $this->getDice();
        $diceIdArray = array_map(fn($id) => intval($id), explode(',', $diceIds));

        switch ($actionName) {
            case RelicAction::FLIP->value:
                foreach ($diceIdArray as $diceId) {
                    $dice[$diceId]['value'] = 7 - $dice[$diceId]['value'];
                }
                break;
            case RelicAction::REROLL->value:
                $dice = $this->rollDiceWithoutStorage($dice, $diceIdArray);
                break;
            case RelicAction::TURN_UP->value:
                foreach ($diceIdArray as $diceId) {
                    $dice[$diceId]['value'] = $dice[$diceId]['value'] == 6 ? 1 : $dice[$diceId]['value'] + 1;
                }
                break;
            case RelicAction::TURN_DOWN->value:
                foreach ($diceIdArray as $diceId) {
                    $dice[$diceId]['value'] = $dice[$diceId]['value'] == 1 ? 6 : $dice[$diceId]['value'] - 1;
                }
                break;
        }

        $this->setDice($dice);
        $powerRelics = $this->getGameStateValue('rule_powerrelics') == 1;
        if ($powerRelics) {
            $this->setCardUsed($card['id'], true);
            $card = $this->getCard($card['id']);
        } else {
            $this->cards->moveCard($card['id'], 'discard');
        }
        $this->clearActiveRelicId();

        $this->incStat(1, 'relics_used', $this->getActivePlayerId());
        $rerolls = $this->globals->get('rerolls');
        if ($rerolls > 0)
            $this->incStat(1, 'relics_used_unforced', $this->getActivePlayerId());

        $this->notifyAllPlayers($powerRelics ? 'playPowerRelic' : 'playRelic', clienttranslate('After playing relic, dice faces are ${rolled_values}'), [
            "rolled_values" => implode(', ', array_map(fn($d) => $d['value'], $dice)),
            "dice" => $dice,
            "rerolls" => $rerolls,
            "takeableCards" => $this->getTakeableCards($dice),
            "relicCard" => $card,
        ]);

        $this->gamestate->nextState("");
    }

    public function actCancelPlayingRelic()
    {
        $card = $this->getCard($this->getActiveRelicId());
        $this->clearActiveRelicId();

        $this->notifyAllPlayers('cancelPlayingRelic', clienttranslate('${playerName} chooses not to play ${relic_name}'), [
            "playerName" => $this->getActivePlayerName(),
            "relic_name" => CARD_DATA[$card['type']]['relic']['name'],
            "relicCard" => $card,
            "i18n" => ['relic_name'],
        ]);

        $this->gamestate->nextState("");
    }
}
