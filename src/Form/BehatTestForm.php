<?php

/**
 * @file
 * Contains \Drupal\behat\Form\BehatTestForm.
 */

namespace Drupal\behat\Form;

use Drupal\behat\Behat;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * List tests arranged in groups that can be selected and run.
 */
class BehatTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'behat_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $parser = Behat::getParser();

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run tests'),
      '#tableselect' => TRUE,
      '#button_type' => 'primary',
    ];

    // Do not needlessly re-execute a full test discovery if the user input
    // already contains an explicit list of test classes to run.
    $user_input = $form_state->getUserInput();
    if (!empty($user_input['tests'])) {
      return $form;
    }

    // JavaScript-only table filters.
    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['table-filter', 'js-show'],
      ],
    ];
    $form['filters']['text'] = [
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#size' => 30,
      '#placeholder' => $this->t('Enter test name…'),
      '#attributes' => [
        'class' => ['table-filter-text'],
        'data-table' => '#simpletest-test-form',
        'autocomplete' => 'off',
        'title' => $this->t('Enter at least 3 characters of the test name or description to filter by.'),
      ],
    ];

    $form['tests'] = [
      '#type' => 'table',
      '#id' => 'simpletest-form-table',
      '#tableselect' => TRUE,
      '#header' => [
        ['data' => $this->t('Test'), 'class' => ['simpletest-test-label']],
        ['data' => $this->t('File'), 'class' => ['simpletest-test-description']],
      ],
      '#empty' => $this->t('No tests to display.'),
      '#attached' => [
        'library' => [
          'simpletest/drupal.simpletest',
        ],
      ],
    ];

    // Define the images used to expand/collapse the test groups.
    $image_collapsed = [
      '#theme' => 'image',
      '#uri' => 'core/misc/menu-collapsed.png',
      '#width' => '7',
      '#height' => '7',
      '#alt' => $this->t('Expand'),
      '#title' => $this->t('Expand'),
      '#suffix' => '<a href="#" class="simpletest-collapse">(' . $this->t('Expand') . ')</a>',
    ];
    $image_extended = [
      '#theme' => 'image',
      '#uri' => 'core/misc/menu-expanded.png',
      '#width' => '7',
      '#height' => '7',
      '#alt' => $this->t('Collapse'),
      '#title' => $this->t('Collapse'),
      '#suffix' => '<a href="#" class="simpletest-collapse">(' . $this->t('Collapse') . ')</a>',
    ];
    $form['tests']['#attached']['drupalSettings']['simpleTest']['images'] = [
      drupal_render($image_collapsed),
      drupal_render($image_extended),
    ];

    // Generate the list of tests arranged by group.
    $groups = Behat::getFeatureContexts();
    foreach ($groups as $provider => $tests) {
      $this->providers[] = $tests['class'];
      $form['tests'][$provider] = [
        '#attributes' => ['class' => ['simpletest-group']],
      ];

      // Make the class name safe for output on the page by replacing all
      // non-word/decimal characters with a dash (-).
      $group_class = 'module-' . strtolower(trim(preg_replace("/[^\w\d]/", "-", $provider)));

      // Override tableselect column with custom selector for this group.
      // This group-select-all checkbox is injected via JavaScript.
      $form['tests'][$provider]['select'] = [
        '#wrapper_attributes' => [
          'id' => $group_class,
          'class' => ['simpletest-group-select-all'],
        ],
      ];
      $form['tests'][$provider]['title'] = [
        // Expand/collapse image.
        '#prefix' => '<div class="simpletest-image" id="simpletest-test-group-' . $group_class . '"></div>',
        '#markup' => '<label for="' . $group_class . '-group-select-all">' . $provider . '</label>',
        '#wrapper_attributes' => [
          'class' => ['simpletest-group-label'],
        ],
      ];
      $form['tests'][$provider]['description'] = [
        '#markup' => '&nbsp;',
        '#wrapper_attributes' => [
          'class' => ['simpletest-group-description'],
        ],
      ];

      // Cycle through each test within the current group.
      $features = Behat::getComponentFeatures($tests['provider']);
      foreach ($features as $delta => $feature) {

        if (!$parsed = $parser->parse(file_get_contents($feature))) {
          continue;
        }

        $explode = explode('/', $feature);
        $feature_name = end($explode);

        $class = $provider . '-' . $feature_name;
        $form['tests'][$class] = [
          '#attributes' => ['class' => [$group_class . '-test', 'js-hide']],
        ];

        $form['tests'][$class]['title'] = [
          '#type' => 'label',
          '#title' => SafeMarkup::checkPlain($parsed->getTitle()),
          '#wrapper_attributes' => [
            'class' => ['simpletest-test-label', 'table-filter-text-source'],
          ],
        ];
        $form['tests'][$class]['description'] = [
          '#prefix' => '<div class="description">',
          '#markup' => SafeMarkup::checkPlain($feature_name),
          '#suffix' => '</div>',
          '#wrapper_attributes' => [
            'class' => ['simpletest-test-description', 'table-filter-text-source'],
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $base_url;
    // Test discovery does not run upon form submission.
    simpletest_classloader_register();

    // see \Drupal\simpletest\Form\SimpletestTestForm::submitForm().
    $user_input = $form_state->getUserInput();
    if ($form_state->isValueEmpty('tests') && !empty($user_input['tests'])) {
      $form_state->setValue('tests', $user_input['tests']);
    }

    if ($selected_tests = $form_state->getValue('tests')) {
      // Build a lists of tests.
      $features = $tests_list = $providers = [];
      foreach ($user_input['tests'] as $test) {
        list($provider, $feature) = explode('-', $test);
        $class = Behat::getFeatureContexts($provider)['class'];

        // Save what features files we need to run for each provider.
        $features[$class][] = str_replace('.features', '', $feature);

        // Store the feature context and the defining providers.
        // todo: Consider themes and features files location defined by provider.
        $providers[$class] = DRUPAL_ROOT . '/' . drupal_get_path('module', $provider) . '/src/Features/';

        // Collect all the classes we need to run.
        $tests_list['phpunit'][] = $class;
      }

      $tests_list['phpunit'] = array_unique($tests_list['phpunit']);

      // Set the
      putenv('SIMPLETEST_BASE_URL=' . $base_url);
      putenv('FEATURES_RUN=' . serialize($features));
      putenv('FEATURES_PROVIDERS=' . serialize($providers));
      $test_id = Behat::runTests($tests_list, 'drupal');
      $form_state->setRedirect(
        'behat.result_form',
        array('test_id' => $test_id)
      );
    }
  }

}
