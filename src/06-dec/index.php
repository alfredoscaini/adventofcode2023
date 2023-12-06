<?php 
class Race {
  use Rules;

  public function __construct($data = []) {}

  public function optimize($time = 0, $record = 0) {
    $result_count = 0;
    $speed   = $time; // the race time

    while ($speed != 0) {
      $time_remaining = $time - $speed;
      $distance       = $this->distance($time_remaining, $speed);
      
      if ($distance > $record) {
        $result_count++;
      }

      $speed--;
    }

    return $result_count;
  }
}


trait Rules {
  public function distance($time_remaining = 0, $speed = 0) {
    $distance = $time_remaining * $speed;

    return $distance;
  }
}

// Question 1
$races = [
  1 => [34, 204],
  2 => [90, 1713],
  3 => [89, 1210],
  4 => [86, 1780]
];

$race = new Race();

$results = [];
foreach ($races as $id => $result) {
  $time     = $result[0];
  $distance = $result[1];

  $results[$id] = $race->optimize($time, $distance);
}

$winning_combinations = 1;
foreach ($results as $key => $result) {
  $winning_combinations *= $result; 
}

print '<p>The total number of ways to beat the records of each race is ' . $winning_combinations . '</p>';

// Question 2
$races = [34908986, 204171312101780];

$race = new Race();

$count = 0;
$time     = $races[0];
$distance = $races[1];
$count    = $race->optimize($time, $distance);

print '<p>The total number of ways to beat the records of each race is ' . $count . '</p>';


