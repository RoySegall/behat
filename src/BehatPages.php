<?php
namespace Drupal\behat;

use Behat\Gherkin\Gherkin;
use Drupal\Core\Controller\ControllerBase;

class BehatPages extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
    $foo = new Gherkin();
    $element = array(
      '#markup' => 'Hello, world',
    );
    return $element;
  }

}