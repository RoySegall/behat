<?php

namespace Drupal\behat;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Parser;
use Drupal\behat\Exception\BehatStepException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;

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
    if (!preg_match($step, $step_definition, $matches)) {
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
    $fs = new Filesystem();

    $behat_path = \Drupal::service('file_system')->realpath('public://behat');
    $yml_path = $behat_path . '/behat-1.yml';

    if (!$fs->exists($behat_path)) {
      $fs->mkdir($behat_path);
    }

    if (!$fs->exists($yml_path)) {
      $fs->touch($yml_path);
    }

    $array = array(
      'foo' => 'bar',
      'bar' => array('foo' => 'bar', 'bar' => 'baz'),
    );

    $dumper = new Dumper();

    $yaml = $dumper->dump($array);
    dpm($fs);

    file_put_contents($yml_path, $yaml);

    $element = array(
      '#markup' => 'Hello world!',
    );
    return $element;
  }

  /**
   * Find the the step definition from the annotation.
   *
   * @param $syntax
   *   The annotation of the method.
   *
   * @return string
   *   The step definition.
   */
  public static function getBehatStepDefinition($syntax) {

    if (!$start = strpos($syntax, '@Given ')) {
      return;
    }

    $explode = explode("\n", substr($syntax, $start + strlen('@Given ')));
    return $explode[0];
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
