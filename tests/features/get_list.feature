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
    And the list should be empty

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

  Scenario: Checking a material requires an valid pid
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When checking if "banana" is on the list
    Then the system should return validation error

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

  Scenario: Checking a list of materials requires valid pids
    Given a known user
    And they have the following items on the list:
        | material  |
        | 123-kat:1 |
        | 123-kat:2 |
        | 123-kat:3 |
    When checking if the list contains:
      | material  |
      | 123-kat:2 |
      | banana    |
    Then the system should return validation error

  Scenario: A user can check that a material is on the list, with another agency
    Given a known user
    And they have the following items on the list:
      | material      |
      | 123-katalog:1 |
      | 123-katalog:2 |
      | 123-katalog:3 |
    When checking if "321-basis:2" is on the list
    Then the system should return success

  Scenario: Invalid list items are not returned as a part of the list
    Given a known user
    And they have the following items on the list:
      | material      |
      | 123-basis:1   |
      # This item is invalid, and should not be part of the response.
      | banana        |
      | 123-basis:2   |
    Then fetching the list should return:
      | material      |
      # The order of the list is intentionally reversed. The last item in the precondition should be the first in the
      # response as items are returned with the most recently added first.
      | 123-basis:2   |
      | 123-basis:1   |
    And the system should return success

