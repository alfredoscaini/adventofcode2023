<?php

class Workflow {
  const ACCEPTED = 'A';
  const REJECTED = 'R';

  const CODE_X = 'x';
  const CODE_M = 'm';
  const CODE_A = 'a';
  const CODE_S = 's';  

  const LESS_THAN    = '<';
  const GREATER_THAN = '>';

  public $original;
  public $label;
  public $instructions = [];

  public function __construct($original = '', $label = '') {
    $this->original   = $original;
    $this->label      = $label;
  }

  public function setInstruction($code = '', $operation = '', $amount = 0, $next = '') {
    $this->instructions[] = [
      'code'      => $code,
      'operation' => $operation,
      'amount'    => $amount,
      'next'      => $next
    ];
  }
}

class Rating {
  public $amount;
  public $total;
  public $range;

  public function __construct() {
    $this->total = 0;
  }
  
  public function setRating($code, $amount) {
    $this->amount[$code]  = $amount;
  }

  public function setRatingRange($min, $max) {
    foreach (array_keys($this->amount) as $code) {
      $this->range[$code] = [
        'min' => $min, 
        'max' => $max
      ];
    }
  }

  public function setTotal($value) {
    $this->total += $value;
  }
}

// ---------------------------------------------------------------------
// Functions
// ---------------------------------------------------------------------
function getWorkflows($workflows = []) {
  $data  = [];
  $check = [
    Workflow::CODE_X . Workflow::GREATER_THAN, 
    Workflow::CODE_M . Workflow::GREATER_THAN, 
    Workflow::CODE_A . Workflow::GREATER_THAN, 
    Workflow::CODE_S . Workflow::GREATER_THAN,
    Workflow::CODE_X . Workflow::LESS_THAN, 
    Workflow::CODE_M . Workflow::LESS_THAN, 
    Workflow::CODE_A . Workflow::LESS_THAN, 
    Workflow::CODE_S . Workflow::LESS_THAN
  ];

  foreach ($workflows as $item) {
    preg_match('/^(\w+)\{(.+?)}$/', $item, $match);
    list($original, $label, $rules) = $match;

    $workflow = new Workflow($original, $label);
    $rules    = explode(',', $rules);

    foreach ($rules as $rule) {
      $character = $rule[0];
      $pattern   = (strlen($rule) >= 4) ? substr($rule, 0, 2) : false;

      if ($pattern && in_array($pattern, $check)) {
        $operation = ($rule[1] == '<') ? Workflow::LESS_THAN : Workflow::GREATER_THAN;

        list($amount, $result) = explode(':', str_replace($pattern, '', $rule));

        $workflow->setInstruction($character, $operation, $amount, $result);
      } else {
        // This is the final instruction
        $workflow->setInstruction('', '', 0, $rule);
      }
    }

    $data[$workflow->label] = $workflow;
  }

  return $data;
}

function getRatings($ratings) {
  $data = [];
  foreach ($ratings as $item) {
    $item  = str_replace(['{', '}'], ['', ''], $item);
    $parts = explode(',', $item);
    $rating = new Rating();

    foreach ($parts as $part) {
      $values = explode('=', $part);
      $rating->setRating($values[0], $values[1]);
      $rating->setTotal($values[1]);
    }

    $data[] = $rating;
  }

  return $data;
}

function process(&$rating, $workflows, $label = 'in', $use_range = false) {  
  $workflow = $workflows[$label];
  $label     = null;
  $found     = false;

  foreach ($workflow->instructions as $instruction) {
    $label = $instruction['next'];    
    
    if ($use_range) {
      list($found, $total) = operationRange($rating->range, $instruction);
      $rating->total += $total;
     } else {
      $found = operation($rating, $instruction);
     }

    if ($found) {
      $label = $found;
      break;
    }
  }
  
  if (in_array($label, [Workflow::ACCEPTED, Workflow::REJECTED])) {
    return ($label == Workflow::ACCEPTED) ? true : false;
  } else {
    return process($rating, $workflows, $label, $use_range);
  }
}

function operationRange($rating, $instruction) {
  $found = false;
  $total = 0;

  $amount     = $instruction['amount'];
  $next_step  = $instruction['next'];
  $operation  = $instruction['operation'];
  $code       = $instruction['code'];
  $min        = $rating[$code]['min'];
  $max        = $rating[$code]['max'];

  $range = [
    'options' => [
      'min_range' => $min,
      'max_range' => $max
    ]
  ];

  if ($code) {
    if ($operation == Workflow::LESS_THAN && filter_var($amount, FILTER_VALIDATE_INT, $range)) {
      $rating[$code]['min'] = $amount + 1;
      $rating[$code]['max'] = $max; 
      $found = $next_step;
    } elseif ($operation == Workflow::GREATER_THAN && filter_var($amount, FILTER_VALIDATE_INT, $range)) {
      $rating[$code]['min'] = $min;
      $rating[$code]['max'] = $amount - 1; 
      $found = $next_step;
    }

    for ($i = $rating[$code]['min']; $i <= $rating[$code]['max']; $i++) {
      $total += $i;
    }
  } 

  return [$found, $total];
}


function operation($rating, $instruction) {
  $found = false;

  if ($instruction['code']) {
    if ($instruction['operation'] == Workflow::LESS_THAN) {
      if ($rating->amount[$instruction['code']] < $instruction['amount'] ) {
        $found = $instruction['next'];
      }
    } else {
      if ($rating->amount[$instruction['code']] > $instruction['amount']) {
        $found = $instruction['next'];
      }
    }
  }

  return $found;
}

function accepted($ratings, $workflows, $use_range = false) {
  $sum = 0;
  
  foreach ($ratings as $rating) {
    if (process($rating, $workflows, 'in', $use_range)) {
      $sum += $rating->total;
    }    
  }

  return $sum;
}


// ---------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------
require_once('data.php');

$workflows = getWorkflows($workflows);
$ratings   = getRatings($ratings);


// Question 1
$sum = accepted($ratings, $workflows);

print '<p>The sum of all of the rating numbers for all of the parts that ultimately get accepted is ' . $sum . '</p>';

// Question 2
$min = 1;
$max = 4000;

foreach ($ratings as $rating) {
  $rating->total = 1;
  $rating->setRatingRange($min, $max);
}

$sum = accepted($ratings, $workflows, true);
print '<p>The distinct combinations of ratings accepted by the elves workflows is ' . $sum . '</p>';
exit;