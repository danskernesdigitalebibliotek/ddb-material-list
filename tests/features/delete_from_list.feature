Feature: Deleting materials from list
  Users should be able to delete materials from their list.

  Scenario: User should be able to delete material
    Given a known user that has the following items on the list:
      | pid 1 |
      | pid 2 |
      | pid 3 |
    When deleting "pid 2" from the list
    Then the system should return success
    And the list should contain:
      | pid 1 |
      | pid 3 |
