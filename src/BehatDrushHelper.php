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
   * Display a cool log with colors and indentation.
   *
   * @param $text
   *   The log text.
   * @param $color
   *   The color of the log: blue, green, red, yellow or white. Default to
   *   white.
   * @param int $indent
   *   Number of indentation for the text.
   */
  public static function coolLog($text, $color = 'white', $indent = 0) {
    $colors = [
      'blue' => 94,
      'green' => 92,
      'red' => 91,
      'yellow' => 93,
      'white' => 37,
    ];

    $string = '';
    $string .= str_repeat("  ", $indent);
    $string .= "\033[{$colors[$color]}m{$text}\033[0m\n";

    echo $string;
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
      BehatDrushHelper::coolLog($feature, 'blue');

      foreach ($steps as $step) {
        if ($step['status'] == 'pass') {
          BehatDrushHelper::coolLog($step['step'], 'green', 1);
        }
        else {
          drush_log(format_string('The tests has failed due to: !error',['!error' => $step['step']]), 'error');
          exit(1);
        }
      }
      echo "\n";
    }

    $file->remove($yml_path);
  }
}
