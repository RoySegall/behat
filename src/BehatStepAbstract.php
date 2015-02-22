<?php

/**
 * Contains Drupal\behat\BehatStepAbstract.
 */

namespace Drupal\behat;

/**
 * Base class for Behat steps. All step plugin need to extend this class.
 */
abstract class BehatStepAbstract {

  /**
   * The action each step will commit. i.e: I visit plugin will invoke
   * $this->drupalGet().
   *
   * @param BehatTestsAbstract $behat
   *   The current instance test which invoked the plugin. Will be use to invoke
   *   simple test method.
   * @param array $arguments
   *   The arguments from the step.
   *
   * @return mixed
   */
  public abstract function step(BehatTestsAbstract $behat, array $arguments = array());

}