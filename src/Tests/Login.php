<?php

/**
 * @file
 * Contains Drupal\behat\Tests\Login.
 */
namespace Drupal\behat\Tests;

use Drupal\behat\BehatBase;
use Drupal\behat\BehatTestsAbstract;
use Drupal\user\Entity\User;

/**
 * Simple login test.
 *
 * @group behat
 */
class Login extends BehatTestsAbstract {

  public function testLogin() {
    $account = $this->drupalCreateUser();

    $Step = new BehatBase($this);
    $Step->Step("I fill in 'name' with '@name'", ['@name' => $account->getUsername()]);
    $Step->Step("I fill in 'pass' with '@pass'", ['@pass' => $account->pass_raw]);
    $Step->Step("I press 'Log in' in 'user'");
    $Step->Step("I should see '@name'", ['@name' => $account->getUsername()]);
  }

}
