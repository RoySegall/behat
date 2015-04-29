<?php

namespace Drupal\Behat\FeaturesTraits;

trait BasicTrait {

  /**
   * @Given /^I fill in '(.*?)' with '(.*?)'$/
   */
  public function iFillInWith(BehatTestsAbstract $behat, $name, $value) {
    $behat->assertSession()->fieldExists($name);
    $behat->setEdit($name, $value);
  }

  /**
   * @Given /^I press '(.*?)'$/
   */
  public function iPress(BehatTestsAbstract $behat, $element) {
    $button = $behat->assertSession()->buttonExists($element);

    if ($button->getAttribute('type') == 'submit') {
      // This is a submit element. Call the submit form method.
      $behat->sendForm($element);
    }
    else {
      // Normal button. Press it.
      $button->press();
    }
  }

  /**
   * @Given /^I should see '(.*?)'$/
   */
  public function iShouldSee(BehatTestsAbstract $behat, $text) {
    $behat->assertSession()->pageTextContains($text);
  }

  /**
   * @Given /^I should see '(.*?)'$/
   */
  public function iVisit(BehatTestsAbstract $behat, $url) {
    $behat->visit($url);
  }

}