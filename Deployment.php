<?php namespace RockDeploy;

use ProcessWire\WireData;

// we make sure that the current working directory is the PW root
chdir(dirname(dirname(dirname(__DIR__))));
require_once "wire/core/ProcessWire.php";
class Deployment extends WireData {

  public $paths;

  public function __construct() {
    $this->paths = new WireData();
    $this->paths->pwroot = getcwd();
    $this->paths->deploydir = dirname($this->paths->pwroot);
  }

  public function hello() {
    $this->write("Hi there, I'm RockDeploy...");
  }

  /**
   * Print paths
   */
  public function paths() {
    $this->write($this->paths->getArray());
  }

  public function write($msg) {
    if(is_array($msg)) echo print_r($msg, true);
    else echo "$msg\n";
  }

}
