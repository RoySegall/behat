<?php

/**
 * @file
 * Behat module drush integration.
 */

use Drupal\behat\Behat;
use Drupal\behat\BehatDrushHelper;
use Drupal\behat\Exception;
use Symfony\Component\HttpFoundation;

/**
 * Implements hook_drush_command().
 */
function behat_drush_command() {
  $items = [];

  $items['behat-run-tests'] = [
    'description' => 'Run behat scenarios.',
    'examples' => [
      'drush bin/behat behat http://localhost/drupal8' => 'Run all the features files provided by the behat module.',
      'drush bin/behat behat http://localhost/drupal8 --features=login,comment' => 'Run selected features files provided by the behat module.',
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
function drush_behat_run_tests($provider, $url) {

  if (!$provider_info = Behat::getFeatureContexts($provider)) {
    throw new Exception\BehatException(dt('The provider @provider was not found', ['@provider' => $provider]));
  }

  if (!$url) {
    throw new Exception\BehatException(dt('You must provide the url to the current Drupal installation.'));
  }

  $class = $provider_info['class'];

  $features = $providers = [];

  // Store the feature context and the defining providers.
  // todo: Consider themes and features files location defined by provider.
  $providers[$class] = DRUPAL_ROOT . '/' . drupal_get_path('module', $provider) . '/src/Features/';

  $components = BehatDrushHelper::FeaturesForProvider($provider);

  foreach ($components as $component) {
    $explode = explode('/', $component);
    $features[$class][] = end($explode);
  }

  // todo: populate the values.
  BehatDrushHelper::SetEnvInformation('FEATURES_PROVIDERS', serialize($providers));
  BehatDrushHelper::SetEnvInformation('FEATURES_RUN', serialize($features));
  BehatDrushHelper::SetEnvInformation('SIMPLETEST_BASE_URL', $url);
  $tests_list['phpunit'][] = $class;

  // Run tests.
  $test_id = Behat::runTests($tests_list, 'drupal');

  BehatDrushHelper::DisplaySearchResults($test_id);
}
