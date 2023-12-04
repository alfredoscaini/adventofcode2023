<?php

class Set {
  use Rules;

  public $id;
  public $winning_numbers = [];
  public $numbers         = [];
  public $matches         = [];
  public $points          = 0;
  public $scratchcards    = [];

  public function __construct($key = 0, $data = []) {
    $this->id              = $key;
    $this->winning_numbers = $data['winning_numbers'];
    $this->numbers         = $data['numbers'];

  }
}

trait Rules {
  public function match($winning_numbers = [], $numbers = []) : array {
    return array_intersect($winning_numbers, $numbers);
  }

  public function points($found_numbers = []) : int {
    $points = 0;

    for ($i = 0; $i < count($found_numbers); $i++) {
      if ($i == 0) { $points = 1; continue; }

      $points *= 2;
    }

    return $points;
  }   

  public function scratchcards($sets = [], $set = null) : int {
    $card_count     = count($set->matches);
    $starting_index = $set->id;
    $limit          = $card_count + $set->id;
    $total          = 1;

  
    // how many total scratchcards do you end up with using this card?
    for ($i = $starting_index; $i < $limit; $i++) {
      if ($i == count($sets)) { break; }
      $total += $this->scratchcards($sets, $sets[$i]);
    }

    return $total;
  }

} // end of Rules

// -----------------------------------------------------------
// Main
// -----------------------------------------------------------
require_once('data.php');

// Question 1: How many points are the cards worth in total?
$sets   = [];
$points = 0;

foreach ($data as $key => $item) {
  $set = new Set($key, $item);

  $set->matches = $set->match($set->winning_numbers, $set->numbers);
  $set->points  = $set->points($set->matches);

  $sets[] = $set;
  $points += $set->points;
}

print '<p>The total number of points is ' . $points . '</p>';

// Question 2: 
// Process all of the original and copied scratchcards until no more scratchcards are won. 
// Including the original set of scratchcards, how many total scratchcards do you end up with?
$total_card_count = 0;
foreach ($sets as $set) {
  $set->scratchcards = $set->scratchcards($sets, $set);
  $total_card_count += $set->scratchcards;
}


print '<p>The total number of free plays is ' . $total_card_count . '</p>';
