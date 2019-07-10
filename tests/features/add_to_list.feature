Feature: Add to list
  Users should be able to add materials to their list.

  Scenario: Add material to list
    Given a known user that has no items on list
    When "pid 1" is added to the list
    Then the system should return success
    And "pid 1" should be on the list

  Scenario: Add material to existing list
    Given a known user that has some items on list
    When "pid 1" is added to the list
    Then the system should return success
    And "pid 1" should be on the list

  Scenario: Materials should only be added once
    Given a known user that has "pid 1" on list
    When "pid 1" is added to the list
    Then the system should return success
    And "pid 1" should be on the list
    And the list should have 1 item
