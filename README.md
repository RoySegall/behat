# Behat for Drupal 8.

The module provide BDD testing framework for contribute components.

## How it's works?
Each project that uses behat as a testing tool have a FeatureContext class that 
contains the step definition or any other additional information.

Each module will need to to implement a FeatureContext plugin that will keep the 
step definitions and other behat integration(i.e beforeScenario or afterScenario 
methods).

## Define a plugin
Behat module implements a FeatureContext plugin:

```php
<?php

/**
 * Contains Drupal\behat\Plugin\FeatureContext\FeatureContextBase.
 */
namespace Drupal\behat\Plugin\FeatureContext;

/**
 * @FeatureContext(
 *   id = "behat",
 *   label = @Translation("Behat"),
 * )
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FeatureContextBase extends BehatTestsAbstract {

  use BasicTrait;

  /**
   * {@inheritdoc}
   */
  public function beforeScenario(ScenarioInterface $scenarioInterface = NULL) {
    parent::beforeScenario($scenarioInterface);

    $account = $this->drupalCreateUser();
    $this->placeholders = [
      '@user-name' => $account->label(),
      '@user-pass' => $account->passRaw,
    ];
  }
}
```

This plugin implements a `beforeScenario` method to a create user for testing.
The `@runTestsInSeparateProcesses` and `@preserveGlobalState disabled` 
annotation needed by the PHPUnit testing framework for fire up a mink browser
environment.

## Step definitions
As mentioned above, the FeatureContext plugin is replacing the FeatureContext 
class. That class will keep all the step definition.

The default step definitions defined in a trait. In this way other modules could
provide more step definition and your FeatureContext could leverage them.

## Cucumber files
By default all the feature files will be located at MODULE/src/Features. In the 
future, you could specify other folder location in the plugin definition.

## Running the tests
There are two ways to run the tests. One way is using the UI under 
`admin/config/development/behat` and you can check which files you want to run.
This isn't a good practice since it's not running in batch operation.

The base way is to use drush: `drush bin/behat PROVIDER URL`.

The `PROVIDER` is the ID for the FeatureContext plugin. In our case is `behat`.

The `URL` is the URL of your Drupal 8 installation.

For example: `drush bin/behat behat http://localhost/drupal8`

Running specific features could be done with the feature option:
`drush bin/behat behat http://localhost/drupal8 --features=login`

This will run only the login.feature file defined by the behat module.
