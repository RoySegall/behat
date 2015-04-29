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

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Run tests'),
      '#tableselect' => TRUE,
      '#button_type' => 'primary',
    );

    // Do not needlessly re-execute a full test discovery if the user input
    // already contains an explicit list of test classes to run.
    $user_input = $form_state->getUserInput();
    if (!empty($user_input['tests'])) {
      return $form;
    }

    // JavaScript-only table filters.
    $form['filters'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('table-filter', 'js-show'),
      ),
    );
    $form['filters']['text'] = array(
      '#type' => 'search',
      '#title' => $this->t('Search'),
      '#size' => 30,
      '#placeholder' => $this->t('Enter test name…'),
      '#attributes' => array(
        'class' => array('table-filter-text'),
        'data-table' => '#simpletest-test-form',
        'autocomplete' => 'off',
        'title' => $this->t('Enter at least 3 characters of the test name or description to filter by.'),
      ),
    );

    $form['tests'] = array(
      '#type' => 'table',
      '#id' => 'simpletest-form-table',
      '#tableselect' => TRUE,
      '#header' => array(
        array('data' => $this->t('Test'), 'class' => array('simpletest-test-label')),
        array('data' => $this->t('File'), 'class' => array('simpletest-test-description')),
      ),
      '#empty' => $this->t('No tests to display.'),
      '#attached' => array(
        'library' => array(
          'simpletest/drupal.simpletest',
        ),
      ),
    );

    // Define the images used to expand/collapse the test groups.
    $image_collapsed = array(
      '#theme' => 'image',
      '#uri' => 'core/misc/menu-collapsed.png',
      '#width' => '7',
      '#height' => '7',
      '#alt' => $this->t('Expand'),
      '#title' => $this->t('Expand'),
      '#suffix' => '<a href="#" class="simpletest-collapse">(' . $this->t('Expand') . ')</a>',
    );
    $image_extended = array(
      '#theme' => 'image',
      '#uri' => 'core/misc/menu-expanded.png',
      '#width' => '7',
      '#height' => '7',
      '#alt' => $this->t('Collapse'),
      '#title' => $this->t('Collapse'),
      '#suffix' => '<a href="#" class="simpletest-collapse">(' . $this->t('Collapse') . ')</a>',
    );
    $form['tests']['#attached']['drupalSettings']['simpleTest']['images'] = [
      drupal_render($image_collapsed),
      drupal_render($image_extended),
    ];

    // Generate the list of tests arranged by group.
    $groups = Behat::getFeatureContexts();
    foreach ($groups as $group => $tests) {
      $form['tests'][$group] = array(
        '#attributes' => array('class' => array('simpletest-group')),
      );

      // Make the class name safe for output on the page by replacing all
      // non-word/decimal characters with a dash (-).
      $group_class = 'module-' . strtolower(trim(preg_replace("/[^\w\d]/", "-", $group)));

      // Override tableselect column with custom selector for this group.
      // This group-select-all checkbox is injected via JavaScript.
      $form['tests'][$group]['select'] = array(
        '#wrapper_attributes' => array(
          'id' => $group_class,
          'class' => array('simpletest-group-select-all'),
        ),
      );
      $form['tests'][$group]['title'] = array(
        // Expand/collapse image.
        '#prefix' => '<div class="simpletest-image" id="simpletest-test-group-' . $group_class . '"></div>',
        '#markup' => '<label for="' . $group_class . '-group-select-all">' . $group . '</label>',
        '#wrapper_attributes' => array(
          'class' => array('simpletest-group-label'),
        ),
      );
      $form['tests'][$group]['description'] = array(
        '#markup' => '&nbsp;',
        '#wrapper_attributes' => array(
          'class' => array('simpletest-group-description'),
        ),
      );

      // Cycle through each test within the current group.
      $features = Behat::getComponentFeatures($tests['provider']);
      foreach ($features as $delta => $feature) {
        $form['tests'][$delta] = array(
          '#attributes' => array('class' => array($group_class . '-test', 'js-hide')),
        );

        if (!$parsed = $parser->parse(file_get_contents($feature))) {
          continue;
        }

        $explode = explode('/', $feature);
        $form['tests'][$delta]['title'] = array(
          '#type' => 'label',
          '#title' => SafeMarkup::checkPlain($parsed->getTitle()),
          '#wrapper_attributes' => array(
            'class' => array('simpletest-test-label', 'table-filter-text-source'),
          ),
        );
        $form['tests'][$delta]['description'] = array(
          '#prefix' => '<div class="description">',
          '#markup' => SafeMarkup::checkPlain(end($explode)),
          '#suffix' => '</div>',
          '#wrapper_attributes' => array(
            'class' => array('simpletest-test-description', 'table-filter-text-source'),
          ),
        );
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
