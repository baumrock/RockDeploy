<?php namespace ProcessWire;
class RockDeploy extends WireData implements Module {

  public static function getModuleInfo() {
    return [
      'title' => 'RockDeploy',
      'version' => '1.0.0',
      'autoload' => 'template=admin',
      'singular' => true,
      'icon' => 'code',
    ];
  }

  public function init() {
    $this->wire->addHookAfter('AdminThemeUikit::renderFile', function($event) {
      $file = $event->arguments(0); // full path/file being rendered
      $vars = $event->arguments(1); // assoc array of vars sent to file
      if(basename($file) === '_footer.php') {
        $event->return = str_replace(
          "ProcessWire",
          $this->deployInfo(),
          $event->return
        );
      }
    });
  }

  public function deployInfo() {
    $out = $this->wire->config->httpHost;
    $time = date("Y-m-d H:i:s", filemtime($this->wire->config->paths->root));
    if($this->wire->user->isSuperuser()) {
      $dir = $this->wire->config->paths->root;
      $out = "<span title='$dir @ $time' uk-tooltip>$out</span>";
    }
    return $out;
  }

}
