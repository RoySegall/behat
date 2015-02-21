<?php

namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * Redirects to a message deletion form.
 *
 * @Step(
 *  id = "/^I visit '(*)'$/"
 * )
 */
class Visit extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat) {
    $behat->visit('user');
  }

}