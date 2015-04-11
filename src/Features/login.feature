Feature: Login testing.

  Scenario: Testing the login form.
    Given I visit 'user'
      And I fill in 'name' with '@user-name'
      And I fill in 'pass' with '@user-pass'
     When I press 'Log in'
     Then I should see '@user-name'
