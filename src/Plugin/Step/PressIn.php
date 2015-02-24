<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * @Step(
 *  id = "I press '(.*?)' in '(.*?)'"
 * )
 */
class PressIn extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $element, $url) {
    // Check if this is submit button.
    $behat->sendForm($element, $url);
  }

}