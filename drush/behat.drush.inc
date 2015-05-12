<?php

/**
 * @file
 * Behat module drush integration.
 */

use Drupal\behat\Behat;
use Drupal\behat\BehatDrushHelper;
use Drupal\behat\Exception;

/**
 * Implements hook_drush_command().
 */
function behat_drush_command() {
  $items = [];

  $items['behat-run-tests'] = [
    'description' => 'Run behat scenarios.',
    'examples' => [
      'drush bin/behat behat' => 'Run all the features files provided by the behat module.',
      'drush bin/behat behat --features=login,comment' => 'Run selected features files provided by the behat module.',
    ],
    'arguments' => [
      'provider' => 'The provider ID as defined in the plugin annotation.',
    ],
    'options' => [
      'features' => 'List of features in the provider.',
    ],
    'aliases' => ['bin/behat'],
  ];

  return $items;
}

/**
 * Running the tests using drush.
 *
 * @param $provider
 *   The name of the provider as defined in the FeaturesContext plugin
 *   definition.
 *
 * @throws \Drupal\behat\Exception\BehatException
 */
function drush_behat_run_tests($provider) {

  if (!$provider_info = Behat::getFeatureContexts($provider)) {
    throw new Exception\BehatException(dt('The provider @provider was not found', ['@provider' => $provider]));
  }

  $components = BehatDrushHelper::FeaturesForProvider($provider);

  // todo: populate the values.
  BehatDrushHelper::SetEnvInformation('FEATURES_PROVIDERS', '');
  BehatDrushHelper::SetEnvInformation('FEATURES_RUN', '');

  // Run tests.
//  Behat::runTests();
}
