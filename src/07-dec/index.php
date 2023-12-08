<?php
 
class Hand {
  use Sort;

  public $hand;
  public $bid;
  public $score;
  public $highest_card;

  public function __construct($hand = '', $bid = 0, $score = 0, $highest_card = '') {
    $this->hand         = $hand;
    $this->bid          = $bid;
    $this->score        = $score;
    $this->highest_card = $highest_card;
  }
}

trait Sort { 

  static function bubble($a, $b) {
    $high_card_1 = $a->highest_card;
    $high_card_2 = $b->highest_card;
    
    if ($high_card_1 == $high_card_2) { 
      return 0;
    }

    return ($high_card_1 > $high_card_2) ? -1 : 1;
  }
}

class Rules {
  const FIVE_OF_A_KIND  = 1;
  const FOUR_OF_A_KIND  = 2;
  const FULL_HOUSE      = 3;
  const THREE_OF_A_KIND = 4;
  const TWO_PAIR        = 5;
  const ONE_PAIR        = 6;
  const HIGH_CARD       = 7;

  const CARD_COUNT      = 5;
  const HAND_SCORE      = 2;

  private $jokers_wild  = false;
  private $wildcard     = 'J';

  public $order = [
    'A' => '13',
    'K' => '12',
    'Q' => '11',
    'J' => '10',
    'T' => '09',
    '9' => '08',
    '8' => '07',
    '7' => '06',
    '6' => '05',
    '5' => '04',
    '4' => '03',
    '3' => '02',
    '2' => '01',
  ];

  public function __construct($jokers_wild = false) {
    if ($jokers_wild) {
      $this->jokers_wild = true;
      $this->order['J']  = '00';
    }
  }

  public $set_order = [
    self::FIVE_OF_A_KIND  => [5, 0, 6],  // first set, second set, relative score
    self::FOUR_OF_A_KIND  => [4, 0, 5],
    self::FULL_HOUSE      => [3, 2, 4],
    self::THREE_OF_A_KIND => [3, 0, 3],
    self::TWO_PAIR        => [2, 2, 2],
    self::ONE_PAIR        => [1, 0, 1],
    self::HIGH_CARD       => [0, 0, 0]
  ];


  public function getHighestCard($hand) : int {
    $cards = str_split($hand);

    $value  = '';
    foreach ($cards as $card) {
      $value .= $this->order[$card];
    }

    return intval($value);
  }

  public function getScore($string = '') : int {
    $hand  =  array_count_values(str_split($string));
    arsort($hand);

    $wildcards = '';
    if ($this->jokers_wild) {
      $wildcards = substr_count($string, $this->wildcard);
    }
        
    $score = $this->set_order[self::HIGH_CARD][self::HAND_SCORE];
    $data  = array_values($hand);

    for ($i = 0; $i < count($data); $i++) {
      if ($data[0] == 5) {
        $score = $this->set_order[self::FIVE_OF_A_KIND][self::HAND_SCORE];
        break;        
      }

      if ($data[0] == 4) {        
        switch ($wildcards) {
          case 4:
          case 1:
            $deal = self::FIVE_OF_A_KIND;
            break;

          default:
            $deal = self::FOUR_OF_A_KIND;
            break;
        } 

        $score = $this->set_order[$deal][self::HAND_SCORE];
        break;
      }

      if ($data[0] == 3 && $data[1] == 2) {
        switch ($wildcards) {
          case 3:
          case 2:
            $deal = self::FIVE_OF_A_KIND;
            break;

          default:
            $deal = self::FULL_HOUSE;
            break;
        } 

        $score = $this->set_order[$deal][self::HAND_SCORE];
        break;
      }

      if ($data[0] == 3) {
        switch ($wildcards) {
          case 3:
          case 1:
            $deal = self::FOUR_OF_A_KIND;
            break;

          case 2:
            $deal = self::FIVE_OF_A_KIND;
            break;

          default:
            $deal = self::THREE_OF_A_KIND;
            break;
        } 

        $score = $this->set_order[$deal][self::HAND_SCORE];
        break;
      }

      if ($data[0] == 2 && $data[1] == 2) {
        switch ($wildcards) {
          case 2:
            $deal = self::FOUR_OF_A_KIND;
            break;

          case 1:
            $deal = self::FULL_HOUSE;
            break;

          default:
            $deal = self::TWO_PAIR;
            break;
        } 

        $score = $this->set_order[$deal][self::HAND_SCORE];
        break;
      }

      if ($data[0] == 2) {
        switch ($wildcards) {
          case 2:            
          case 1:
            $deal = self::THREE_OF_A_KIND;
            break;

          default:
            $deal = self::ONE_PAIR;
            break;
        } 

        $score = $this->set_order[$deal][self::HAND_SCORE];
        break;
      }

      if ($data[0] == 1 && $wildcards == 1) {
        $score = $this->set_order[self::ONE_PAIR][self::HAND_SCORE];
      }
    }

    return $score;
  }
} // end of class Rules



// --------------------------------------------------------------
// Main
// --------------------------------------------------------------
require_once('data.php');

$hands = [];
$count = count($data);
$rules = new Rules();

foreach ($data as $set) {
  $hand  = strtoupper($set[0]);
  $bid   = $set[1];  

  $score        = $rules->getScore($hand);
  $highest_card = $rules->getHighestCard($hand);

  $hands[$score][] = new Hand($hand, $bid, $score, $highest_card);
}

foreach ($hands as $key => $hand) {
  usort($hands[$key], [Hand::class, "bubble"]);
}

krsort($hands);

$total_winnings = [];
$total          = 0;
foreach ($hands as $key => $hand) {
  foreach ($hand as $set) {
    $total_winnings[$count] = [
      'sum'   => $set->bid * $count,
      'hand'  => $set->hand,
      'bid'   => $bid,
      'count' => $count
    ];

    $total += ($set->bid * $count);

    $count--;
  }
}

// Question 1: Total Bid products
print '<p>Question 1: The total winnings are ' . $total . '</p>'; 


// Question 2: Joker's are wild
$hands = [];
$count = count($data);
$rules = new Rules(true);

foreach ($data as $set) {
  $hand  = strtoupper($set[0]);
  $bid   = $set[1];  

  $score        = $rules->getScore($hand);
  $highest_card = $rules->getHighestCard($hand);

  $hands[$score][] = new Hand($hand, $bid, $score, $highest_card);
}

foreach ($hands as $key => $hand) {
  usort($hands[$key], [Hand::class, "bubble"]);
}

krsort($hands);

$total_winnings = [];
$count          = count($data);
$total          = 0;

foreach ($hands as $key => $hand) {
  foreach ($hand as $set) {
    $total_winnings[$count] = [
      'sum'   => $set->bid * $count,
      'hand'  => $set->hand,
      'highest_card' => $set->highest_card,
      'bid'   => $set->bid,
      'score' => $set->score,
      'count' => $count
    ];
    
    $total += ($set->bid * $count);

    $count--;
  }
}

print '<p>Question 2: Jokers wild! The total winnings are ' . $total . '</p>'; 




