<?php

class Pattern {
  use Methods;

  const ASH   = '.';
  const ROCKS = '#';

  public $id = 0;
  public $map = [];

  public function __construct($id = 0) {
    $this->id = $id;
  }
}

trait Methods {

  private function transpose() {
    $data = $this->map;
    return array_map(null, ...$data);
  }
  
  public function vertical($skip_index = null) {
    $pattern      = new Pattern();
    $pattern->id  = -($this->id);
    $pattern->map = $this->transpose();

    return $pattern->horizontal($skip_index);
  } 

  public function horizontal($skip_index = null) {
    $row    = $this->map[0];
    $column = $this->map;

    for ($index = 0; $index < count($row); $index++) {
      if ($index === $skip_index) {
        continue;
      }
      
      $length = min($index + 1, count($row) - $index - 1);
      
      if ($length === 0) {
        return null;
      }

      for ($i = 0; $i < count($column); $i++) {
        for ($j = 0; $j < $length; $j++) {
          if ($this->map[$i][$index + $j + 1] !== $this->map[$i][$index - $j]) {
            continue 3;
          }
        }
      }

      return $index;
    }

    return null;
  }

  public function switch($x = 0, $y = 0) {
    $current = $this->map[$y][$x];
    
    if ($current === Pattern::ASH) {
      $this->map[$y][$x] = Pattern::ROCKS;
    } else {
      $this->map[$y][$x] = Pattern::ASH;
    }
  }
}

// ------------------------------------------------------------------
// Main
// ------------------------------------------------------------------
require_once('data.php');

$index    = 0;
$sum      = 0;
$patterns = [];

$pattern = new Pattern($index, []);

foreach ($data as $item) {
  $record = str_split($item);

  if (count($record) > 0) {
    $pattern->map[] = $record;
  } else {
    $index++;
    $patterns[] = $pattern;
    $pattern = new Pattern($index, []);
  }
}

$patterns[] = $pattern;


foreach ($patterns as $pattern) {
  $vertical = $pattern->vertical();
  
  if ($vertical !== null) {
    $sum += ($vertical + 1) * 100;
  } else {
    $horizontal = $pattern->horizontal();
    if (!is_null($horizontal)) {       
      $sum += $horizontal + 1;
    }
  }
}

print 'Q1: Summarizing all my notes gives me ' . $sum . '</p>';


// Question 2:
$sum = 0;
foreach ($patterns as $pattern) {
  $vertical   = $pattern->vertical();
  $horizontal = $pattern->horizontal();

  $max_x = count($pattern->map[0]);
  $max_y = count($pattern->map);

  for ($x = 0; $x < $max_x; $x++) {
    for ($y = 0; $y < $max_y; $y++) {
      $clone = clone $pattern;
      $clone->switch($x, $y);

      $row = $clone->horizontal($horizontal);
      if ($row !== null) {
        $sum += $row + 1;
        continue 3;
      }

      $column = $clone->vertical($vertical);
      if ($column !== null) {
        $sum += ($column + 1) * 100;
        continue 3;
      }
    }
  }
}

print '<p>Q2: The number obtained after summarizing the new reflection line in each pattern is ' . $sum . '</p>';