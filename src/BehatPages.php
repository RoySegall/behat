<?php
namespace Drupal\behat;

use Drupal\Core\Controller\ControllerBase;

class BehatPages extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {

    Behat::Step(NULL, "I visit ");

    return array(
      '#markup' => 'a',
    );
  }

}