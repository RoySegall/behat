<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;
use SebastianBergmann\Exporter\Exception;

/**
 * @Step(
 *  id = "I fill in '(.*?)' with '(.*?)'"
 * )
 */
class Fill extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $name, $value) {
    $behat->assertSession()->fieldExists($name);
    $behat->setEdit($name, $value);
  }

}
