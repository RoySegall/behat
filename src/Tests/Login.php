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
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Login extends BehatTestsAbstract {

  public function testLogin() {
    $account = $this->drupalCreateUser();
    $this
      ->setPlaceholder('@user-name', $account->label())
      ->setPlaceholder('@user-pass', $account->passRaw)
      ->executeScenario('login', 'behat');
  }

}
