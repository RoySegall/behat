<?php

namespace Drupal\behat;

use Drupal\behat\Exception\BehatException;

class Behat {

  /**
   * Get all the notifiers plugins or a specific one.
   *
   * @param $step_definition
   *  The id of the plugin.
   *
   * @throws BehatException
   * @return null|array
   */
  public static function Step(BehatTestsAbstract $behat, $step_definition) {
    $steps = \Drupal::service('plugin.manager.behat.step')->getDefinitions();

    foreach ($steps as $step) {
      if (self::stepDefinitionMatch($step['id'], $step_definition)) {
        \Drupal::service('plugin.manager.behat.step')->createInstance($step_definition)->step($behat);
        return TRUE;
      }
    }

    throw new BehatException($step_definition);
  }

  static public function stepDefinitionMatch($step, $step_definition) {
    preg_match($step, $step_definition);
    return $step == $step_definition;
  }
}