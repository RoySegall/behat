<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * @Step(
 *  id = "I should see '(.*?)'"
 * )
 */
class ShouldSee extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $text) {
    $behat->assertSession()->pageTextContains($text);
  }

}
