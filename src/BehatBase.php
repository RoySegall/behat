<?php
/**
 * Contains Drupal\behat\BehatBase.
 */
namespace Drupal\behat;

class BehatBase {

  /**
   * @var BehatTestsAbstract
   */
  protected $Behat;

  /**
   * @param BehatTestsAbstract $Behat
   *   Instance of a behat simple test.
   */
  public function __construct(BehatTestsAbstract $Behat) {
    $this->Behat = $Behat;
  }

  /**
   * Invoke a step.
   *
   * @param $step
   *   The step you need to invoke i.e: "I visit 'user'"
   * @return $this
   *   The current object.
   * @throws Exception\BehatException
   */
  public function Step($step) {
    Behat::Step($this->Behat, $step);
    return $this;
  }
}