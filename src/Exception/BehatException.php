<?php

/**
 * Contains Drupal\behat\Exception\BehatException;
 */
namespace Drupal\behat\Exception;

class BehatException extends \Exception {

  public function __construct($step) {
    $this->message = t('The step @step was not found.', array('@step' => $step));
  }
}