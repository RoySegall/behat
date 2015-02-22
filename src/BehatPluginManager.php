<?php

/**
 * @file
 * Contains \Drupal\behat\Plugin\BehatPluginManager.
 */
namespace Drupal\behat;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manage behat steps.
 */
class BehatPluginManager extends DefaultPluginManager {

  /**
   * Constructs a StepDefinition object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Step', $namespaces, $module_handler, NULL, 'Drupal\behat\Annotation\Step');
    $this->alterInfo('behat_step_alter');
    $this->setCacheBackend($cache_backend, 'behat_steps');
  }

}