<?php 

class Universe {
  public $galaxies  = [];
  public $map       = [];
  public $expansion = [
    'rows'    => [],
    'columns' => []
  ];

  public $galaxy_icon;
  public $empty_space;
  public $expansion_factor;


  public function __construct($data = [], $expansion = 0) {
    $this->galaxy_icon        = '#';
    $this->empty_space        = '.';
    $this->expansion_factor   = $expansion;

    $this->map = $this->map($data);
    $this->galaxies = $this->find();
  }

  private function map($data = []) : array {
    $rows           = [];
    $empty_columns  =  array_fill(0, 140, 0);

  
    for ($i = 0; $i < count($data); $i++) {
      $rows[$i]   = str_split($data[$i]);
      $empty_row  = true;
      
      for ($j = 0; $j < count($rows[$i]); $j++) {        
        if ($rows[$i][$j] == $this->empty_space) {
          $empty_columns[$j] += 1;

          if ($empty_columns[$j] == 140) {
            $this->expansion['columns'][] = $j;
          }
        } else {
          $empty_row = false;
        }
      }

      // Business Rule: if there is a row of empty space, add another row
      if ($empty_row) {
        $this->expansion['rows'][] = $i;
      }
    }

    return $rows;
  }

  private function find() : array {
    $result = [];
    $id     = 0;

    for ($i = 0; $i < count($this->map); $i++) {
      $row = $this->map[$i];

      for ($j = 0; $j < count($row); $j++) {
        if ($row[$j] == $this->galaxy_icon) {
          // Our coordinates within the universe
          $x = $j;
          $y = $i;

          // Apply the expansion factor to our coordinates. Check to see if the galaxy
          // comes after one of the rows or columns within the expansion
          list($x, $y) = $this->getExpansionCoordinates(['x' => $x, 'y' => $y], $id);
          $result[$id] = new Galaxy($id++, $x, $y);
        }
      }
    }

    return $result;
  }

  private function getExpansionCoordinates($coordinate = [], $id = 0) : array {
    $result = [];

    $expansion_count_x = 0;
    $expansion_count_y = 0;

    for ($x = 0; $x < count($this->expansion['columns']); $x++) {
      $coordinate_x = $this->expansion['columns'][$x];

      if ($coordinate['x'] > $coordinate_x) {
        $expansion_count_x++;
      }
    }

    for ($y = 0; $y < count($this->expansion['rows']); $y++) {
      $coordinate_y = $this->expansion['rows'][$y];

      if ($coordinate['y'] > $coordinate_y) {
        $expansion_count_y++;
      }
    }

    $result = [
      $coordinate['x'] + ($expansion_count_x * $this->expansion_factor),
      $coordinate['y'] + ($expansion_count_y * $this->expansion_factor)
    ];

    return $result;
  }
}

class Galaxy {
  use Distance;

  public $id = 0;
  public $coordinates = ['x' => 0, 'y' => 0];


  public function __construct($id, $x, $y) {
    $this->id = $id;
    $this->coordinates['x'] = $x;
    $this->coordinates['y'] = $y;
  }
}

trait Distance {
  public function calculate($galaxy) {
      return abs($galaxy->coordinates['x'] - $this->coordinates['x']) + abs($galaxy->coordinates['y'] - $this->coordinates['y']);
  }
}


// --------------------------------------------------
// Main
// --------------------------------------------------
require_once('data.php');

$expansion = 1; // default is 1. We are adding 1 to sum up to 2 (double expansion)
$universe  = new Universe($data, $expansion);

$sum = 0;
$distances = [];
foreach ($universe->galaxies as $current) {
  foreach ($universe->galaxies as $next) {
    if ($current === $next) { continue; }

    if ($current->id < $next->id) {
      $combo = $current->id . '-' . $next->id;
    } else {
      $combo = $next->id . '-' . $current->id;
    }

    if (!array_key_exists($combo, $distances)) {
      $distances[$combo] = $current->calculate($next);
      $sum += $distances[$combo];
    }
  }
}

print 'Q1: The sum of these lengths is ' . $sum . '</p>';

// Question 2
$expansion = 1000000 - 1; // default is 1. We are adding 999999 to sum up to a million
$universe = new Universe($data, $expansion);

$sum = 0;
$distances = [];
foreach ($universe->galaxies as $current) {
  foreach ($universe->galaxies as $next) {
    if ($current === $next) { continue; }

    if ($current->id < $next->id) {
      $combo = $current->id . '-' . $next->id;
    } else {
      $combo = $next->id . '-' . $current->id;
    }

    if (!array_key_exists($combo, $distances)) {
      $distances[$combo] = $current->calculate($next);
      $sum += $distances[$combo];
    }
  }
}

print 'Q2: The sum of these lengths with the expansion factor is ' . $sum . '</p>';