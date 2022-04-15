<?php namespace RockDeploy;

use ProcessWire\Paths;
use ProcessWire\WireData;

// we make sure that the current working directory is the PW root
chdir(dirname(dirname(dirname(__DIR__))));
require_once "wire/core/ProcessWire.php";
class Deployment extends WireData {

  public $dry;
  public $paths;

  public function __construct() {
    $this->paths = new WireData();

    // path to the current release
    $this->paths->release = getcwd();

    // path to the root that contains all releases and current + shared folder
    $this->paths->root = dirname($this->paths->release);

    // path to shared folder
    $this->paths->shared = $this->paths->root."/shared";
  }

  /**
   * Cleanup old releases and keep given number
   *
   * This does also rename old release folders to make symlinks aware of the
   * change without rebooting the server or reloading php-fpm
   */
  public function deleteOldReleases($keep = 3, $rename = true) {
    $this->echo("Deleting old releases...");
    $folders = glob($this->paths->root."/release-*");
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
          $this->echo("Renaming $base  >  $base-", 2);
          $this->exec("mv $folder $folder-");
          $folder = "$folder-";
          $base = "$base-";
        }
        else $this->echo("Keeping $base", 2);
      }
    }
    $this->echo("Done");
  }

  public function dry($flag = true) {
    $this->dry = $flag;
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
    if($this->dry) $echoCmd = true;
    if($echoCmd) $this->echo($cmd);
    if($this->dry) return;
    exec($cmd, $out);
    $this->echo($out);
  }

  /**
   * Finish deployment
   * This removes the tmp- prefix from the deployment folder
   * and updates the "current" symlink
   * @return void
   */
  public function finish($keep = 3) {
    $oldPath = $this->paths->release;
    $newName = substr(basename($oldPath), 4);
    $this->echo("Finishing deployment - updating symlink...");
    $this->exec("mv $oldPath {$this->paths->root}/$newName");
    $this->exec("
      cd {$this->paths->root}
      ln -sfn $newName current
    ");
    $this->deleteOldReleases($keep);
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

  /**
   * Share files and folders
   *
   * Usage:
   * $deploy->share(
   *   '/site/assets/files/123' => 'push',
   *   '/site/assets/files' => 'link', // link is default
   *   '/site/config-local.php',
   * );
   * @return void
   */
  public function share(array $files) {
    $this->echo("Setting up shared files...");

    $release = $this->paths->release;
    $shared = $this->paths->shared;

    foreach($files as $k=>$v) {
      $file = $v;

      // push to shared folder or just create link?
      $type = 'link';
      if(is_string($k)) {
        $file = $k;
        $type = $v;
      }

      // prepare the src path
      $file = trim($file, "/");
      $from = Paths::normalizeSeparators("$release/$file");
      $toAbs = Paths::normalizeSeparators("$shared/$file");
      $isfile = !!pathinfo($from, PATHINFO_EXTENSION);
      $toDir = dirname($toAbs);

      // we create relative symlinks
      $level = substr_count($file, "/");
      $to = "shared/$file";
      for($i=0;$i<=$level;$i++) $to = "../$to";

      if($isfile) {
        $this->echo("Sharing file $from");
        $this->exec("ln -sfn $to $from");
      }
      else {
        $this->echo("Sharing directory $from");

        // push means we only push files to the shared folder
        // but we do not create a symlink. This can be used to push site
        // translations where the files folder itself is already symlinked
        if($type == 'push') {
          $this->exec("
            rm -rf $toAbs
            mkdir -p $toDir
            mv $from $toDir
          ");
        }
        else {
          $this->exec("ln -sfn $to $from");
        }
      }
    }
  }

}
