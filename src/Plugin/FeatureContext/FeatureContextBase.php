<?php

/**
 * Contains Drupal\behat\Plugin\FeatureContext\FeatureContextBase.
 */
namespace Drupal\behat\Plugin\FeatureContext;
use Drupal\behat\BehatTestsAbstract;
use Drupal\Behat\FeaturesTraits\BasicTrait;

/**
 * @FeatureContext(
 *   id = "behat",
 *   label = @Translation("Behat"),
 * )
 */
class FeatureContextBase extends BehatTestsAbstract {

  use BasicTrait;

}
