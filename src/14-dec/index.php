<?php 

class Board {
  const CUBE_ROCK = '#';
  const ROLL_ROCK = 'O';
  const EMPTY     = '.';

  const NORTH = 1;
  const EAST  = 2;
  const SOUTH = 3;
  const WEST  = 4;

  public $map = [];

  public $map_limit = 0;
  public $row_limit = 0;
  
  public $rows = [];

  public function __construct($map = []) {
    $this->map = $map;
    $this->map_limit = count($this->map);
    $this->row_limit = count($this->map[0]);
  }

  public function getRows($data = []) : array {
    $rows  = array_fill(0, count($data), []);

    for ($i = 0; $i < count($data); $i++) {
      $row = $data[$i];

      for ($j = 0; $j < $this->row_limit; $j++) {        
        $rows[$j][$i] = $row[$j];    
      }            
    }

    return $rows;
  }

  public function rearrange() {
    $data = [];

    for ($i = 0; $i < $this->map_limit; $i++) {
      $row = $this->map[$i];
      
      for ($j = 0; $j < $this->row_limit; $j++) {
        $character = $row[$j];

        if (!isset($data[$j])) {
          $data[$j] = [];
        }

        $data[$j][$i] = $character;
      }
      
    }

    return $data;
  }  
 
  public function shift() {    
    $rows = [];

    foreach ($this->rows as $data) {
      $row = $data;

      for ($i = 0, $j = 1; $i < $this->row_limit - 1, $j < $this->row_limit; $i++, $j++) {
        $current = $row[$i];
        $next    = $row[$j];

        if ($current == self::EMPTY && $next == self::ROLL_ROCK) {
          $row[$i] = self::ROLL_ROCK;
          $row[$j] = self::EMPTY;
          $i = -1; $j = 0;
        }
      }

      $rows[] = $row;
    }

    return $rows;
  }

  public function cycleNorth() {
    for ($i = 0; $i < $this->row_limit; $i++) {
      $position_y = -1;
      for ($j = 0; $j < $this->map_limit; $j++) {
            
        switch ($this->map[$j][$i]) {
          case Board::ROLL_ROCK:
            $position_y++;
            if ($position_y !== $j) {
              $this->map[$position_y][$i] = Board::ROLL_ROCK;
              $this->map[$j][$i] = Board::EMPTY;
            }
            break;
    
          case Board::CUBE_ROCK:
            $position_y = $j;
            break;
        }
      }
    }
  }

  public function cycleWest() {
    for ($j = 0; $j < $this->map_limit; $j++) {
      $position_x = -1;
      for ($i = 0; $i < $this->row_limit; $i++) {
            
        switch ($this->map[$j][$i]) {
          case Board::ROLL_ROCK:
            $position_x++;
            if ($position_x !== $i) {
              $this->map[$j][$position_x] = Board::ROLL_ROCK;
              $this->map[$j][$i] = Board::EMPTY;
            }
            break;
        
            case Board::CUBE_ROCK:
              $position_x = $i;
              break;
        }
      }
    }
  }

  public function cycleSouth() {
    for ($i = 0; $i < $this->row_limit; $i++) {
      $position_y = $this->map_limit;
      for ($j = $this->map_limit - 1; $j >= 0; $j--) {
    
        switch ($this->map[$j][$i]) {
          case Board::ROLL_ROCK:
            $position_y--;
            if ($position_y !== $j) {
              $this->map[$position_y][$i] = Board::ROLL_ROCK;
              $this->map[$j][$i] = Board::EMPTY;
            }
            break;
    
            case Board::CUBE_ROCK:
              $position_y = $j;
              break;
        }
      }
    }
  }  

  public function cycleEast() {
    for ($j = 0; $j < $this->map_limit; $j++) {
      $position_x = $this->row_limit;
      for ($i = $this->row_limit - 1; $i >= 0; $i--) {
            
        switch ($this->map[$j][$i]) {
          case Board::ROLL_ROCK:
            $position_x--;
            if ($position_x !== $i) {
              $this->map[$j][$position_x] = Board::ROLL_ROCK;
              $this->map[$j][$i] = Board::EMPTY;
            }
            break;
    
            case Board::CUBE_ROCK:
              $position_x = $i;
              break;
        }
      }
    }
  }  

  public function getLoad() {
    $result = [];
  
    for ($i = 0; $i < $this->row_limit; $i++) {
      $result[$i] = 0;
      for ($j = 0; $j < $this->map_limit; $j++) {
        if ($this->map[$j][$i] === Board::ROLL_ROCK) {
          $load = $this->map_limit - $j;
          $result[$i] += $load;
        }
      }
    }
  
    return $result;
  }
}



// --------------------------------------------------------------
// Main
// --------------------------------------------------------------
require_once('data.php');

$map = [];
foreach ($data as $item) {
  $row = str_split($item);
  $map[] = $row;
}

$board    = new Board($map);
$board->rows = $board->rearrange();

// Question 1
$sum  = 0;
$rows = $board->shift();

// now that we've shifted the rolled rocks within the columns create rows
$rows = $board->getRows($rows);

for ($i = 0; $i < count($rows); $i++) {
  $values = array_count_values($rows[$i]);  
  $sum   += (isset($values[Board::ROLL_ROCK])) ? ($values[Board::ROLL_ROCK] * (count($rows) - $i)) : 0;  
}

print '<p>Q1: The total load on the north support beams is ' . $sum . '</p>';

// Question 2
$board = new Board($map);

$cycles  = 1000000000;
$sum     = 0;
$loops   = [];
$loads   = [];

for ($i = 1; $i <= $cycles; $i++) {
  $board->cycleNorth();
  $board->cycleWest();
  $board->cycleSouth();
  $board->cycleEast();

  $row  = $board->getLoad();
  $id   = md5(implode('', $row)); 

  if (!isset($loops[$id])) {
    $loops[$id] = $i;
    $loads[$i] = array_sum($row);  
  } else {
    // The map is starting again from the original position.  There are only so many combinations before the system loops around again.
    $previous = $loops[$id];
    $restart  = $i - $previous;
    $index    = $previous + (($cycles - $previous) % $restart);
    $sum      = $loads[$index];
    break;
  }
}

print '<p>The total load on the north support beams is ' . $sum . '</p>';
