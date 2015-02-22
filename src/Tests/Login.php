<?php

/**
 * @file
 * Contains Drupal\behat\Tests\Login.
 */
namespace Drupal\behat\Tests;

use Drupal\behat\BehatBase;
use Drupal\behat\BehatTestsAbstract;

/**
 * Simple login test.
 *
 * @group behat
 */
class Login extends BehatTestsAbstract {

  public function testLogin() {

    $Step = new BehatBase($this);
    $Step->Step("I visit 'user'");
  }
}
