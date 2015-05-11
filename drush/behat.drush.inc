<?php

/**
 * @file
 * Behat module drush integration.
 */

/**
 * Implements hook_drush_command().
 */
function behat_drush_command() {
  $items = array();

  $items['behat-run-tests'] = array(
    'description' => 'Run behat scenarios.',
    'examples' => array(
      "drush bin/behat behat" => 'Run all the features files provided by the behat module.',
      "drush bin/behat behat --features=login,comment" => 'Run selected features files provided by the behat module.',
    ),
    'arguments' => array(
      'provider' => 'The provider ID as defined in the plugin annotation.',
    ),
    'aliases' => array('bin/behat'),
  );

  return $items;
}

/**
 * Running the tests using drush.
 */
function drush_behat_run_tests() {
  // todo: do!
}
