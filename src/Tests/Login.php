<?php

/**
 * @file
 * Contains \Drupal\node\Tests\Views\BulkFormAccessTest.
 */

namespace Drupal\behat\Tests;

use Drupal\behat\BehatBase;
use Drupal\behat\BehatTestsAbstract;

/**
 * Tests if entity access is respected on a node bulk operations form.
 *
 * @group behat
 */
class Login extends BehatTestsAbstract {

  public function testLogin() {

    $Step = new BehatBase($this);
    $Step
      ->Step('I visit')
      ->Step('I visit')
      ->Step('I visit')
      ->Step('I visit');
  }
}
