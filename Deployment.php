<?php namespace RockDeploy;

use ProcessWire\WireData;

// we make sure that the current working directory is the PW root
chdir(dirname(dirname(dirname(__DIR__))));
require_once "wire/core/ProcessWire.php";
class Deployment extends WireData {

  public $dry;
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
  public function deleteOldReleases($keep = 3, $rename = true) {
    $this->echo("Deleting old releases...");
    $folders = glob($this->paths->deploydir."/release-*");
    rsort($folders);
    $cnt = 0;
    foreach($folders as $folder) {
      $cnt++;
      $base = basename($folder);
      if($cnt>$keep) {
        $this->echo("Deleting $base", 2);
        $this->exec("rm -rf $folder");
        continue;
      }
      if($rename) {
        if($cnt>1) {
          $this->echo("rename $base -> $base-", 2);
          $this->exec("mv $folder $folder-");
          $folder = "$folder-";
          $base = "$base-";
        }
        else $this->echo("keeping $base", 2);
      }
    }
    $this->echo("Done");
  }

  /**
   * Echo message to stout
   */
  public function echo($msg = '', $indent = 0) {
    if(is_int($indent)) $indent = str_pad('', $indent);
    if(is_string($msg)) {
      echo "{$indent}$msg\n";
    }
    elseif(is_array($msg)) {
      if(count($msg)) echo print_r($msg, true)."\n";
    }
  }

  /**
   * Execute command and echo output
   */
  public function exec($cmd, $echoCmd = false) {
    if($echoCmd) $this->echo($cmd);
    if($this->dry) return;
    exec($cmd, $out);
    $this->echo($out);
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
