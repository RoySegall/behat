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

    $permissions = [];
    if ($tags = $scenarioInterface->getTags()) {
      // Keep the permissions for tests with entity.
      $tests_permissions = [
        'comment' => ['post comments'],
        'node' => ['create node'],
        'taxonomy-term' => 'create terms',
      ];

      $entity_feature = $tags[0];
      $permissions = $tests_permissions[$entity_feature];
    }

    $account = $this->drupalCreateUser($permissions);
    $this->placeholders = [
      '@user-name' => $account->label(),
      '@user-pass' => $account->passRaw,
    ];
  }
}
