<?php

class Game {
  public $id;
  public $sets = [];

  const GREEN = 1;
  const RED   = 2;
  const BLUE  = 3;

  public function __construct($id, $sets) {
    $this->id = $id;
    foreach ($sets as $set) {
      $this->sets[] = Extract::data($set, ['green' => self::GREEN, 'blue' => self::BLUE, 'red' => self::RED]);
    }
  }
} // end of Game

class Extract {
  public static function data($data = [], $keys = []) {
    $results = [];

    foreach ($data as $item) {
      $id    = 0;
      $count = intval($item);

      foreach ($keys as $key => $key_id) {
        if (strpos($item, $key) !== false) {
          $id = $key_id;
        }
      }

      $results[$id] = $count;      
    }

    foreach ($keys as $key) {
      if (!array_key_exists($key, $results)) {
        $results[$key] = 0;
      }
    }

    return $results;
  }
} // end of Extract


// --------------------------------------------------------
// Util functions
// --------------------------------------------------------
function getValidGameSum($games = [], $dice = []) {
  $sum = 0;

  foreach ($games as $game) {
    $valid_game = true;

    foreach ($game->sets as $set) {
      if (
        $set[Game::GREEN] > $dice[Game::GREEN] || 
        $set[Game::RED]   > $dice[Game::RED]   || 
        $set[Game::BLUE]  > $dice[Game::BLUE]
      ) {
        $valid_game = false;
      }
    }

    if ($valid_game) {
      $sum += $game->id;
    }
  }

  return $sum;
} // end of getValidGameSum


function getGamePowers($games = []) {
  $sums = [];  

  foreach ($games as $game) {
    $minimum = [
      Game::GREEN => 0,
      Game::RED   => 0,
      Game::BLUE  => 0
    ];

    foreach ($game->sets as $set) {
      $minimum[Game::GREEN] = ($set[Game::GREEN] > $minimum[Game::GREEN]) ? $set[Game::GREEN] : $minimum[Game::GREEN] ;
      $minimum[Game::RED]   = ($set[Game::RED] > $minimum[Game::RED])     ? $set[Game::RED]   : $minimum[Game::RED] ;
      $minimum[Game::BLUE]  = ($set[Game::BLUE] > $minimum[Game::BLUE])   ? $set[Game::BLUE]  : $minimum[Game::BLUE] ;      
    }

    $sums[$game->id] = [
      'power' => $minimum[Game::GREEN] * $minimum[Game::RED] * $minimum[Game::BLUE],
      'green' => $minimum[Game::GREEN],
      'red'   => $minimum[Game::RED],
      'blue'  => $minimum[Game::BLUE]
    ];
  }

  return $sums;
} // end of getGamePowers



// --------------------------------------------------------
// Main
// --------------------------------------------------------
require_once('data.php');

$games = [];
foreach ($data as $id => $game) {
  $games[$id] = new Game($id, $game);
}

/* 
Question:
Determine which games would have been possible if the bag had been loaded with only 
 - 12 red cubes, 
 - 13 green cubes, and 
 - 14 blue cubes. 
 
 What is the sum of the IDs of those games?
*/

$dice = [
  Game::GREEN  => 13,
  Game::RED    => 12,
  Game::BLUE   => 14
];

$sum = getValidGameSum($games, $dice);
print '<p>The valid games total is ' . $sum . '</p>';

/*
Question:
For each game, find the minimum set of cubes that must have been present. What is the sum of the power of these sets?
*/

$sums  = getGamePowers($games); // returns array - needed to verify values
$total = 0;
foreach($sums as $sum) {
  $total += $sum['power'];
}

print '<p>The sum of the powers for each game is ' . $total . '</p>';
