<?php

/**
 * Contains Drupal\behat\BehatTestsAbstract.
 */
namespace Drupal\behat;

use Drupal\behat\Exception\BehatFailedStep;
use Drupal\simpletest\WebTestBase;

class BehatTestsAbstract extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('behat');

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
   * @var string
   *
   * The last url.
   */
  protected $url;

  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param string $url
   */
  public function setUrl($url) {
    $this->url = $url;
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
   */
  public function setMetadata($key, $value) {
    $this->metadata[$key] = $value;
  }
  /**
   * @return array
   */
  public function getMetadata() {
    return $this->metadata;
  }

  /**
   * @param $key
   * @param $value
   */
  public function setEdit($key, $value) {
    $this->edit[$key] = $value;
  }

  /**
   * Visiting a Drupal page.
   *
   * @param $path
   *   The internal path.
   */
  public function visit($path) {
    $this->drupalGet($path);
  }

  /**
   * Sending the form.
   *
   * @param $element
   *   The submit button element.
   * @param $url
   *   The url fo the form.
   */
  public function sendForm($element, $url) {
    $this->drupalPostForm($url, $this->edit, $element);
  }

  /**
   * Trigger xpath method.
   *
   * @param $xpath
   *   The xpath string to use in the search.
   * @param $pass
   *   Determine if we need to display the pass message.
   *
   * @throws BehatFailedStep
   * @return array
   */
  public function searchElement($xpath, $pass = TRUE) {
    if (!$result = $this->xpath($xpath)) {
      throw new BehatFailedStep(format_string('The element @xpath was not found in the page', [
        '@xpath' => $xpath
      ]));
    }

    if ($pass) {
      $this->pass(format_string('The element @element is present in the page.', ['@element' => $xpath]));
    }

    return $result;
  }

}