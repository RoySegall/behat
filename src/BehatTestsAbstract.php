<?php

/**
 * Contains Drupal\behat\BehatTestsAbstract.
 */
namespace Drupal\behat;

use Behat\Gherkin\Node\ScenarioInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Drupal\behat\Exception\BehatFailedStep;
use Drupal\behat\Exception\BehatStepException;
use Drupal\simpletest\BrowserTestBase;

/**
 * Simple login test.
 *
 * @group behat
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BehatTestsAbstract extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['behat'];

  /**
   * @var array
   *
   * The edit elements for forms.
   */
  protected $edit = [];

  /**
   * @var array
   *
   * Holds placeholders for the scenarios.
   */
  protected $placeholders = [];

  /**
   * Before each scenario logout the user.
   *
   * @param $scenarioInterface
   *   The scenario object.
   */
  public function beforeScenario(ScenarioInterface $scenarioInterface = NULL) {
    $this->drupalGet('user/logout');
  }

  /**
   * Get the test ID.
   *
   * @return integer
   *   The test ID.
   */
  protected function getTestID() {
    return getenv('TESTID');
  }

  /**
   * Get the features we need to run for each provider.
   *
   * @param $name
   *   The name of the class namespace. i.e:
   *   Drupal\behat\Plugin\FeatureContext\FeatureContextBase
   *
   * @return array
   *   An array of features files we need to run during the tests.
   */
  protected function getFeaturesSettings($name = NULL) {
    $features = unserialize(getenv('FEATURES_RUN'));

    if ($name && !empty($features[$name])) {
      return $features[$name];
    }

    return $features;
  }

  /**
   * Get the path for the FeatureContext plugin path.
   *
   * @param $name
   *   The name of the class namespace. i.e:
   *   Drupal\behat\Plugin\FeatureContext\FeatureContextBase
   *
   * @return Array|String
   *   Array or a single path of FeatureContext plugin and their features files
   *   path.
   */
  protected function getProvidersPath($name = NULL) {
    $providers = unserialize(getenv('FEATURES_PROVIDERS'));

    if ($name && !empty($providers[$name])) {
      return $providers[$name];
    }

    return $providers;
  }

  /**
   * After each scenario invoke actions.
   *
   * @param $scenarioInterface
   *   The scenario object.
   */
  public function afterScenario(ScenarioInterface $scenarioInterface = NULL) {}

  /**
   * Execute a feature file.
   *
   * @param $path
   *   The path for the feature file.
   *
   * @throws \Exception
   */
  public function executeFeature($path) {
    if (!file_exists($path)) {
      throw new \Exception('The scenario is missing from the path ' . $path);
    }

    $test = file_get_contents($path);

    // Get the parser of the gherkin files.
    $parser = Behat::getParser();
    $scenarios = $parser->parse($test)->getScenarios();

    foreach ($scenarios as $scenario) {
      $this->beforeScenario($scenario);

      foreach ($scenario->getSteps() as $step) {
        try {
          $this->executeStep(format_string($step->getText(), $this->placeholders));
        }
        catch (\Exception $e) {
          throw new \Exception($e->getMessage());
        }

      }

      $this->afterScenario($scenario);
    }
  }

  /**
   * This method will run all the tests in the current request.
   */
  public function testRunTests() {
    $reflection = new \ReflectionClass($this);
    $name = $reflection->getName();

    // Get the base path of the features files.
    $base_path = $this->getProvidersPath($name);

    foreach ($this->getFeaturesSettings($name) as $feature) {
      $this->executeFeature($base_path . $feature);
    }
  }

  /**
   * Find in the current instance a method which match the step definition.
   *
   * @param $step_definition
   *   The step definition.
   *
   * @throws BehatStepException
   */
  protected function executeStep($step_definition) {
    $reflection = new \ReflectionObject($this);
    foreach ($reflection->getMethods() as $method) {

      if (!$step = Behat::getBehatStepDefinition($method->getDocComment())) {
        continue;
      }

      if ($results = Behat::stepDefinitionMatch($step, $step_definition)) {
        // Reflect the instance.
        $object_reflection = new \ReflectionClass($this);
        $reflection = new \ReflectionClass($object_reflection->getName());

        // Invoke the method.
        $reflection->getMethod($method->getName())->invokeArgs($this, $results['arguments']);
      }
    }
  }

}
