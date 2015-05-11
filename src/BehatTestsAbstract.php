<?php

/**
 * Contains Drupal\behat\BehatTestsAbstract.
 */
namespace Drupal\behat;

use Behat\Gherkin\Node\ScenarioInterface;
use Drupal\behat\Exception\BehatStepException;
use Drupal\simpletest\BrowserTestBase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

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
   * @var \Symfony\Component\Filesystem\Filesystem
   *
   * Symfony file system object.
   */
  protected $fileSystem;

  /**
   * @var String
   *
   * The yml file path.
   */
  protected $ymlPath;

  /**
   * Get the yml file content.
   *
   * @return mixed
   *   The yml content.
   */
  public function getYmlFileContent() {
    $parser = new Parser();
    return $parser->parse(file_get_contents($this->ymlPath));
  }

  /**
   * Write the content to the file path.
   *
   * @param $content
   */
  public function writeYmlFile($content) {
    $dumper = new Dumper();
    file_put_contents($this->ymlPath, $dumper->dump($content));
  }

  /**
   * Before each scenario logout the user.
   *
   * @param $scenarioInterface
   *   The scenario object.
   */
  public function beforeScenario(ScenarioInterface $scenarioInterface = NULL) {
    // todo: re-think if this is needed.
    // $this->prepareEnvironment();
    // $this->installDrupal();
    // $this->initMink();

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
  public function afterScenario(ScenarioInterface $scenarioInterface = NULL) {
    // todo: re-think if this needed.
    // $this->tearDown();
  }

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

          // Log the step the file.
          $content = $this->getYmlFileContent();
          $content[$scenario->getTitle()][] = [
            'step' => $step->getText(),
            'status' => 'pass',
          ];
          $this->writeYmlFile($content);
        }
        catch (\Exception $e) {
          // Log the step the file.
          $content = $this->getYmlFileContent();
          $content[$scenario->getTitle()][] = [
            'step' => $step->getText() . "<br />" . $e->getMessage(),
            'status' => 'fail',
          ];
          $this->writeYmlFile($content);
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
    $this->fileSystem = new FileSystem();

    $testid = $this->getTestID();

    // Create the folder of the behat in case it doesn't exists. When displaying
    // the results we will remove the file for the test.
    $behat_path = drupal_get_path('module', 'behat') . '/results';
    $this->ymlPath = $behat_path . '/behat-' . $testid . '.yml';

    if (!$this->fileSystem->exists($behat_path)) {
      $this->fileSystem->mkdir($behat_path);
    }

    if (!$this->fileSystem->exists($this->ymlPath)) {
      $this->fileSystem->touch($this->ymlPath);
    }

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
