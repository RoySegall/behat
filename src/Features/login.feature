Feature: Testing

  Scenario: I test me!
    Given I visit 'user'
    Given I fill in 'name' with '@user-name'
      And I fill in 'pass' with '@user-pass'
     When I press 'Log in'
     Then I should see '@user-name'