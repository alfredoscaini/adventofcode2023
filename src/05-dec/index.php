<?php 

class Map {
  use Extract;

  private $id;
  public $seed;
  public $soil;
  public $fertilizer;
  public $water;
  public $light;
  public $temperature;
  public $humidity;
  public $location;

  public function __construct($id = 0, $seed = 0) {
    $this->id   = $id;
    $this->seed = $seed;
  }
}

trait Extract {
  public function getID($id = 0, $data = []) {
    $result = $id;

    foreach ($data as $item) {      
      $destination_range_start = $item[0];
      $source_range_start      = $item[1];        
      $range_legnth            = $item[2];

      $source_range_end        = $source_range_start + $range_legnth;


      if (($id > $source_range_start) && ($id < $source_range_end)) {
        $increment = $id - $source_range_start;
        $result = $destination_range_start + $increment;
      }
    }

    return $result;
  }
}


function findLowest($location_min, $lowest, $range, $seed_to_soil, $soil_to_fertilizer, $fertilizer_to_water, $water_to_light, $light_to_temperature, $temperature_to_humidity, $humidity_to_location) {
  
  for ($seed = $lowest; $seed <= $range; $seed++) {
    $id  = 0;
    $map = new Map($id, $seed);

    $map->soil        = $map->getID($map->seed, $seed_to_soil);
    $map->fertilizer  = $map->getID($map->soil, $soil_to_fertilizer);
    $map->water       = $map->getID($map->fertilizer, $fertilizer_to_water);
    $map->light       = $map->getID($map->water, $water_to_light);
    $map->temperature = $map->getID($map->light, $light_to_temperature);
    $map->humidity    = $map->getID($map->temperature, $temperature_to_humidity);
    $map->location    = $map->getID($map->humidity, $humidity_to_location);

    if (is_null($location_min)) {
      $location_min = $map->location;
    }

    if ($location_min > $map->location) {
      $location_min = $map->location;
    }

    yield $location_min;
  }
}

// ---------------------------------------------------------
// Main
// ---------------------------------------------------------
require_once('data.php');

$maps = [];
$id   = 0;
foreach ($seeds as $seed) {
  $maps[] = new Map($id++, $seed);
}

$location_min = null;
foreach ($maps as $map) {
  $map->soil        = $map->getID($map->seed, $seed_to_soil);
  $map->fertilizer  = $map->getID($map->soil, $soil_to_fertilizer);
  $map->water       = $map->getID($map->fertilizer, $fertilizer_to_water);
  $map->light       = $map->getID($map->water, $water_to_light);
  $map->temperature = $map->getID($map->light, $light_to_temperature);
  $map->humidity    = $map->getID($map->temperature, $temperature_to_humidity);
  $map->location    = $map->getID($map->humidity, $humidity_to_location);

  if (is_null($location_min)) {
    $location_min = $map->location;
  }

  if ($location_min > $map->location) {
    $location_min = $map->location;
  }
}

// Question 1: what is the lowest location value?
print '<p>The lowest location value is ' . $location_min . '</p>';

// Question 2: What is the lowest location value from a range of seeds?

$location_min = null;
foreach ($seed_ranges as $key => $item) {
  $seed_start  = $item[0];
  $range       = $seed_start + $item[1];

  for ($seed = $seed_start; $seed <= $range; $seed++) {
    $id  = 0;
    $map = new Map($id, $seed);

    $map->soil        = $map->getID($map->seed, $seed_to_soil);
    $map->fertilizer  = $map->getID($map->soil, $soil_to_fertilizer);
    $map->water       = $map->getID($map->fertilizer, $fertilizer_to_water);
    $map->light       = $map->getID($map->water, $water_to_light);
    $map->temperature = $map->getID($map->light, $light_to_temperature);
    $map->humidity    = $map->getID($map->temperature, $temperature_to_humidity);
    $map->location    = $map->getID($map->humidity, $humidity_to_location);    
  }

  if (is_null($location_min)) {
    $location_min = $map->location;
  }

  if ($location_min > $map->location) {
    $location_min = $map->location;
  }

}

print '<p>The lowest location value is ' . $location_min . '</p>';