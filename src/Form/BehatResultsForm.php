<?php

/**
 * @file
 * Contains \Drupal\behat\Form\BehatResultsForm.
 */

namespace Drupal\behat\Form;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\simpletest\Form\SimpletestResultsForm;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Parser;

/**
 * Test results form for $test_id.
 *
 * Note that the UI strings are not translated because this form is also used
 * from run-tests.sh.
 *
 * @see simpletest_script_open_browser()
 * @see run-tests.sh
 */
class BehatResultsForm extends SimpletestResultsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'behat_results_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $test_id = NULL) {
    // Make sure there are test results to display and a re-run is not being
    // performed.
    $results = array();
    $yml_path = drupal_get_path('module', 'behat') . '/results/behat-' . $test_id . '.yml';

    if (is_numeric($test_id) && !file_exists($yml_path)) {
      drupal_set_message($this->t('No test results to display.'), 'error');
      return new RedirectResponse($this->url('behat.test_form', array(), array('absolute' => TRUE)));
    }

    // Load all classes and include CSS.
    $form['#attached']['library'][] = 'simpletest/drupal.simpletest';

    // Add the results form.
    $form['test_id'] = $test_id;
    $filter = static::addResultForm($form, $results, $this->getStringTranslation());

    // Actions.
    $form['#action'] = $this->url('behat.result_form', array('test_id' => 're-run'));
    $form['action'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Actions'),
      '#attributes' => array('class' => array('container-inline')),
      '#weight' => -11,
    );

    $form['action']['filter'] = array(
      '#type' => 'select',
      '#title' => 'Filter',
      '#options' => array(
        'all' => $this->t('All (@count)', array('@count' => count($filter['pass']) + count($filter['fail']))),
        'pass' => $this->t('Pass (@count)', array('@count' => count($filter['pass']))),
        'fail' => $this->t('Fail (@count)', array('@count' => count($filter['fail']))),
      ),
    );
    $form['action']['filter']['#default_value'] = ($filter['fail'] ? 'fail' : 'all');

    // Categorized test classes for to be used with selected filter value.
    $form['action']['filter_pass'] = array(
      '#type' => 'hidden',
      '#default_value' => implode(',', $filter['pass']),
    );
    $form['action']['filter_fail'] = array(
      '#type' => 'hidden',
      '#default_value' => implode(',', $filter['fail']),
    );

    $form['action']['op'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Run tests'),
    );

    $form['action']['return'] = array(
      '#type' => 'link',
      '#title' => $this->t('Return to list'),
      '#url' => Url::fromRoute('behat.test_form'),
    );

    if (FALSE) {
      $fileSystem = new Filesystem();
      $fileSystem->remove([drupal_get_path('module', 'behat') . '/results/behat-' . $test_id . '.yml']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pass = $form_state->getValue('filter_pass') ? explode(',', $form_state->getValue('filter_pass')) : array();
    $fail = $form_state->getValue('filter_fail') ? explode(',', $form_state->getValue('filter_fail')) : array();

    if ($form_state->getValue('filter') == 'all') {
      $classes = array_merge($pass, $fail);
    }
    elseif ($form_state->getValue('filter') == 'pass') {
      $classes = $pass;
    }
    else {
      $classes = $fail;
    }

    if (!$classes) {
      $form_state->setRedirect('behat.test_form');
      return;
    }

    $form_execute = array();
    $form_state_execute = new FormState();
    foreach ($classes as $class) {
      $form_state_execute->setValue(['tests', $class], $class);
    }

    // Submit the simpletest test form to rerun the tests.
    // Under normal circumstances, a form object's submitForm() should never be
    // called directly, FormBuilder::submitForm() should be called instead.
    // However, it calls $form_state->setProgrammed(), which disables the Batch API.
    $simpletest_test_form = new BehatTestForm();
    $simpletest_test_form->buildForm($form_execute, $form_state_execute);
    $simpletest_test_form->submitForm($form_execute, $form_state_execute);
    if ($redirect = $form_state_execute->getRedirect()) {
      $form_state->setRedirectUrl($redirect);
    }
  }

  /**
   * Adds the result form to a $form.
   *
   * This is a static method so that run-tests.sh can use it to generate a
   * results page completely external to Drupal. This is why the UI strings are
   * not wrapped in t().
   *
   * @param array $form
   *   The form to attach the results to.
   * @param array $test_results
   *   The simpletest results.
   *
   * @return array
   *   A list of tests the passed and failed. The array has two keys, 'pass' and
   *   'fail'. Each contains a list of test classes.
   *
   * @see simpletest_script_open_browser()
   * @see run-tests.sh
   */
  public static function addResultForm(array &$form, array $results) {
    $id = $form['test_id'];

    // Transform the test results to be grouped by test class.
    $test_results = array();
    foreach ($results as $result) {
      if (!isset($test_results[$result->test_class])) {
        $test_results[$result->test_class] = array();
      }
      $test_results[$result->test_class][] = $result;
    }

    $image_status_map = static::buildStatusImageMap();

    // Keep track of which test cases passed or failed.
    $filter = array(
      'pass' => array(),
      'fail' => array(),
    );

    // Summary result widget.
    $form['result'] = array(
      '#type' => 'fieldset',
      '#title' => 'Results',
      // Because this is used in a theme-less situation need to provide a
      // default.
      '#attributes' => array(),
    );
    $form['result']['summary'] = $summary = array(
      '#theme' => 'simpletest_result_summary',
      '#pass' => 0,
      '#fail' => 0,
      '#exception' => 0,
      '#debug' => 0,
    );

    \Drupal::service('test_discovery')->registerTestNamespaces();

    $yml_path = drupal_get_path('module', 'behat') . '/results/behat-' . $id . '.yml';

    $parser = new Parser();
    $logs = $parser->parse(file_get_contents($yml_path));

    // Cycle through each test group.
    $header = array(
      'Message',
      array('colspan' => 2, 'data' => 'Status')
    );
    $form['result']['results'] = array();
    foreach ($logs as $scenario => $assertions) {
      // Create group details with summary information.
      $form['result']['results'][$scenario] = array(
        '#type' => 'details',
        '#title' => $scenario,
        '#open' => TRUE,
        '#description' => 'voo',
      );
      $form['result']['results'][$scenario]['summary'] = $summary;
      $group_summary =& $form['result']['results'][$scenario]['summary'];

      // Create table of assertions for the group.
      $rows = array();
      foreach ($assertions as $assertion) {
        $row = array();
        // Assertion messages are in code, so we assume they are safe.
        $row[] = SafeMarkup::set($assertion['step']);
        $row[] = $image_status_map[$assertion['status']];

        $class = 'simpletest-' . $assertion['status'];
        if ($assertion->message_group == 'Debug') {
          $class = 'simpletest-debug';
        }
        $rows[] = array('data' => $row, 'class' => array($class));

        $group_summary['#' . $assertion['status']]++;
        $form['result']['summary']['#' . $assertion['status']]++;
      }
      $form['result']['results'][$scenario]['table'] = array(
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      );

      // Set summary information.
      $group_summary['#ok'] = $group_summary['#fail'] + $group_summary['#exception'] == 0;
      $form['result']['results'][$scenario]['#open'] = !$group_summary['#ok'];

      // Store test group (class) as for use in filter.
      $filter[$group_summary['#ok'] ? 'pass' : 'fail'][] = $scenario;
    }

    // Overall summary status.
    $form['result']['summary']['#ok'] = $form['result']['summary']['#fail'] + $form['result']['summary']['#exception'] == 0;

    return $filter;
  }

}
