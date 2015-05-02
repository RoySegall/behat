<?php

namespace Drupal\behat;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Parser;
use Drupal\behat\Exception\BehatStepException;
use Drupal\simpletest\TestDiscovery;

class Behat {

  public static function getFeatureContexts($provider = NULL) {
    $providers = \Drupal::service('plugin.manager.behat.FeatureContext');
    return $provider ? $providers->getDefinition($provider) : $providers->getDefinitions();
  }

  /**
   * Return list of all the feature files of a module.
   *
   * @param $name
   *   The name of the component.
   * @param $type
   *   The type of the component: module or theme. Default to module.
   * @param $dir
   *   The directory. Default to src/features.
   *
   * @return array
   *   Array of features name.
   */
  public static function getComponentFeatures($name, $type = 'module', $dir = 'src/Features') {
    return glob(drupal_get_path($type, $name) . '/' . $dir . '/*.feature');
  }

  /**
   * Invoking a step.
   *
   * @param BehatTestsAbstract $behat
   *   A behat test instance.
   * @param $step_definition
   *   A step definition.
   *
   * @throws BehatStepException
   * @return null|array
   */
  public static function FeatureContext(BehatTestsAbstract $behat, $step_definition) {
    $featureContext = \Drupal::service('plugin.manager.behat.FeatureContext')->getDefinitions();

    return $featureContext;

    foreach ($steps as $step) {
      if ($results = self::stepDefinitionMatch($step['id'], $step_definition)) {
        // Get the step instance.
        $object = \Drupal::service('plugin.manager.behat.step')->createInstance($results['step']);

        // Reflect the instance.
        $object_reflection = new \ReflectionClass($object);
        $reflection = new \ReflectionClass($object_reflection->getName());

        // Invoke the
        $reflection->getMethod('step')->invokeArgs($object, array($behat) + $results['arguments']);
        return TRUE;
      }
    }

    throw new BehatStepException($step_definition);
  }

  /**
   * Verify if the step definition match any plugin step.
   *
   * @param $step
   *   The current step.
   * @param $step_definition
   *   The step definition.
   * @return array|bool
   */
  static public function stepDefinitionMatch($step, $step_definition) {
    if (!preg_match('/' . $step . '/', $step_definition, $matches)) {
      return FALSE;
    }

    // Remove the step and keep the arguments.
    unset($matches[0]);

    return array(
      'arguments' => $matches,
      'step' => $step,
    );
  }

  /**
   * Get a new instance of Gherkin parser.
   *
   * @return Parser
   */
  static public function getParser() {
    $keywords = new ArrayKeywords(array(
      'en' => array(
        'feature' => 'Feature',
        'background'  => 'Background',
        'scenario'  => 'Scenario',
        'scenario_outline' => 'Scenario Outline|Scenario Template',
        'examples'  => 'Examples|Scenarios',
        'given' => 'Given',
        'when'  => 'When',
        'then'  => 'Then',
        'and'  => 'And',
        'but'  => 'But'
      )
    ));

    // Allow other module to alter the parser key words.
    \Drupal::moduleHandler()->alter('behat_parser_words', $keywords);

    $lexer  = new Lexer($keywords);

    return new Parser($lexer);
  }

  /**
   * This is a dummy method for tests of the behat module.
   */
  public static function content() {
    $parser = self::getParser();

    foreach (glob(drupal_get_path('module', 'behat') . '/src/features/*.feature') as $feature) {
      $test = file_get_contents($feature);
      $scenarios = $parser->parse($test)->getScenarios();

      foreach ($scenarios as $scenario) {
        foreach ($scenario->getSteps() as $step) {
//          dpm($step);
        }
      }
    }

    $element = array(
      '#markup' => 'Hello world!',
    );
    return $element;
  }

  /**
   * Run a list of tests. The function simpletest_run_tests() run the tests but
   * not passing the test id variable through the environment variable.
   *
   * Since we need to run only PHPUnit tests we can set the test ID to the
   * environment variable and run the behat tests.
   *
   * @see simpletest_run_tests().
   */
  public static function runTests($test_list) {
    $test_id = db_insert('simpletest_test_id')
      ->useDefaults(array('test_id'))
      ->execute();

    if (!empty($test_list['phpunit'])) {
      putenv('TESTID=' . $test_id);
      $phpunit_results = simpletest_run_phpunit_tests($test_id, $test_list['phpunit']);
      simpletest_process_phpunit_results($phpunit_results);
    }

    // Early return if there are no further tests to run.
    if (empty($test_list['simpletest'])) {
      return $test_id;
    }
  }
}