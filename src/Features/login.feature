Feature: Testing

  @api
  Scenario: I test me!
    Given I fill in 'name' with 'admin'
      And I fill in 'pass' with 'admin'
     When I press 'Log in' in 'user'
     Then I should see 'admin'