<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * @Step(
 *  id = "I press '(.*?)'"
 * )
 */
class Press extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $element) {
    $button = $behat->assertSession()->buttonExists($element);

    if ($button->getAttribute('type') == 'submit') {
      // This is a submit element. Call the submit form method.
      $behat->sendForm($element);
    }
    else {
      // Normal button. Press it.
      $button->press();
    }
  }

}
