<?php

class Path {

  const LEFT  = 'L';
  const RIGHT = 'R';

  public $location = '';
  public $paths    = [];

  public function __construct($location = '', $left = '', $right = '') {
    $this->location = $location;
    $this->paths[self::LEFT] = $left;
    $this->paths[self::RIGHT] = $right;
  }
}


class Rules {
  public $endsIn = '';
  public $destinations;

  public function __construct($endsIn = '') {
    $this->destinations = [];
    $this->endsIn = $endsIn;
  }

  public function getSteps($paths = [], $origin = '', $destination = '', $route = '') : int {
    $path             = $paths[$origin];
    $steps            = 0;
    $direction_index  = -1;
    $direction_length = strlen($route);
    
    do {
      // Restart the directions if they run out
      $direction_index = ($direction_index == $direction_length - 1) ? 0 : ($direction_index + 1);

      $direction = $route[$direction_index];
      $location  = $path->paths[$direction];

      if ($location != $destination) {
        $path = $paths[$location];
      }

      $steps++;  
    } while ($location != $destination);

    return $steps;
  }

  public function getStepsForPathEndingIn($paths = [], $origin = '', $route = '') : int {
    $path             = $paths[$origin];
    $steps            = 0;
    $direction_index  = -1;
    $direction_length = strlen($route);
    $found            = false;
    
    do {
      // Restart the directions if they run out
      $direction_index = ($direction_index == $direction_length - 1) ? 0 : ($direction_index + 1);

      $direction = $route[$direction_index];
      $location  = $path->paths[$direction];

      if (strpos($location, $this->endsIn, -1) == 2) {
        $this->destinations[] = $location;
        $found = true;
      } else {
        $path = $paths[$location];
      }

      $steps++;  
    } while (!$found);

    return $steps;
  }

  public function getLCM($data = []) {
    $count = count($data);
    $sum = $data[0];
  
    for ($i = 1; $i < $count; $i++) {
      $sum = ($data[$i] * $sum) / (gmp_gcd($data[$i], $sum));
    }

    return $sum;
  }
}

// ----------------------------------------------------
// Main
// ----------------------------------------------------
require_once('data.php');

$paths = [];
foreach ($data as $location => $item) {
  $paths[$location] = new Path($location, $item[0], $item[1]);
}

$rules = new Rules('Z');

// --------------------------------------------------------------------------
// Question 1: Find all the steps to get to the destination
$origin       = 'AAA';
$destination  = 'ZZZ';
$steps        = $rules->getSteps($paths, $origin, $destination, $route);

print '<p>Q1: The number of steps to get to ' . $destination . ' is ' . $steps . '</p>';

// --------------------------------------------------------------------------
// Question 2: Simultaneously go through all the nodes that end in A to get to all the nodes that end in Z
$origins         = ['AAA' , 'NBA', 'SXA', 'JVA', 'XVA', 'GRA'];
$endpoints       = ['ZZZ']; // , 'BPZ', 'BVZ', 'PSZ', 'VGZ', 'VTZ'];
$steps           = 0;

$sums = [];
foreach ($origins as $origin) {
  foreach ($endpoints as $endpoint) {

    $sums[] = $rules->getStepsForPathEndingIn($paths, $origin, $route);
  }
}

$lcm = $rules->getLCM($sums);

print '<p>Q2: The number of steps is the least common multiple (LCM) of all the results.  This value is ' . $lcm . '</p>';
