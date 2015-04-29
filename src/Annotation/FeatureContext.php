<?php

/**
 * @file
 * Contains Drupal\behat\Annotation\Step.
 */
namespace Drupal\behat\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Define a feature context plugin.
 *
 * @Annotation
 */
class FeatureContext extends Plugin {

  /**
   * @var String
   *
   * The directory.
   */
  public $directory;

}