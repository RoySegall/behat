<?php

/**
 * @file
 *
 * Contains \Drupal\behat\DrushHelper.
 */
namespace Drupal\behat;

use \Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

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
    $file = new FileSystem();

    $yml_path = drupal_get_path('module', 'behat') . '/results/behat-' . $test_id . '.yml';

    $parser = new Parser();
    $logs = $parser->parse(file_get_contents($yml_path));

    foreach ($logs as $feature => $steps) {
      drush_log($feature, 'success');
      foreach ($steps as $step) {
        if ($step['status'] == 'pass') {
          drush_log($step['step'], 'success');
        }
        else {
          drush_log(format_string('The tests has failed due to: !error',['!error' => $step['step']]), 'error');
          exit(1);
        }
      }
    }
  }
}
