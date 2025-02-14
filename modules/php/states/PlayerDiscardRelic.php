<?php

namespace Bga\Games\DicePyramid;

trait StatePlayerDiscardRelicTrait
{
    public function argPlayerDiscardRelic()
    {
        return [
            'maxRelics' => $this->globals->get('max_relics')
        ];
    }

    public function actDiscardRelic(string $cardId)
    {
        $card = $this->getCard($cardId);
        if ($card['location'] != 'relics')
            throw new \BgaUserException(self::_('You cannot discard a relic you have not acquired yet'));

        $this->cards->moveCard($card['id'], 'discard');

        $this->incStat(1, 'relics_lost', $this->getActivePlayerId());
        $this->notifyAllPlayers('discardRelic', clienttranslate('${playerName} discards ${relic_name} down to limit'), [
            "playerName" => $this->getActivePlayerName(),
            "relic_name" => CARD_DATA[$card['type']]['relic']['name'],
            "relicCard" => $card,
        ]);

        $this->gamestate->nextState("");
    }
}
