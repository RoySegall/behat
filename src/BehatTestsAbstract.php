<?php

/**
 * Contains Drupal\behat\BehatTestsAbstract.
 */
namespace Drupal\behat;

use Behat\Gherkin\Node\ScenarioInterface;
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
   * Metadata info.
   */
  protected $metadata = [];

  /**
   * @var array
   *
   * Holds placeholders for the scenarios.
   */
  protected $placeholders = [];

  /**
   * @var
   *
   * Holds the tag of the running tests.
   */
  protected $tag;

  /**
   * @return mixed
   */
  public function getTag() {
    return $this->tag;
  }

  /**
   * @param mixed $tag
   *
   * @return BehatTestsAbstract
   */
  public function setTag($tag) {
    $this->tag = $tag;
    return $this;
  }

  /**
   * @param $key
   * @param $value
   *
   * @return BehatTestsAbstract
   */
  public function setMetadata($key, $value) {
    $this->metadata[$key] = $value;
    return $this;
  }
  /**
   * @return array
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * @return array
   */
  public function getEdit() {
    return $this->edit;
  }

  /**
   * @param $key
   * @param $value
   *
   * @return BehatTestsAbstract
   */
  public function setEdit($key, $value) {
    $this->edit[$key] = $value;
    return $this;
  }

  /**
   * @param null $key
   * @return array
   */
  public function getPlaceholders($key = NULL) {
    return $key ? $this->placeholders[$key] : $this->placeholders;
  }

  /**
   * @param $key
   * @param $value
   *
   * @return BehatTestsAbstract
   */
  public function setPlaceholder($key, $value) {
    $this->placeholders[$key] = $value;
    return $this;
  }

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
   * After each scenario invoke actions.
   *
   * @param $scenarioInterface
   *   The scenario object.
   */
  public function afterScenario(ScenarioInterface $scenarioInterface = NULL) {}

  /**
   * Execute a scenario from a feature file.
   *
   * @param $scenario
   *   The name of the scenario file.
   * @param $component
   *   Name of the module/theme.
   * @param string $type
   *   The type of the component: module or theme. Default is module.
   *
   * @throws \Exception
   */
  public function executeScenario($scenario, $component, $type = 'module') {
    // Get the path of the file.
    $path = DRUPAL_ROOT . '/' . drupal_get_path($type, $component) . '/src/Features/' . $scenario . '.feature';

    if (!$path) {
      throw new \Exception('The scenario is missing from the path ' . $path);
    }

    $test = file_get_contents($path);

    // Initialize Behat module step manager.
    $StepManager = new BehatBase($this);

    // Get the parser of the gherkin files.
    $parser = Behat::getParser();
    $scenarios = $parser->parse($test)->getScenarios();

    foreach ($scenarios as $scenario) {
      if ($this->getTag() && !in_array($this->getTag(), $scenario->getTags())) {
        // Run tests with specific tags.
        continue;
      }

      $this->beforeScenario($scenario);

      foreach ($scenario->getSteps() as $step) {

        // Invoke the steps.
        $StepManager->executeStep($step->getText(), $this->getPlaceholders());
      }

      $this->afterScenario($scenario);
    }
  }

  /**
   * Visiting a Drupal page.
   *
   * @param $path
   *   The internal path.
   */
  public function visit($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Sending the form.
   *
   * @param $element
   *   The submit button element.
   */
  public function sendForm($element) {
    $this->submitForm($this->edit, $element);
  }

}
