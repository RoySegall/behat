<?php

/**
 * Contains Drupal\behat\Exception\BehatStepException;
 */
namespace Drupal\behat\Exception;

class BehatStepException extends \Exception {

  public function __construct($step) {
    $this->message = t('The step @step was not found.', array('@step' => $step));
  }
}