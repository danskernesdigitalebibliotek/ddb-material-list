Feature: Fetching list
  Users should be able to fetch their list.

  Scenario: Should return an empty list if not found
    Given a known user that has no items on list
    When fetching the list
    Then the system should return success
    And the list should be emtpy

  Scenario: User can fetch their list
    Given a known user that has the following items on the list:
      | pid 1 |
      | pid 2 |
      | pid 3 |
    When fetching the list
    Then the system should return success
    And the list should contain:
      | pid 1 |
      | pid 2 |
      | pid 3 |

  Scenario: A user can check that a material is on the list
    Given a known user that has the following items on the list:
      | pid 1 |
      | pid 2 |
      | pid 3 |
    When checking if "pid 2" is on the list
    Then the system should return success

  Scenario: A user can check that a material is not on the list
    Given a known user that has the following items on the list:
      | pid 1 |
      | pid 2 |
      | pid 3 |
    When checking if "pid 4" is on the list
    Then the system should return not found
