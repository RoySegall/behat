<?php

/**
 * Contains Drupal\behat\BehatStepAbstract.
 */

namespace Drupal\behat;
use Drupal\behat\Exception\BehatException;

/**
 * Base class for Behat steps. All step plugin need to extend this class. Each
 * class will need to implement a step method:
 *
 * @code
 * public abstract function step(BehatTestsAbstract $behat);
 * public abstract function step(BehatTestsAbstract $behat, $url);
 * public abstract function step(BehatTestsAbstract $behat, $element, $value);
 * @endcode
 *
 * Each method will get by default the $behat object. The extra arguments will
 * be the placeholder from the step definition.
 */
abstract class BehatStepAbstract {

  public function __construct() {
    if (!method_exists($this, 'step')) {
      // The class don't have the step method.
      $object_reflection = new \ReflectionClass($this);
      throw new BehatException(t("The method @class don't have step method.", array('@class' => $object_reflection->getName())));
    }
  }

}