<?php

class Map {
  const UP    = 'U';
  const DOWN  = 'D';
  const RIGHT = 'R';
  const LEFT  = 'L';

  const LOSS = 'heat_loss';

  const MOVE_LIMIT = 3;

  public $map = [];
  public $directions;

  public function __construct() {
    $this->directions = [ self::UP, self::DOWN, self::RIGHT, self::LEFT ];
  }


  public function path($map, $start, $end, $steps = []) {
    $data                = [$start];
    $recurrence          = [];
    $heat_loss_found     = 0;
    $last_position_found = [];

    $multiply            = [
      Map::UP    => [-1, 0],
      Map::DOWN  => [1, 0],
      Map::RIGHT => [0, 1],
      Map::LEFT  => [0, -1]
    ];

    $opposite = [
      Map::UP    => Map::DOWN,
      Map::DOWN  => Map::UP,
      Map::RIGHT => Map::LEFT,
      Map::LEFT  => Map::RIGHT
    ];

    while ($last_position_found !== $end) {
      $found = $this->findLowestHeatLoss($data);

      $position_y      = $found['position_y'];
      $position_x      = $found['position_x'];
      $direction_found = $found['direction'];
      $heat_loss_found = $found[Map::LOSS];

      $last_position_found = ['position_y' => $position_y, 'position_x' => $position_x];

      if (isset($recurrence[$position_y][$position_x][$direction_found])) {
        continue;
      } else {
        $recurrence[$position_y][$position_x][$direction_found] = true;
      }

      foreach ($this->directions as $direction) {
        if ($direction == $direction_found || $opposite[$direction] == $direction_found) {
          continue;
        }

        $increment = 0;
        list ($multiply_y, $multiply_x) = $multiply[$direction];

        for ($i = 1; $i <= $steps['max']; $i++) {
          $check_y = $position_y + $i * $multiply_y;
          $check_x = $position_x + $i * $multiply_x;
        
          if (($check_y) >= 0 && ($check_y) < count($map) && $check_x >= 0 && $check_x < count($map[0])) {
            $increment += $map[$check_y][$check_x][Map::LOSS];
            $check_heat_loss = $heat_loss_found + $increment;
        
            if ($i < $steps['min']) {
              continue;
            }

            if ($map[$check_y][$check_x][$direction] > $check_heat_loss) {
              $map[$check_y][$check_x][$direction] = $check_heat_loss;
              $data[] = ['position_y' => $check_y, 'position_x' => $check_x, 'direction' => $direction, Map::LOSS => $check_heat_loss];
            }
          }
        }
      }
    }    

    return $heat_loss_found;
  } // end of path

  private function findLowestHeatLoss(&$data) {
    $lowest_heat_loss = min(array_column($data, Map::LOSS));
    $record           = [];

    foreach ($data as $key => $item) {
      if ($item[Map::LOSS] == $lowest_heat_loss) {
        unset($data[$key]);
        $record = $item;
        break;
      }
    }

    return $record;
  } // end of find
}

// -------------------------------------------------------
// Main
// -------------------------------------------------------
require_once('data.php');

$placeholder = 99999;           
$starter     = [
  Map::LOSS   => $placeholder,
  Map::UP     => $placeholder,
  Map::DOWN   => $placeholder,
  Map::RIGHT  => $placeholder,
  Map::LEFT   => $placeholder
];

$start = [
  'position_y'   => 0, 
  'position_x'   => 0, 
  Map::LOSS      => 0, 
  'direction'    => ''
];

$map = new Map();
foreach ($data as $index => $row) {
  $row = str_split($row);

  if (!isset($map->map[$index])) {
    $map->map[$index] = [];
  }

  for ($i = 0 ; $i < count($row); $i++) {
    $record            = $starter;
    $record[Map::LOSS] = $row[$i];

    $map->map[$index][$i] = $record;
  }  
}

$end = [
  'position_y' => count($map->map) - 1, 
  'position_x' => count($map->map[0]) - 1
];

// Question 1:
$time_start = date('h:i:s A');
$sum        = $map->path($map->map, $start, $end, ['min' => 0, 'max' => Map::MOVE_LIMIT]);
$time_end   = date('h:i:s A');

print '<p>The least heat loss it can incur is ' . $sum . '</p>';
print '<p>Start: ' . $time_start. '<br />End: ' . $time_end . '</p>';


// Question 2
$minimum    = 4;
$maximum    = 10;
$time_start = date('h:i:s A');
$sum        = $map->path($map->map, $start, $end, ['min' => $minimum, 'max' => $maximum]);
$time_end   = date('h:i:s A');

print '<p>In the ultra crucible, the least heat loss it can incur is ' . $sum . '</p>';
print '<p>Start: ' . $time_start. '<br />End: ' . $time_end . '</p>';