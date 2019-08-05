Feature: Deleting materials from list
  Users should be able to delete materials from their list.

  Scenario: User should be able to delete material
    Given a known user
    And they have the following items on the list:
      | material |
      | pid-1    |
      | pid-2    |
      | pid-3    |
    When deleting "pid-2" from the list
    Then the system should return success
    And fetching the list should return:
      | material |
      | pid-3    |
      | pid-1    |
