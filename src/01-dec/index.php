<?php 

function getResult($item = '', $filter = []) {
  $position = [];
  $result   = 0;
  $index    = 0;

  foreach ($filter as $key => $value) {
    $needle = $key;
    while (($index = strpos($item, $needle, $index)) !== false) {
      $position[$index] = $value;
      $index += strlen($needle);
    }
  }  

  ksort($position);
  $results = array_values($position);

  $last   = (count($results) > 0) ? count($results) - 1 : 0;
  $result = (count($results) > 0) ? ( $results[0]. $results[$last] ): 0;

  
  return $result;
}



// ======================================================
// Main
// ======================================================
require_once('data.php');

$numbers = [];
$total   = 0;
$filter  = [
  '1' => 1, 
  '2' => 2, 
  '3' => 3, 
  '4' => 4, 
  '5' => 5, 
  '6' => 6, 
  '7' => 7, 
  '8' => 8, 
  '9' => 9, 
  'one' => 1, 
  'two' => 2, 
  'three' => 3, 
  'four' => 4, 
  'five' => 5, 
  'six' => 6, 
  'seven' => 7, 
  'eight' => 8, 
  'nine' => 9
];

foreach ($data as $item) {
  $numbers[$item] = getResult(strtolower($item), $filter);  
}

foreach ($numbers as $number) {
  $total += $number;
}

print '<p>Total is : ' . $total . '</p>';
print '<p>The array contains:<pre>';
print_r($numbers);
exit;