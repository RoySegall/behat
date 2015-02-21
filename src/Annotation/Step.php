<?php
/**
 * @file
 * Contains Drupal\ckeditor\Annotation\CKEditorPlugin.
 */
namespace Drupal\behat\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Define a message notify annotation object.
 *
 * @Annotation
 */
class Step extends Plugin {

  /**
   * @var String
   *
   * The identifier of the plugin.
   */
  public $step;

}