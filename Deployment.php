<?php namespace RockDeploy;
class Deployment {

  public function hello() {
    $this->write("Hi there, I'm RockDeploy...");
  }

  public function write($msg) {
    echo "$msg\n";
  }

}
