<?php

namespace Drupal\behat;

class BehatBase {

  /**
   * @var BehatTestsAbstract
   */
  protected $Behat;

  public function __construct(BehatTestsAbstract $Behat) {
    $this->Behat = $Behat;
  }

  public function Step($step) {
    Behat::Step($this->Behat, $step);
    return $this;
  }

  public function execute() {
    return 'done!';
  }

}