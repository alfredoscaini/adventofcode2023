<?php 

class Set {
  use Maths;

  public $id  = 0;
  public $set = [];
  public $reductions = [];

  public function __construct($id = 0, $set = []) {
    $this->id  = $id;
    $this->set = $set;
  }
}

trait Maths {
  public function reduce($data = []) {
    $count     = count($data);
    $all_zeros = 0;
    foreach ($data as $number) {
      if ($number == 0) { $all_zeros++; }
    }

    if ($all_zeros == $count) {
      return;
    }

    $previous = array_shift($data);

    $reduce   = array_map(function ($next) use (&$previous) {
      $difference  = $next - $previous;
      $previous    = $next;
    
      return $difference;
    }, $data);

    $this->reductions[] = $reduce;

    $this->reduce($reduce);
    
    return;
  }

  public function extrapolate($index = 0, $previous_value = 0, $beginning = true) {
    $total = 0;

    if ($index < 0) {  
      $set = $this->set;
    } else {
      $set = $this->reductions[$index];
    }

    $value = ($beginning) ? array_shift($set) : array_pop($set);
    $total = ($beginning) ? ($value - $previous_value) : ($value + $previous_value);
        
    if ($index >= 0) {
      $index--; // go to the set above and add/subtract the sum      
      $total = $this->extrapolate($index, $total, $beginning);
    }
    
    return $total;
  }

}

// ------------------------------------------------------------
// Main
// ------------------------------------------------------------
require_once('data.php');

$sets = [];
$id   = 1;

foreach ($data as $set) {
  $sets[$id] = new Set($id++, $set);  
  $sets[$id]->reduce($set);
  $sets[$id]->reductions = $sets[$id]->reductions;
}

// Question 1: the sum of these extrapolated values
$sum  = 0;
foreach ($sets as $key => $set) {  
  $sum += $set->extrapolate(count($set->reductions) - 1, 0, false);
}

print 'Q1: The sum of these extrapolated values is ' . $sum . '</p>';


// Question 2: the sum of these extrapolated values from position 0
$sum  = 0;
foreach ($sets as $key => $set) {
  $sum += $set->extrapolate(count($set->reductions) - 1, 0, true);
}

print 'Q2: The sum of the extrapolated values from the beginning is ' . $sum . '</p>';