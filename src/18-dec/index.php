<?php 

class Instruction {
  public $id;
  public $direction;
  public $amount;
  public $color_instruction;

  public function __construct($id = 0, $direction = '', $amount = 0, $color = '') {
    $this->id        = $id;
    $this->direction = $direction;
    $this->amount    = $amount;

    $hexidecimal = str_replace('#', '', $color);
    $this->color_instruction = [
      'direction' => $this->getDirection(substr($hexidecimal, -1)),
      'amount'    => hexdec(substr($hexidecimal, 0, 5))
    ];
  }

  private function getDirection($direction = '') {

    switch ($direction) {
      case '0':
        $direction = Map::EAST;
        break;
        
      case '1':
        $direction = Map::SOUTH;
        break;

      case '2':
        $direction = Map::WEST;
        break;

      case '3':
        $direction = Map::NORTH;
        break;
    }

    return $direction;
  }
}

class Map {
  const NORTH = 'U';
  const EAST  = 'R';
  const SOUTH = 'D';
  const WEST  = 'L';

  const TRENCH = '#';
  const GROUND = '.';

  public $map;

  public $directions = [
    Map::NORTH => [0, -1], 
    Map::SOUTH => [0, 1], 
    Map::EAST  => [1, 0],
    Map::WEST  => [-1, 0]    
  ];

  public $start;
  public $points = [];
  public $column_points = 0;
  public $row_points    = 0;

  public function __construct() {
    $this->start = [0, 0];
  }

  public function build($current, Instruction $instruction, $min = 0, $max = 0, $use_color_instruction = false) {
    list($current_x, $current_y) = $current;
    
    $column_points = 0;
    $row_points    = 0;

    $range = [
      'min' => [],
      'max' => []
    ];

    $direction = ($use_color_instruction) ? $instruction->color_instruction['direction'] : $instruction->direction;
    $amount    = ($use_color_instruction) ? $instruction->color_instruction['amount']    : $instruction->amount;

    switch($direction) {
      case self::NORTH:
        $next_x = $current_x;
        $next_y = $current_y - $amount;

        $row_points = $amount;        
        break;

      case self::SOUTH:
        $next_x = $current_x;
        $next_y = $current_y + $amount;

        $row_points = $amount * -1;
        break;

      case self::EAST:
        $next_x = $current_x + $amount;
        $next_y = $current_y;

        $column_points = $amount;
        break;

      case self::WEST:
        $next_x = $current_x - $amount;
        $next_y = $current_y;

        $column_points = $amount * -1;
        break;
    }

    $range['min'] = ['x' => min($current_x, $next_x), 'y' => min($current_y, $next_y)];
    $range['max'] = ['x' => max($current_x, $next_x), 'y' => max($current_y, $next_y)];
    
    // We just need the X coordinate (columns) as the rows will always have a # value 
    $min = min($min, $range['min']['x']);
    $max = max($max, $range['max']['x']);

    $this->row_points    += $row_points;
    $this->column_points += $column_points;

    $this->points[] = [
      $this->row_points,
      $this->column_points
    ];

    return [$next_x, $next_y, $min, $max];
  } // end of build
}



// ------------------------------------------------------------ 
// Main
// ------------------------------------------------------------
require_once('data.php');

$map          = new Map();
$instructions = [];
$index        = 1;
$position     = [0, 0];
$min_column   = 0;
$max_column   = 0;
$borders      = 0;
$area         = 0;

foreach ($data as $dig) {
  $instruction = new Instruction($index, $dig[0], $dig[1], $dig[2]);  
  $instructions[$index++] = $instruction;
  $borders += $instruction->amount;

  list($position_x, $position_y, $min_column, $max_column) = $map->build($position, $instruction, $min_column, $max_column, false);  
  $position = [$position_x, $position_y];    
}

for ($i = 1; $i < count($map->points) - 1; $i++){
  $area += ($map->points[$i][0] * ($map->points[$i+1][1] - $map->points[$i-1][1]));
}


$area = abs($area)/2;
$interior = $area - $borders/2 + 1;

print '<p>The total cubic meters of lava this pattern can hold is ' . $interior + $borders . '</p>';


$map          = new Map();
$instructions = [];
$index        = 1;
$position     = [0, 0];
$min_column   = 0;
$max_column   = 0;
$borders      = 0;
$area         = 0;

foreach ($data as $dig) {
  $instruction = new Instruction($index, $dig[0], $dig[1], $dig[2]);  
  $instructions[$index++] = $instruction;
  $borders += $instruction->color_instruction['amount'];

  
  list($position_x, $position_y, $min_column, $max_column) = $map->build($position, $instruction, $min_column, $max_column, true);  
  $position = [$position_x, $position_y];      
}

for ($i = 1; $i < count($map->points) - 1; $i++){
  $area += ($map->points[$i][0] * ($map->points[$i+1][1] - $map->points[$i-1][1]));
}

$area = abs($area)/2;
$interior = $area - $borders/2 + 1;

print '<p>The total cubic meters of lava this new pattern can hold is ' . $interior + $borders . '</p>';
