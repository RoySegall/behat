<?php

/**
 * Contains Drupal\behat\Plugin\FeatureContext\FeatureContextBase.
 */
namespace Drupal\behat\Plugin\FeatureContext;
use Drupal\behat\BehatTestsAbstract;
use Drupal\behat\FeaturesTraits\BasicTrait;

/**
 * @FeatureContext(
 *   id = "behat",
 *   label = @Translation("Behat"),
 * )
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FeatureContextBase extends BehatTestsAbstract {

  use BasicTrait;

}
