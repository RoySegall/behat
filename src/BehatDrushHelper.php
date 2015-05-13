<?php

/**
 * @file
 *
 * Contains \Drupal\behat\DrushHelper.
 */
namespace Drupal\behat;

/**
 * Helper methods for the drush integration.
 */
class BehatDrushHelper {

  /**
   * Return the list of the features for given test from drush command.
   *
   * @param $provider
   *   The name of the provider.
   *
   * @return array
   *   List of features files with path from the provider.
   */
  public static function FeaturesForProvider($provider) {
    if (!$features = drush_get_option('features')) {
      // todo: Check if this is a module type.
      return Behat::getComponentFeatures($provider);
    }

    $components = explode(',', $features);
    $path = drupal_get_path('module', $provider) . '/src/Features/';

    foreach ($components as &$component) {
      $temp_conf = $path . $component . '.feature';

      if (!file_exists($temp_conf)) {
        $params = [
          '@feature' => $component,
          '@provider' => $provider,
        ];
        drush_log(dt('@feature was not found in @provider features folders.', $params), 'warning');
        continue;
      }

      $component = $temp_conf;
    }

    return $components;
  }

  /**
   * Set environment variables for the tests.
   *
   * @param $name
   *   The variable name.
   * @param $value
   *   The value.
   */
  public static function SetEnvInformation($name, $value) {
    putenv($name . '=' . $value);
  }

  /**
   * Display the search results.
   *
   * @param $test_id
   *   The test ID.
   */
  public static function DisplaySearchResults($test_id) {

    throw new Exception\BehatException('Roy is to Awesome! the test could not handle it.');
  }

}
