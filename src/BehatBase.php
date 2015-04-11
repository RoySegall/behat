<?php
/**
 * Contains Drupal\behat\BehatBase.
 */
namespace Drupal\behat;

use Drupal\behat\Exception\BehatFailedStep;

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
   * @param $placeholders
   *   Optional. Placeholder for elements from the step definition.
   *
   * @return $this
   *   The current object.
   * .
   * @throws BehatFailedStep
   */
  public function executeStep($step, $placeholders = []) {
    try {
      Behat::Step($this->Behat, format_string($step, $placeholders));
    }
    catch (\Exception $e) {
      throw new BehatFailedStep($e->getMessage());
    }

    return $this;
  }
}