<?php 

class Beam {
  use Transverse;

  const NORTH = 0;
  const EAST  = 1;
  const SOUTH = 2;
  const WEST  = 3;

  public $width;
  public $height;
    
  private $map;
  private $directions;

  public function __construct($map = [], $width = 0, $height = 0) {
    $this->map    = $map;
    $this->width  = $width;
    $this->height = $height;

    // North, easst, south, west
    $this->directions = [
      [0, -1], [1, 0], [0, 1], [-1, 0]
    ];
  }
}

trait Transverse {

  public function energize($data = [], $start = [], $direction = []) {
    $energized = [];
    $completed = [];
    $queue     = [
      [$start, $direction]
    ];

    while ($queue) {      
      list($position, $direction)       = array_shift($queue);
      list($position_x, $position_y)    = $position;
      list($direction_x, $direction_y)  = $direction;

      $in_bounds    = ($position_x < 0 || $position_x >= $this->width || $position_y < 0 || $position_y >= $this->height) ? true : false;
      $already_done = (isset($completed[$position_y][$position_x]) && in_array($direction, $completed[$position_y][$position_x])) ? true : false;

      if ($in_bounds || $already_done) {
        continue;
      }

      $completed[$position_y][$position_x][] = $direction;

      $item = $data[$position_y][$position_x];
      $energized[$position_y][$position_x] = true;

      switch ($item) {
        case '.':
          $queue[] = [[$position_x + $direction_x, $position_y + $direction_y], $direction];
          break;
        case '|':
          if ($direction_x === 0) {
            $queue[] = [[$position_x + $direction_x, $position_y + $direction_y], $direction];
          } else {
            $queue[] = [[$position_x, $position_y - 1], $this->directions[Beam::NORTH]];
            $queue[] = [[$position_x, $position_y + 1], $this->directions[Beam::SOUTH]];
          }
          break;
        case '-':
          if ($direction_y === 0) {
            $queue[] = [[$position_x + $direction_x, $position_y + $direction_y], $direction];
          } else {
            $queue[] = [[$position_x - 1, $position_y], $this->directions[Beam::WEST]];
            $queue[] = [[$position_x + 1, $position_y], $this->directions[Beam::EAST]];
          }
          break;
        
        case '/':
        case '\\':
          $index = array_search($direction, $this->directions, true);
          $next  = ($item === '/' && $direction_y === 0) || ($item === '\\' && $direction_x === 0) ? -1 : 1;
          $new_index = (4 + $index + $next) % 4;

          list($direction_x, $direction_y) = $this->directions[$new_index];
          
          $queue[] = [
            [$position_x + $direction_x, $position_y + $direction_y], 
            [$direction_x, $direction_y]
          ];

          break;        
      }
    }

    return array_sum(array_map('array_sum', $energized));
  }
}

// ----------------------------------------------------------
// Main
// ----------------------------------------------------------
$data = file_get_contents('data.txt');  // ugh
$data = explode("\n", trim($data));
$data = array_map('str_split', $data);

$height = count($data);
$width = count($data[0]);

$beam = new Beam($data, $width, $height);

$start     = [0, 0];
$direction = [1, 0];

$sum   = $beam->energize($data, $start, $direction);

print '<p>The number of tiles being energized is ' . $sum . '</p>';

// Question 2
$sum = 0;
for ($y = 0; $y < $beam->height; $y++) {
  $start     = [0, $y];
  $direction = [1, 0];
  $left      = $beam->energize($data, $start, $direction);

  $start     = [$beam->width - 1, $y];
  $direction = [-1, 0];
  $right     = $beam->energize($data, $start, $direction);

  $sum = max($sum, $left, $right);
}

for ($x = 1; $x < $beam->width - 1; $x++) {
  $start     = [$x, 0];
  $direction = [0, -1];
  $down      = $beam->energize($data, $start, $direction);

  $start     = [$y, $beam->height - 1];
  $direction = [0, -1];
  $up        = $beam->energize($data, $start, $direction);

  $sum = max($sum, $down, $up);
}

// 7305 is too low
print '<p>The number of tiles energized in the configuration is ' . $sum . '</p>';