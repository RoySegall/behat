<?php

/**
 * Contains Drupal\behat\BehatTestsAbstract.
 */
namespace Drupal\behat;

use Drupal\simpletest\WebTestBase;

class BehatTestsAbstract extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('behat');

  public function visit($path) {
    $this->drupalGet($path);
  }

}