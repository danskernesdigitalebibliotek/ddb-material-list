Feature: List order
  The list should be ordered with the last added first.

  Scenario: Order by the last added first.
    Given a known user that has no items on list
    When "pid 1" is added to the list
    And "pid 2" is added to the list
    And "pid 3" is added to the list
    When fetching the list
    Then the system should return success
    And the list should contain:
      | pid 3 |
      | pid 2 |
      | pid 1 |
