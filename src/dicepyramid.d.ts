/**
 * Your game interfaces
 */

// remove this if you don't use cards. If you do, make sure the types are correct . By default, some number are send as string, I suggest to cast to right type in PHP.
interface Card {
  id: number;
  name?: string;
  location?: string;
  location_arg?: number;
  type?: number;
  type_arg?: number;
  flipped?: boolean;
  used?: boolean;
}

interface dicepyramidGamedatas {
  current_player_id: string;
  decision: { decision_type: string };
  game_result_neutralized: string;
  gamestate: Gamestate;
  gamestates: { [gamestateId: number]: Gamestate };
  neutralized_player_id: string;
  notifications: { last_packet_id: string; move_nbr: string };
  playerorder: (string | number)[];
  players: { [playerId: number]: Player };
  tablespeed: string;

  // Add here variables you set up in getAllDatas
  CARD_DATA: Record<number, ICardInfo>;
  pyramid?: Card[];
  relics?: Card[];
  activeRelic?: Card;
  dice?: Record<number, Die>;
  powerRelics?: boolean;
}

interface Die {
  id: number;
  value: number;
}
