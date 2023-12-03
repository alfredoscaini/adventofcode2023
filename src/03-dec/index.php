<?php 

class Chart {
  public $rows        = [];
  public $symbols     = [];
  public $row_length  = 0;

  public function __construct($chart = '') {
    list($this->rows, $this->symbols) = Extract::data($chart);

    $this->row_length = (isset($this->rows[0])) ? count($this->rows[0]) : 0;
  }
} // end of Chart

class Extract {
  private static $symbols      = [];

  public static function data($data = '') {
    $rows         = [];
    $row_index    = 0;
    $column_index = 0;    

    foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
      
      $column_index = 0;

      foreach(str_split($line) as $column) {
        $rows[$row_index][$column_index] = self::filter($column);
        $column_index++;
      }

      $row_index++;
    } 

    return [$rows, self::$symbols];
  }

  private static function filter($column = '') {
    $data = '';
    
    switch ($column) {
      case '.':
        $data = '';
        break;
      
      case !is_numeric($column):
        if ($column == 0) { 
          $data = $column;
          break;
        }

        $count = (!isset(self::$symbols[$column])) ? 1 : self::$symbols[$column] + 1;
        self::$symbols[$column] = $count;
        $data = $column;
        break;

      case is_numeric($column):
        $data = $column;
        break;       
    }

    return $data;
  }
} // end of Extract


class Gear {
  public $symbols = [];
  private $chart;

  public function __construct($chart) {
    $this->chart = $chart;
    $symbols = array_keys($this->chart->symbols);
    foreach ($symbols as $key) {
      $this->symbols[$key] = [];
    }   
  }

  public function isAdjacentToSymbol($rows = [], $row_id = 0, $column_id = 0, $row_length = 0, $symbols = []) {
    $found = false;
  
    // The symbol can be in front, behind, above, below, offset by 1 above, or offset by 1 below
    $possibilities = [
      'index_top'                 => null,
      'index_bottom'              => null,
      'index_left'                => null,
      'index_right'               => null,
      'index_top_offset_right'    => null,
      'index_top_offset_left'     => null,
      'index_bottom_offset_right' => null,
      'index_bottom_offset_left'  => null
    ];
  
    $possibilities['index_top']                     = ($row_id != 0)                    ? true : false;
    $possibilities['index_bottom']                  = ($row_id < count($rows) - 2)      ? true : false;
    $possibilities['index_left']                    = ($column_id != 0)                 ? true : false;
    $possibilities['index_right']                   = ($column_id < ($row_length - 2))  ? true : false;
  
    $possibilities['index_bottom_offset_right']     = ($possibilities['index_bottom'] && $possibilities['index_right']) ? true : false;
    $possibilities['index_bottom_offset_left']      = ($possibilities['index_bottom'] && $possibilities['index_left'])  ? true : false;
  
    $possibilities['index_top_offset_right']        = ($possibilities['index_top'] && $possibilities['index_right']) ? true : false;
    $possibilities['index_top_offset_left']         = ($possibilities['index_top'] && $possibilities['index_left'])  ? true : false;
  
    // check if this value is adjacent to a symbol
    foreach ($possibilities as $option => $valid_option) {
      if (!$valid_option) { continue; }
  
      switch ($option) {
        case 'index_top':
          $position_x = $column_id;
          $position_y = $row_id - 1;
          break;
        
        case 'index_bottom':
          $position_x = $column_id;
          $position_y = $row_id + 1;
          break;
          
        case 'index_left':
          $position_x = $column_id - 1;
          $position_y = $row_id;
          break;
  
        case 'index_right':
          $position_x = $column_id + 1;
          $position_y = $row_id;
          break;
        
        case 'index_top_offset_right':
          $position_x = $column_id + 1;
          $position_y = $row_id - 1;
          break;
        
        case 'index_top_offset_left':
          $position_x = $column_id - 1;
          $position_y = $row_id - 1;
          break;
  
        case 'index_bottom_offset_right':
          $position_x = $column_id + 1;
          $position_y = $row_id + 1;
          break;
          
        case 'index_bottom_offset_left':
          $position_x = $column_id - 1;
          $position_y = $row_id + 1;
          break;
      }
  
      $character = $rows[$position_y][$position_x];
      if (in_array($character, $symbols)) {

        // This is for part 2 of the question.  We need to capture the special character position as well as 
        // The position of the number it is associated to.
        $character_position = $position_y . '-' . $position_x;
        list($starting_position, $position_number)    = $this->obtainNumberAndPosition($rows[$row_id], $column_id);

        $number_position = $row_id . '-' . $starting_position; // we now have to account for row value if the number is above or below the asterix
        $this->symbols[$character][$character_position][$number_position] = $position_number;

        $found = true;
      }
    }
  
    return $found;
  } // end of isAdjacentToSymbol


  function obtainNumberAndPosition($row = [], $column_id = 0) {
    $number         = $row[$column_id];
    $count          = count($row);
    $starting_index = $column_id;
  
    // --------------------------------------------------
    // Don't judge me ... :p
    for ($i = ($column_id + 1); $i < $count; $i++) {
      $character = $row[$i];
  
      if (!is_numeric($character)) {
        break;
      } else {
        $number .= $character;
      }    
    }
  
    for ($i = ($column_id - 1); $i >= 0; $i--) {
      $character = $row[$i];
  
      if (!is_numeric($character)) {
        break;
      } else {
        $starting_index--;
        $number = $character . $number;
      }    
    }
    // -------------------------------------------------
  
    return [$starting_index, $number];
  } // end of obtainNumberAndPosition
  
} // end of Gears




// ---------------------------------------------------------------
// Main
// ---------------------------------------------------------------
require_once('data.php');

$chart   = new Chart($data);
$gear    = new Gear($chart);
$numbers = [];


// --------------------------------------------------------
// Question 1. Count all the numbers adjacent to a symbol
foreach ($chart->rows as $row_id => $row) {
  if (!isset($numbers[$row_id])) {
    $numbers[$row_id] = [];
  }

  foreach ($row as $column_id => $value) {
    if (is_numeric($value) && $gear->isAdjacentToSymbol($chart->rows, $row_id, $column_id, $chart->row_length, array_keys($chart->symbols))) {
      list($position, $number) = $gear->obtainNumberAndPosition($row, $column_id);

      $numbers[$row_id][$position] = $number;
    }
  }
}

$total = 0;
foreach ($numbers as $row) {
  foreach ($row as $number) {
    $total += $number;
  }
}

print '<p>The total sum of numbers adjacent to symbols is : ' . $total;

// --------------------------------------------------------
// Question 2: What is the sum of all of the gear ratios in your engine schematic?
// From the Gear class we now have the position of each symbol. We need to loop through all the * positions and find if there are two adjacent 
// numbers. If so, retrieve those numbers.

$product = 0;
foreach ($gear->symbols['*'] as $index => $numbers) {
  if (count($numbers) == 2) {    
    $keys = array_keys($numbers);
    $product += $numbers[$keys[0]] * $numbers[$keys[1]];
  }
}

print '<p>The sum of the gear ratios is ' . $product . '</p>';