<?php

namespace Drupal\behat\FeaturesTraits;

trait BasicTrait {

  /**
   * @Given /^I fill in "([^"]*)" with "([^"]*)"$/
   */
  public function iFillInWith($name, $value) {
    $this->assertSession()->fieldExists($name);
    $this->edit[$name] = $value;
  }

  /**
   * @Given /^I press "([^"]*)"$/
   */
  public function iPress($element) {
    $button = $this->assertSession()->buttonExists($element);

    if ($button->getAttribute('type') == 'submit') {
      // This is a submit element. Call the submit form method.
      $this->submitForm($this->edit, $element);
    }
    else {
      // Normal button. Press it.
      $button->press();
    }
  }

  /**
   * @Given /^I should see "([^"]*)"$/
   */
  public function iShouldSee($text) {
    $this->assertSession()->pageTextContains($text);
  }

  /**
   * @Given /^I visit "([^"]*)"$/
   */
  public function iVisit($url) {
    $this->drupalGet($url);
  }

  /**
   * @Given /^I login as user "([^"]*)"$/
   */
  public function iLogIng($name) {
    exit('sss');
  }

}
