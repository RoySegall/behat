<?php

/**
 * Contains Drupal\behat\Plugin\FeatureContext\FeatureContextBase.
 */
namespace Drupal\behat\Plugin\FeatureContext;

use Behat\Gherkin\Node\ScenarioInterface;
use Drupal\behat\BehatTestsAbstract;
use Drupal\behat\FeaturesTraits\BasicTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

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

  /**
   * @var User
   *
   * The user object.
   */
  protected $account;

  /**
   * @var Node
   */
  protected $node;

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

    $this->account = $this->drupalCreateUser($permissions);
    $this->placeholders = [
      '@user-name' => $this->account->label(),
      '@user-pass' => $this->account->passRaw,
    ];
  }

  /**
   * Creates a node based on default settings.
   *
   * @param array $settings
   *   (optional) An associative array of settings for the node, as used in
   *   entity_create(). Override the defaults by specifying the key and value
   *   in the array, for example:
   *   @code
   *     $this->drupalCreateNode(array(
   *       'title' => t('Hello, world!'),
   *       'type' => 'article',
   *     ));
   *   @endcode
   *   The following defaults are provided:
   *   - body: Random string using the default filter format:
   *     @code
   *       $settings['body'][0] = array(
   *         'value' => $this->randomMachineName(32),
   *         'format' => filter_default_format(),
   *       );
   *     @endcode
   *   - title: Random string.
   *   - type: 'page'.
   *   - uid: The currently logged in user, or anonymous.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node entity.
   */
  protected function drupalCreateNode(array $settings = array()) {
    // Populate defaults array.
    $settings += array(
      'body'      => array(array(
        'value' => $this->randomMachineName(32),
        'format' => filter_default_format(),
      )),
      'title'     => $this->randomMachineName(8),
      'type'      => 'page',
      'uid'       => \Drupal::currentUser()->id(),
    );
    $node = entity_create('node', $settings);
    $node->save();

    return $node;
  }

  /**
   * @Given /^I fill in "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInWith($name, $value) {
    $this->assertSession()->fieldExists($name);
    $this->edit[$name] = $value;
  }

  /**
   * @Given /^I press "([^"]*)"$/
   */
  public function iPress($element) {
    $button = $this->assertSession()->buttonExists($element);

    if ($button->getAttribute('type') == 'submit') {
      // This is a submit element. Call the submit form method.
      $this->submitForm($this->edit, $element);
    }
    else {
      // Normal button. Press it.
      $button->press();
    }
  }

  /**
   * @Given /^I should see "([^"]*)"$/
   */
  public function iShouldSee($text) {
    $this->assertSession()->pageTextContains($text);
  }

  /**
   * @Given /^I visit "([^"]*)"$/
   */
  public function iVisit($url) {
    $this->drupalGet($url);
  }

  /**
   * @Given /^I login as user "([^"]*)"$/
   */
  public function iLogInAsUser($name) {
    // todo: handle multiple users in the test.
    $this->drupalLogin($this->account);
  }

  /**
   * @Given /^I create node$/
   */
  public function iCreateNode() {
    $this->node = $this->drupalCreateNode();
    $this->ivisit($this->node->url());
  }
}
