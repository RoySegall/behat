<?php

/**
 * @file
 * Contains Drupal\behat\Annotation\Step.
 */
namespace Drupal\behat\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Define a step definition annotation.
 *
 * @Annotation
 */
class Step extends Plugin {

  /**
   * @var String
   *
   * The step.
   */
  public $step;

}