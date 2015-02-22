<?php

namespace Drupal\behat;

use Drupal\behat\Exception\BehatStepException;

class Behat {

  /**
   * Invoking a step.
   *
   * @param BehatTestsAbstract $behat
   *   A behat test instance.
   * @param $step_definition
   *   A step definition.
   *
   * @throws BehatStepException
   * @return null|array
   */
  public static function Step(BehatTestsAbstract $behat, $step_definition) {
    $steps = \Drupal::service('plugin.manager.behat.step')->getDefinitions();

    foreach ($steps as $step) {
      if ($results = self::stepDefinitionMatch($step['id'], $step_definition)) {
        // Get the step instance.
        $object = \Drupal::service('plugin.manager.behat.step')->createInstance($results['step']);

        // Reflect the instance.
        $object_reflection = new \ReflectionClass($object);
        $reflection = new \ReflectionClass($object_reflection->getName());

        // Invoke the
        $reflection->getMethod('step')->invokeArgs($object, array($behat) + $results['arguments']);
        return TRUE;
      }
    }

    throw new BehatStepException($step_definition);
  }

  /**
   * Verify if the step definition match any plugin step.
   *
   * @param $step
   *   The current step.
   * @param $step_definition
   *   The step definition.
   * @return array|bool
   */
  static public function stepDefinitionMatch($step, $step_definition) {
    if (!preg_match('/' . $step . '/', $step_definition, $matches)) {
      return FALSE;
    }

    // Remove the step and keep the arguments.
    unset($matches[0]);

    return array(
      'arguments' => $matches,
      'step' => $step,
    );
  }
}