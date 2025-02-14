<?php

namespace Bga\Games\DicePyramid;

use Deck;

define('SQL_SELECTCARD', "SELECT `card_id` `id`, `card_type` `type`, `card_type_arg` `type_arg`, `card_location` `location`, `card_location_arg` `location_arg`, `card_flipped` `flipped`, `card_used` `used` FROM `card`");

enum SpecialCardType: string
{
    case ENTRANCE = '1';
    case TREASURE = '2';
}

enum RelicAction: string
{
    case FLIP = 'flip';
    case REROLL = 'reroll';
    case TURN = 'turnupdown';
    case TURN_UP = 'turnup';
    case TURN_DOWN = 'turndown';
}

define('CARD_1_ENTRANCE', 1);
define('CARD_2_TREASURE_CHAMBER', 2);
define('CARD_3_SANDSTONE_TRIAD', 3);
define('CARD_4_BASALT_TRIUNE', 4);
define('CARD_5_GRANITE_TRIPYTCH', 5);
define('CARD_6_DESCENDING_PASSAGE', 6);
define('CARD_7_ASCENDING_PASSAGE', 7);
define('CARD_8_GRAND_GALLERY', 8);
define('CARD_9_ROYAL_CHAMBER', 9);
define('CARD_10_UNSTABLE_STAIRWAY', 10);
define('CARD_11_HALL_OF_HIEROGLYPHS', 11);
define('CARD_12_INNER_SANCTUM', 12);
define('CARD_13_ANTECHAMBER', 13);
define('CARD_14_ALTAR_OF_THE_SKY', 14);
define('CARD_15_ALTAR_OF_THE_DEPTHS', 15);

define('CARD_DATA', [
    CARD_1_ENTRANCE => [
        'name' => clienttranslate('Entrance'),
        'room' => ['name' => clienttranslate('Entrance'), 'text' => clienttranslate('Any Combination')],
        'relic' => ['name' => clienttranslate('Pheonix\'s Feather'), 'text' => clienttranslate('Reroll all 5 dice'), 'action' => RelicAction::REROLL->value, 'diceCount' => [5]],
    ],
    CARD_2_TREASURE_CHAMBER => [
        'name' => clienttranslate('Treasure Chamber'),
        'room' => ['name' => clienttranslate('Treasure Chamber'), 'text' => clienttranslate('Five of a Kind')],
        'relic' => ['name' => clienttranslate('Golden Ankh'), 'text' => clienttranslate('Win the Game')],
    ],
    CARD_3_SANDSTONE_TRIAD => [
        'name' => clienttranslate('Sandstone Triad'),
        'room' => ['name' => clienttranslate('Sandstone Triad'), 'text' => clienttranslate('Three 6s or Three 1s')],
        'relic' => ['name' => clienttranslate('Sandstone Amulet'), 'text' => clienttranslate('Flip a dice with a 6 or a 1'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1], 'diceValues' => [1, 6]],
    ],
    CARD_4_BASALT_TRIUNE => [
        'name' => clienttranslate('Basalt Triune'),
        'room' => ['name' => clienttranslate('Basalt Triune'), 'text' => clienttranslate('Three 5s or Three 2s')],
        'relic' => ['name' => clienttranslate('Basalt Signet'), 'text' => clienttranslate('Flip a dice with a 5 or a 2'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1], 'diceValues' => [2, 5]],
    ],
    CARD_5_GRANITE_TRIPYTCH => [
        'name' => clienttranslate('Granite Triptych'),
        'room' => ['name' => clienttranslate('Granite Triptych'), 'text' => clienttranslate('Three 4s or Three 3s')],
        'relic' => ['name' => clienttranslate('Granite Idol'), 'text' => clienttranslate('Flip a dice with a 4 or a 3'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1], 'diceValues' => [3, 4]],
    ],
    CARD_6_DESCENDING_PASSAGE => [
        'name' => clienttranslate('Descending Passage'),
        'room' => ['name' => clienttranslate('Descending Passage'), 'text' => clienttranslate('A 3, a 2 and a 1')],
        'relic' => ['name' => clienttranslate('Onyx Beetle'), 'text' => clienttranslate('Flip a dice with 3, 2, or 1'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1], 'diceValues' => [1, 2, 3]],
    ],
    CARD_7_ASCENDING_PASSAGE => [
        'name' => clienttranslate('Ascending Passage'),
        'room' => ['name' => clienttranslate('Asscending Passage'), 'text' => clienttranslate('A 4, a 5 and a 6')],
        'relic' => ['name' => clienttranslate('Marble Beetle'), 'text' => clienttranslate('Flip a dice with 4, 5, or 6'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1], 'diceValues' => [4, 5, 6]],
    ],
    CARD_8_GRAND_GALLERY => [
        'name' => clienttranslate('Grand Gallery'),
        'room' => ['name' => clienttranslate('Grand Gallery'), 'text' => clienttranslate('Three of a Kind')],
        'relic' => ['name' => clienttranslate('Jackal\'s Fang'), 'text' => clienttranslate('Reroll 3 dice'), 'action' => RelicAction::REROLL->value, 'diceCount' => [3]],
    ],
    CARD_9_ROYAL_CHAMBER => [
        'name' => clienttranslate('Royal Chamber'),
        'room' => ['name' => clienttranslate('Royal Chamber'), 'text' => clienttranslate('Four of a Kind')],
        'relic' => ['name' => clienttranslate('Crook and Flail'), 'text' => clienttranslate('Add or subtract 1 to a dice'), 'action' => RelicAction::TURN->value, 'diceCount' => [1]],
    ],
    CARD_10_UNSTABLE_STAIRWAY => [
        'name' => clienttranslate('Unstable Stairway'),
        'room' => ['name' => clienttranslate('Unstable Stairway'), 'text' => clienttranslate('Short Straight')],
        'relic' => ['name' => clienttranslate('Cat Statue'), 'text' => clienttranslate('Flip 1 dice'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1]],
    ],
    CARD_11_HALL_OF_HIEROGLYPHS => [
        'name' => clienttranslate('Hall of Hieroglyphs'),
        'room' => ['name' => clienttranslate('Hall of Hieroglyphs'), 'text' => clienttranslate('Long Straight')],
        'relic' => ['name' => clienttranslate('Sphinx\'s Ring'), 'text' => clienttranslate('Flip 1 or 2 dice'), 'action' => RelicAction::FLIP->value, 'diceCount' => [1, 2]],
    ],
    CARD_12_INNER_SANCTUM => [
        'name' => clienttranslate('Inner Sanctum'),
        'room' => ['name' => clienttranslate('Inner Sanctum'), 'text' => clienttranslate('Full House')],
        'relic' => ['name' => clienttranslate('Griffin\'s Feather'), 'text' => clienttranslate('Reroll 3 or 2 dice'), 'action' => RelicAction::REROLL->value, 'diceCount' => [2, 3]],
    ],
    CARD_13_ANTECHAMBER => [
        'name' => clienttranslate('Antechamber'),
        'room' => ['name' => clienttranslate('Antechamber'), 'text' => clienttranslate('Two Pairs')],
        'relic' => ['name' => clienttranslate('Cobra\'s Fangs'), 'text' => clienttranslate('Reroll two dice'), 'action' => RelicAction::REROLL->value, 'diceCount' => [2]],
    ],
    CARD_14_ALTAR_OF_THE_SKY => [
        'name' => clienttranslate('Altar of the Sky'),
        'room' => ['name' => clienttranslate('Altar of the Sky'), 'text' => clienttranslate('All 5 dice higher than 3')],
        'relic' => ['name' => clienttranslate('Scepter of Nut'), 'text' => clienttranslate('Add 1 to a dice'), 'action' => RelicAction::TURN_UP->value, 'diceCount' => [1], 'diceValues' => [1, 2, 3, 4, 5]],
    ],
    CARD_15_ALTAR_OF_THE_DEPTHS => [
        'name' => clienttranslate('Altar of the Depths'),
        'room' => ['name' => clienttranslate('Altar of the Depths'), 'text' => clienttranslate('All 5 dice lower than 4')],
        'relic' => ['name' => clienttranslate('Scepter of Ged'), 'text' => clienttranslate('Subgract 1 from a dice'), 'action' => RelicAction::TURN_DOWN->value, 'diceCount' => [1], 'diceValues' => [2, 3, 4, 5, 6]],
    ],
]);

trait CardsTrait
{
    private Deck $cards;

    private function fixCardAttributes(mixed $card)
    {
        if ($card == null) return null;

        $card['id'] = intval($card['id']);
        $card['type'] = intval($card['type']);
        $card['type_arg'] = intval($card['type_arg']);
        //$card['location'] = intval($card['location']);
        $card['location_arg'] = intval($card['location_arg']);

        $card['name'] = CARD_DATA[$card['type']]['name'];
        $card['flipped'] = boolval($card['flipped']);
        $card['used'] = boolval($card['used']);

        return $card;
    }
    private function fixCardsAttributes(array $cards)
    {
        if ($cards == null) return null;
        return array_map(fn($c) => $this->fixCardAttributes($c), $cards);
    }

    private function cardIdOnly(array $card)
    {
        return [
            'id' => $card['id'],
            'location' => $card['location'],
            'location_arg' => $card['location_arg'],
        ];
    }

    private function getCard($cardId)
    {
        return $this->fixCardAttributes($this->getObjectFromDB(SQL_SELECTCARD . " WHERE `card_id` = '$cardId'"));
    }

    private function getCardsInLocation($location)
    {
        return $this->fixCardsAttributes(array_values($this->getCollectionFromDb(
            SQL_SELECTCARD . " WHERE `card_location` = '$location' ORDER BY `card_location_arg`"
        )));
    }

    private function getSpecialCard(SpecialCardType $type)
    {
        return $this->fixCardAttributes($this->getObjectFromDB(SQL_SELECTCARD . " WHERE `card_type` = '$type->value'"));
    }

    private function getCardsOfTypeInLocation($type, $location)
    {
        return $this->fixCardsAttributes(array_values($this->getCollectionFromDb(
            SQL_SELECTCARD . " WHERE `card_type` = '$type' AND `card_location` = '$location' ORDER BY `card_location_arg`"
        )));
    }

    private function flipCards(array $cardIds, bool $faceUp)
    {
        $flippedBit = $faceUp ? '1' : '0';
        $cardIdsString = implode(',', $cardIds);
        $this->DbQuery("UPDATE `card` SET card_flipped = $flippedBit WHERE card_id IN ($cardIdsString)");
    }

    private function setCardUsed(int $cardId, bool $used)
    {
        return $this->setCardsUsed([$cardId], $used);
    }
    private function setCardsUsed(array $cardIds, bool $used)
    {
        $usedBit = $used ? '1' : '0';
        $cardIdsString = implode(',', $cardIds);
        $this->DbQuery("UPDATE `card` SET card_used = $usedBit WHERE card_id IN ($cardIdsString)");
    }
    private function clearCardsUsed()
    {
        $this->DbQuery("UPDATE `card` SET card_used = 0");
    }

    private function shouldCardBeVisible($card, $pyramidCards)
    {
        switch ($card['location_arg']) {
            case 0:
                $argOffset = 1;
                break;
            case 1:
            case 2:
                $argOffset = $card['location_arg'] + 2;
                break;
            case 3:
            case 4:
            case 5:
                $argOffset = $card['location_arg'] + 3;
                break;
            case 6:
            case 7:
            case 8:
            case 9:
                $argOffset = $card['location_arg'] + 4;
                break;
            default:
                return true;
        }

        return 0 == count(array_filter($pyramidCards, fn($c) => in_array($c['location_arg'], [$argOffset, $argOffset + 1])));
    }

    private function getTakeableCards(array $dice)
    {
        $pyramidCards = $this->getCardsInLocation('pyramid');
        return array_values(array_filter(
            $pyramidCards,
            fn($c) => $this->shouldCardBeVisible($c, $pyramidCards) && $this->canTakeRoomCard($c, array_values($dice))
        ));
    }

    private function getActiveRelicId()
    {
        return $this->globals->get('activeRelicId');
    }
    private function setActiveRelicId(int $cardId)
    {
        $this->globals->set('activeRelicId', $cardId);
    }
    private function clearActiveRelicId()
    {
        $this->globals->delete('activeRelicId');
    }

    private function canTakeRoomCard($card, $dice): bool
    {
        // no dice to check
        if (count($dice) === 0) return false;
        // cannot take face-down cards
        if (!$card['flipped']) return false;

        switch ($card['type']) {
            case 1: // Entrance
                return true;
            case 2: // Treasure Chamber
                $firstValue = $dice[0]['value'];
                foreach ($dice as $die) {
                    if ($die['value'] !== $firstValue) return false;
                }
                return true;
            case 3: // Sandstone Triad
                if (count(array_filter($dice, fn($die) => $die['value'] === 1)) >= 3) return true;
                if (count(array_filter($dice, fn($die) => $die['value'] === 6)) >= 3) return true;
                return false;
            case 4: // Basalt Triune
                if (count(array_filter($dice, fn($die) => $die['value'] === 2)) >= 3) return true;
                if (count(array_filter($dice, fn($die) => $die['value'] === 5)) >= 3) return true;
                return false;
            case 5: // Granite Triptych
                if (count(array_filter($dice, fn($die) => $die['value'] === 3)) >= 3) return true;
                if (count(array_filter($dice, fn($die) => $die['value'] === 4)) >= 3) return true;
                return false;
            case 6: // Descending Passage
                return count(array_filter($dice, fn($die) => $die['value'] === 1)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 2)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0;
            case 7: // Ascending Passage
                return count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 5)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 6)) > 0;
            case 8: // Grand Gallery
                foreach ($dice as $die) {
                    if (count(array_filter($dice, fn($d) => $d['value'] === $die['value'])) >= 3) return true;
                }
                return false;
            case 9: // Royal Chamber
                foreach ($dice as $die) {
                    if (count(array_filter($dice, fn($d) => $d['value'] === $die['value'])) >= 4) return true;
                }
                return false;
            case 10: // Unstable Stairway
                return (count(array_filter($dice, fn($die) => $die['value'] === 1)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 2)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0) ||
                    (count(array_filter($dice, fn($die) => $die['value'] === 2)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 5)) > 0) ||
                    (count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 5)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 6)) > 0);
            case 11: // Hall of Hieroglyphs
                return (count(array_filter($dice, fn($die) => $die['value'] === 1)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 2)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0 &&
                    count(array_filter($dice, fn($die) => $die['value'] === 5)) > 0) ||
                    (count(array_filter($dice, fn($die) => $die['value'] === 2)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 3)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 4)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 5)) > 0 &&
                        count(array_filter($dice, fn($die) => $die['value'] === 6)) > 0);
            case 12: // Inner Sanctum
                $values = array_map(fn($die) => $die['value'], $dice);
                $uniqueValues = array_unique($values);
                if (count($uniqueValues) === 1) {
                    return true;
                } elseif (count($uniqueValues) === 2) {
                    $firstValueCount = count(array_filter($values, fn($value) => $value === reset($uniqueValues)));
                    $secondValueCount = count(array_filter($values, fn($value) => $value === next($uniqueValues)));
                    return ($firstValueCount === 3 && $secondValueCount === 2) || ($firstValueCount === 2 && $secondValueCount === 3);
                }
                return false;
            case 13: // Antechamber
                $pairedValues = [];
                for ($i = 0; $i < count($dice); $i++) {
                    $val = $dice[$i]['value'];
                    $count = count(array_filter($dice, fn($d) => $d['value'] == $val));
                    if ($count >= 4)
                        return true; // 4-of-a-kind is 2 pairs
                    if ($count >= 2 && !in_array($val, $pairedValues))
                        $pairedValues[] = $val;
                }
                return count($pairedValues) >= 2;
            case 14: // Altar of the Sky
                return array_reduce($dice, fn($acc, $die) => $acc && $die['value'] > 3, true);
            case 15: // Altar of the Depths
                return array_reduce($dice, fn($acc, $die) => $acc && $die['value'] < 4, true);
        }
    }

    // private function getSelectableDiceIdsForRelic($card, $dice): array
    // {
    //     switch ($card['type']) {
    //         case CARD_3_SANDSTONE_TRIAD:
    //             return array_map(fn($d) => $d['id'], array_filter(array_values($dice), fn($d) => in_array($d['value'], [1, 6])));
    //         case CARD_4_BASALT_TRIUNE:
    //             return array_map(fn($d) => $d['id'], array_filter(array_values($dice), fn($d) => in_array($d['value'], [2, 5])));
    //         case CARD_5_GRANITE_TRIPYTCH:
    //             return array_map(fn($d) => $d['id'], array_filter(array_values($dice), fn($d) => in_array($d['value'], [3, 4])));
    //         case CARD_6_DESCENDING_PASSAGE:
    //             return array_map(fn($d) => $d['id'], array_filter(array_values($dice), fn($d) => in_array($d['value'], [1, 2, 3])));
    //         case CARD_7_ASCENDING_PASSAGE:
    //             return array_map(fn($d) => $d['id'], array_filter(array_values($dice), fn($d) => in_array($d['value'], [4, 5, 6])));
    //         case CARD_8_GRAND_GALLERY:
    //         case CARD_9_ROYAL_CHAMBER:
    //         case CARD_10_UNSTABLE_STAIRWAY:
    //         case CARD_11_HALL_OF_HIEROGLYPHS:
    //         case CARD_12_INNER_SANCTUM:
    //         case CARD_13_ANTECHAMBER:
    //         case CARD_14_ALTAR_OF_THE_SKY:
    //         case CARD_15_ALTAR_OF_THE_DEPTHS:
    //             return array_keys($dice);
    //         default:
    //             return [];
    //     }
    // }
}
