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

  /**
   * Cleanup old releases and keep given number
   *
   * This does also rename old release folders to make symlinks aware of the
   * change without rebooting the server or reloading php-fpm
   */
  public function cleanupOldReleases($keep = 3, $rename = true) {
    $folders = glob($this->paths->deploydir."/release-*");
    rsort($folders);
    $cnt = 0;
    foreach($folders as $folder) {
      $cnt++;
      $newname = $rename ? "$folder-" : $folder;
      if($cnt>1) $this->exec("mv $folder $newname", 2, false);
      if($cnt>$keep) {
        $this->echo("Deleting $newname", 2);
        // $this->exec("rm -rf $newname", 2, false);
      }
    }
  }

  /**
   * Echo message to stout
   */
  public function echo($msg = '', $indent = 0) {
    if(is_int($indent)) $indent = str_pad('', $indent);
    if(is_string($msg)) {
      echo "{$indent}$msg\n";
    }
    elseif(is_array($msg)) echo print_r($msg);
  }

  /**
   * Execute command and echo output
   */
  public function exec($cmd, $indent = 0, $echoCmd = true) {
    if($echoCmd) $this->echo($cmd, $indent);
    if($this->dry) return;
    exec($cmd, $out);
    $this->echo($out, $indent);
  }

  public function hello() {
    $this->echo("Hi there, I'm RockDeploy...");
  }

  /**
   * Print paths
   */
  public function paths() {
    $this->echo($this->paths->getArray());
  }

}
