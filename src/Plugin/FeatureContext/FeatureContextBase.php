<?php

/**
 * Contains Drupal\behat\Plugin\FeatureContext\FeatureContextBase.
 */
namespace Drupal\behat\Plugin\FeatureContext;

use Behat\Gherkin\Node\ScenarioInterface;
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

  /**
   * {@inheritdoc}
   */
  public function beforeScenario(ScenarioInterface $scenarioInterface = NULL) {
    parent::beforeScenario($scenarioInterface);

    $account = $this->drupalCreateUser();
    $this->placeholders = [
      '@user-name' => $account->label(),
      '@user-pass' => $account->passRaw,
    ];
  }
}
