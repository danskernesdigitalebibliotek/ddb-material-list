Feature: Authentication
  Users should be authenticated with a token.

  Scenario: Bad token denies access
    Given an unknown user
    When fetching the list
    Then the system should return access denied

  Scenario: Proper token gives access
    Given a known user
    When fetching the list
    Then the system should return success
