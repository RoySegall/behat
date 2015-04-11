<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * @Step(
 *  id = "I visit '(.*?)'"
 * )
 */
class Visit extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $url) {
    $behat->visit($url);
  }

}
