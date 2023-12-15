<?php
class Code {
  use Rules;

  const ADD      = 1;
  const SUBTRACT = 2;

  public $code = '';
  public $current_value;
  public $focal_length;
  public $operation;
  public $label;
  
  public function __construct($code = '') {
    $this->code = $code;
    $this->current_value = $this->calculateCurrentValue($code);
    $this->focal_length  = $this->getFocalLength();
    $this->operation     = $this->getOperation();
    $this->label         = $this->getLabel();
  }
}

trait Rules {
  /**
   * 
   * 1. Determine the ASCII code for the current character of the string.
   * 2. Increase the current value by the ASCII code you just determined.
   * 3. Set the current value to itself multiplied by 17.
   * 4. Set the current value to the remainder of dividing itself by 256.
   * 
   */
  public function calculateCurrentValue($str) {
    $characters    = str_split($str);
    $current_value = 0;

    foreach ($characters as $character) {
      $ascii = ord($character);
      $current_value += $ascii;
      $current_value *= 17;
      $current_value = fmod($current_value, 256);
    }

    return $current_value;
  }

  private function getLabel() : string {
    return preg_replace("/[^a-zA-Z]+/", "", $this->code);
  }

  private function getOperation() : int {
    return (preg_replace("/[^=|-]+/", "", $this->code) == '=') ? self::ADD : self::SUBTRACT;
  }

  private function getFocalLength() : int {
    $value = preg_replace("/[^0-9]+/", "", $this->code);
    return (is_numeric($value)) ? $value : 0;
  }
}


// -----------------------------------------------------------
// Main
// -----------------------------------------------------------
require_once('data.php');

$sum = 0;
foreach ($data as $item) {
  $code = new Code($item);
  $sum += $code->current_value;
  $codes[] = $code;
}


// Q1:
print '<p>Q1: The sum of the results is ' . $sum . '</p>';


// Q2. 
$boxes = array_fill(0, 256, 0); // an array of ASCII indexes (0-255)
for ($i = 0; $i < count($boxes); $i++) {
  $boxes[$i] = [];
}

foreach ($codes as $code) {
  $index = $code->calculateCurrentValue($code->label); // get the ASCII index of the label
  $box   = $boxes[$index];

  if ($code->operation == Code::SUBTRACT) {
   unset($box[$code->label]);
  }

  if ($code->operation == Code::ADD) {
    $box[$code->label] = $code->focal_length;
  }

  $boxes[$index] = $box;
}


// Tally up the counts
$sum = 0;
for ($i = 0; $i < count($boxes); $i++) {
  $box_value = $i + 1;
  $position  = 1;
  $total     = 0;

  foreach ($boxes[$i] as $key => $value) {
    $total += $box_value * $position * $value;
    $position++;
  }    

  $sum += $total;
}

print '<p>Q2: The focusing power of the resulting lens configuration ' . $sum . '</p>';