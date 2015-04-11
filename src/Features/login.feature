Feature: Login testing.

  @login-success
  Scenario: Testing the login form.
    Given I visit 'user'
      And I fill in 'Username' with '@user-name'
      And I fill in 'Password' with '@user-pass'
     When I press 'Log in'
     Then I should see '@user-name'

  @login-failed
  Scenario: Testing the user can't login with bas credentials.
    Given I visit 'user'
      And I fill in 'Username' with 'foo'
      And I fill in 'Password' with 'bar'
     When I press 'Log in'
     Then I should see 'Sorry, unrecognized username or password.'