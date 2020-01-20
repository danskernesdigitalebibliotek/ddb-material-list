Feature: List order
  The list should be ordered with the last added first.

  Scenario: Order by the last added first.
    Given a known user that has no items on list
    When "123-kat:1" is added to the list
    And "123-kat:2" is added to the list
    And "123-kat:3" is added to the list
    When fetching the list
    Then the system should return success
    And the list should contain:
      | material  |
      | 123-kat:3 |
      | 123-kat:2 |
      | 123-kat:1 |
