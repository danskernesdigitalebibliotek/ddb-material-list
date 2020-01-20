Feature: Fetching list
  Users should be able to fetch their list.

  Scenario: Should return an error if list is not the default one
    Given a known user
    When fetching the "other" list
    Then the system should return not found

  Scenario: Should return an empty list if not found
    Given a known user that has no items on list
    When fetching the list
    Then the system should return success
    And the list should be emtpy

  Scenario: User can fetch their list
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When fetching the list
    Then the system should return success
    And the list should contain:
        | material  |
        | 123-kat:3 |
        | 123-kat:2 |
        | 123-kat:1 |

  Scenario: A user can check that a material is on the list
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When checking if "123-kat:2" is on the list
    Then the system should return success

  Scenario: A user can check that a material is not on the list
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When checking if "123-kat:4" is on the list
    Then the system should return not found

  Scenario: A user can check that a set of materials is on the list
    Given a known user
    And they have the following items on the list:
        | material  |
        | 123-kat:1 |
        | 123-kat:2 |
        | 123-kat:3 |
        | 123-kat:4 |
    When checking if the list contains:
      | material  |
      | 123-kat:2 |
      | 123-kat:4 |
      | 123-kat:5 |
    Then the list should contain:
      | material  |
      | 123-kat:4 |
      | 123-kat:2 |
