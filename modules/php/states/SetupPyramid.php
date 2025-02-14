<?php

namespace Bga\Games\DicePyramid;

trait StateGameSetupPyramidTrait
{
    public function argSetupPyramid()
    {
        return [
            'pyramid' => $this->getCardsInLocation('pyramid')
        ];
    }

    public function stSetupPyramid(): void
    {
        $greatPyramid = $this->getGameStateValue('rule_greatpyramid') == 1;
        $faceUp = $this->getGameStateValue('rule_faceup') == 1;

        $this->cards->shuffle('deck');
        $entranceCard = $this->getSpecialCard(SpecialCardType::ENTRANCE);
        $treasureRoomCard = $this->getSpecialCard(SpecialCardType::TREASURE);
        $this->cards->moveCards([$entranceCard['id'], $treasureRoomCard['id']], 'temp');

        // build the base of the pyramid
        if ($greatPyramid) {
            $this->cards->pickCardsForLocation(9, 'deck', 'pyramid');
            $this->cards->moveCard($treasureRoomCard['id'], 'pyramid');
            $this->cards->shuffle('pyramid');
        } else {
            $this->cards->pickCardsForLocation(5, 'deck', 'pyramid');
            $this->cards->shuffle('pyramid');
            $this->cards->insertCard($treasureRoomCard['id'], 'pyramid', 0);
        }

        // build the first row, flipping them to be visible
        $this->cards->pickCardsForLocation($greatPyramid ? 4 : 3, 'deck', 'firstRow');
        $this->cards->moveCard($entranceCard['id'], 'firstRow');
        $this->cards->shuffle('firstRow');

        $firstRowCards = $this->getCardsInLocation('firstRow');
        $this->flipCards(array_map(fn($c) => $c['id'], $firstRowCards), true);
        if ($faceUp) {
            $this->flipCards(array_map(fn($c) => $c['id'], $this->getCardsInLocation('pyramid')), true);
        }

        foreach ($firstRowCards as $card) {
            $this->cards->insertCardOnExtremePosition($card['id'], 'pyramid', true);
        }

        $this->notifyAllPlayers('pyramidSetup', clienttranslate('Pyramid setup'), [
            "cards" => array_map(fn($c) => $c['flipped'] ? $c : $this->cardIdOnly($c), $this->getCardsInLocation('pyramid'))
        ]);

        $this->gamestate->nextState("");
    }
}
