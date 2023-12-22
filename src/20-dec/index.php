<?php
class Module {
  const FLIP_PULSE   = '%';
  const CONJUNCTION = '&';
  
  const PULSE_HIGH = 1;
  const PULSE_LOW  = 2;

  public $id;
  public $type;
  public $destination_modules = [];

  public function __construct($id = '', $type = '', $destination_modules = []) {
    $this->id                  = $id;
    $this->type                = $type;
    $this->destination_modules = $destination_modules;
  }

}

trait Rules {
  public function flipPulse($pulse = Module::PULSE_LOW) {
    if ($pulse == Module::PULSE_LOW) {
      return Module::PULSE_HIGH;
    }

    return $pulse;
  }

  public function sendPulse($pulse, $modules) {
    foreach ($this->destination_modules as $module) {
      $module_pulse = $this->flipPulse($pulse);
      $modules[$module]->sendPulse($pulse);
    }
  }
}



// ----------------------------------------------------------------
// Main
// ----------------------------------------------------------------
require_once('data.php');

$modules = [];

foreach ($data as $key => $item) {
  if ($key == 'broadcaster') {
    $id   = 'broadcaster';
    $type = '';
  } else {
    $id   = str_replace([Module::FLIP_PULSE, Module::CONJUNCTION], ['', ''], $key);
    $type = strpos($key, Module::FLIP_PULSE) ? Module::FLIP_PULSE : Module::CONJUNCTION;
  }

  $modules[$id] = new Module($id, $type, $item);
}

$start = $modules['broadcaster'];
$start->sendPulse(Module::PULSE_LOW, $modules);

print '<pre>';
print_r($modules);
exit;