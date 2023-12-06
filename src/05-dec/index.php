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

class MapRange {
  use Extract;

  private $id;
  public $seedRange;
  public $soilRange;
  public $fertilizerRange;
  public $waterRange;
  public $lightRange;
  public $temperatureRange;
  public $humidityRange;
  public $locationRange;

  public function __construct($id = 0, $min = 0, $max = 0) {
    $this->id   = $id;
    $this->seedRange = [
      'min' => $min, 
      'max' => $max
    ];
  }
}

trait Extract {
  public function getID($id = 0, $data = []) {
    $result = $id;

    foreach ($data as $item) {      
      $destination_range_start = $item[0];
      $source_range_start      = $item[1];        
      $range_length            = $item[2];

      $source_range_end        = $source_range_start + $range_length;


      if (($id > $source_range_start) && ($id < $source_range_end)) {
        $increment = $id - $source_range_start;
        $result = $destination_range_start + $increment;
      }
    }

    return $result;
  }

  function getRange($range = [], $data = []) {
    $source_min = $range['min'];
    $source_max = $range['max'];
  
    $result_min = $source_min;
    $result_max = $source_max;
  
    foreach ($data as $item) {
      $destination_range_start = $item[0];
      $source_range_start      = $item[1];        
      $range_length            = $item[2];
  
      $source_range_end       = $source_range_start + $range_length;

      if ( ($source_min > $source_range_start) && ($source_min < $source_range_end)) {
        $increment = $source_min - $source_range_start;
        $result_min = $destination_range_start + $increment;
      }

      if ( ($source_max > $source_range_start) && ($source_max < $source_range_end)) {
        $increment  = $source_max - $source_range_start;
        $result_max = $destination_range_start + $increment;
      }
    }
  
    return [
      'min' => $result_min, 
      'max' => $result_max
    ];
  }
}


// ---------------------------------------------------------
// Main
// ---------------------------------------------------------
date_default_timezone_set("America/New_York");
require_once('data.php');

// Question 1: what is the lowest location value?
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

print '<p>The lowest location value is ' . $location_min . '</p>';

// Question 2: What is the lowest location value from a range of seeds?
$maps         = [];
foreach ($seed_ranges as $key => $item) {
  $min       = $item[0];
  $max       = $min + $item[1];
  $maps[]     = new MapRange($key, $min, $max);
}

$location_min = null;
foreach ($maps as $map) {
  $map->soilRange        = $map->getRange($map->seedRange, $seed_to_soil);
  $map->fertilizerRange  = $map->getRange($map->soilRange, $soil_to_fertilizer);
  $map->waterRange       = $map->getRange($map->fertilizerRange, $fertilizer_to_water);
  $map->lightRange       = $map->getRange($map->waterRange, $water_to_light);
  $map->temperatureRange = $map->getRange($map->lightRange, $light_to_temperature);
  $map->humidityRange    = $map->getRange($map->temperatureRange, $temperature_to_humidity);
  $map->locationRange    = $map->getRange($map->humidityRange, $humidity_to_location);

  if (is_null($location_min)) {
    $location_min = $map->locationRange['min'];
  }

  if ($location_min > $map->locationRange['min']) {
    $location_min = $map->locationRange['min'];
  }
}


print '<p>The minimum location is ' . $location_min . '</p>';

print '<pre>';
print_r($maps);

exit;