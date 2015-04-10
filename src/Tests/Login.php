<?php

/**
 * @file
 * Contains Drupal\behat\Tests\Login.
 */
namespace Drupal\behat\Tests;

use Drupal\behat\BehatTestsAbstract;

/**
 * Simple login test.
 *
 * @group behat
 */
class Login extends BehatTestsAbstract {

  public function setUp() {
    parent::setUp('behat');
  }

  public function testLogin() {
    $this->executeScenario('login.feature', 'behat');
  }

}
