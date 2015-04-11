# Behat for Drupal 8 contribute components.

The module provide BDD testing framework to contribute components.

## How it's works?
The Behat we all know and love built from a couple of symofony elements. This 
module use gherkin library which read the cucumber files(AKA *.feature file).

After tearing down the feature file to steps definition behat module will search
for plugins which their ID match the step.

Eventually the behat module is just a layer which sit above the unit test
framework and trigger PHP commands.

## Define a plugin
Defining a plugin is very easy. All you need to do is to implement Behat step
definition plugin. For example the `I visit PATH` plugin:

```php
<?php

/**
 * Contains Drupal\behat\Plugin\Step\Visit.
 */
namespace Drupal\behat\Plugin\Step;

use Drupal\behat\BehatStepAbstract;
use Drupal\behat\BehatTestsAbstract;

/**
 * @Step(
 *  id = "I visit '(.*?)'"
 * )
 */
class Visit extends BehatStepAbstract {

  public function step(BehatTestsAbstract $behat, $url) {
    $behat->visit($url);
  }

}
```

In the feature file it look like this:
```cucumber
  Given I visit 'user'
```

## Define a test
The test definition is quite easy as well. Create a file under the `Tests` 
folder:
```php
<?php

/**
 * Simple login test.
 *
 * @group behat
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Login extends BehatTestsAbstract {

  public function testLogin() {
    $account = $this->drupalCreateUser();
    $this
      ->setPlaceholder('@user-name', $account->label())
      ->setPlaceholder('@user-pass', $account->passRaw)
      ->executeScenario('login', 'behat');
  }

}
```

You can see there are arguments passed to the steps definition via `$this->setPlaceholder()`
This wll use us later on in the cucumber files. The key method is `$this->executeScenario()`
Which invoke a file named `login.feature` under the behat module. If you need to 
invoke feature file to a theme component you'll need to write 
`$this->executeScenario('collapsed', 'bootstrap', 'theme');`

## Cucumber files
The cucumber files should be located at MODULE/src/Features/*.feature

## Patch
You'll need to patch Drupal core with the latest [patch](https://www.drupal.org/node/2232861)
