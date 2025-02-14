<?php

namespace Bga\Games\DicePyramid;

trait StatePlayerTurnTrait
{
    public function argPlayerTurn()
    {
        $dice = $this->getDice();
        return [
            'dice' => $dice,
            'rerolls' => $this->globals->get('rerolls'),
            "takeableCards" => $this->getTakeableCards($dice)
        ];
    }

    public function actRerollDice(string $diceIds)
    {
        $rerolls = $this->globals->get('rerolls');
        if ($rerolls <= 0) {
            throw new \BgaUserException(self::_('No more rerolls allowed'));
        }

        $diceIdArray = explode(',', $diceIds);
        $dice = $this->rollDice($diceIdArray);
        $rerolls = $this->globals->inc('rerolls', -1);

        $this->notifyAllPlayers('reRoll', clienttranslate('${playerName} rerolls dice, resulting in ${rolled_values}'), [
            "playerName" => $this->getActivePlayerName(),
            "rolled_values" => implode(', ', array_map(fn($d) => $d['value'], $dice)),
            "dice" => $dice,
            "rerolls" => $rerolls,
            "takeableCards" => $this->getTakeableCards($dice),
        ]);
    }

    public function actTakeRoom(int $cardId)
    {
        $card = $this->getCard($cardId);
        $dice = $this->getDice();

        if (!$this->canTakeRoomCard($card, $dice)) {
            throw new \BgaUserException(sprintf(
                self::_('The room %s cannot be completed with the dice %s'),
                $card['name'],
                implode(', ', array_map(fn($d) => $d['value'], $dice))
            ));
        }

        $this->cards->moveCard($cardId, 'relics');
        $card = $this->getCard($cardId);

        $pyramidCards = $this->getCardsInLocation('pyramid');
        if ($pyramidCards == null || count($pyramidCards) == 0) {
            $flippedCards = [];
        } else {
            $flippedCards = array_values(array_filter($pyramidCards, fn($c) => $this->shouldCardBeVisible($c, $pyramidCards)));
            $this->flipCards(array_map(fn($c) => $c['id'], $flippedCards), true);
        }

        $this->incStat(1, 'rooms_taken', $this->getActivePlayerId());
        $this->notifyAllPlayers('takeRoom', clienttranslate('${playerName} acquires the relic ${relic_name}'), [
            "playerName" => $this->getActivePlayerName(),
            "relic_name" => CARD_DATA[$card['type']]['relic']['name'],
            "card" => $card,
            "flippedCards" => $flippedCards,
        ]);

        $maxRelics = $this->globals->get('max_relics');
        $relics = $this->getCardsInLocation('relics');
        if ($card['type'] != SpecialCardType::TREASURE->value && count($relics) > $maxRelics)
            $this->gamestate->nextState("forcedDiscardRelic");
        else
            $this->gamestate->nextState("endTurn");
    }

    public function actPlayRelic(int $cardId)
    {
        $card = $this->getCard($cardId);
        if ($card['location'] != 'relics')
            throw new \BgaUserException(self::_('You can only play relics you have acquired'));
        if ($card['used'])
            throw new \BgaUserException(self::_('This relic has already been used this turn'));

        switch ($card['type']) {
            case CARD_2_TREASURE_CHAMBER:
                throw new \BgaUserException(self::_('Ankh cannot be played as a relic'));

            default:
                $this->setActiveRelicId($card['id']);

                $this->notifyAllPlayers('playingRelic', clienttranslate('${playerName} is playing ${relic_name}'), [
                    "playerName" => $this->getActivePlayerName(),
                    "relic_name" => CARD_DATA[$card['type']]['relic']['name'],
                    "relicCard" => $card,
                ]);

                $this->gamestate->nextState("useRelic");
                break;
        }
    }

    public function actTurnDiscardRelic(int $cardId)
    {
        $card = $this->getCard($cardId);
        if ($card['location'] != 'relics')
            throw new \BgaUserException(self::_('You cannot discard a relic you have not acquired yet'));

        $this->cards->moveCard($card['id'], 'discard');

        $this->incStat(1, 'relics_discarded', $this->getActivePlayerId());
        $this->notifyAllPlayers('discardRelic', clienttranslate('${playerName} discards ${relic_name} to begin a new turn'), [
            "playerName" => $this->getActivePlayerName(),
            "relic_name" => CARD_DATA[$card['type']]['relic']['name'],
            "relicCard" => $card,
        ]);

        $this->gamestate->nextState("endTurn");
    }

    public function actGiveUp()
    {
        $this->gamestate->nextState("endGame");
    }
}
