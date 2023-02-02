Feature: Cross version compatability
  Items on a list should be retrievable across versions of the API.

  Scenario: Materials added in v1 are exposed as collections in v2
    Given a known user that has no items on list
    When material "123-kat:1" is added to the list
    When fetching collections in the list
    Then the system should return success
    And the list should contain collections:
      | collection        |
      | work-of:123-kat:1 |

  Scenario: Collections added in v2 are exposed as materials in v1
    Given a known user that has no items on list
    When collection "work-of:123-kat:1" is added to the list
    When fetching materials in the list
    Then the system should return success
    And the list should contain:
      | material  |
      | 123-kat:1 |

  Scenario: Materials added in v1 can be checked as collections in v2
    Given a known user
    When material "123-kat:1" is added to the list
    When checking if collection "work-of:123-kat:1" is on the list
    Then the system should return success

  Scenario: Collections added in v2 can be checked as materials in v1
    Given a known user that has no items on list
    When collection "work-of:123-kat:1" is added to the list
    When checking if material "123-kat:1" is on the list
    Then the system should return success

  Scenario: Materials added in v1 can be deleted as collections in v2
    Given a known user that has no items on list
    When material "123-kat:1" is added to the list
    When deleting collection "work-of:123-kat:1" from the list
    Then the system should return success
    When fetching materials in the list
    Then the list should be empty

  Scenario: Collections added in v2 can be deleted as materials in v1
    Given a known user that has no items on list
    When collection "work-of:123-kat:1" is added to the list
    When deleting "123-kat:1" from the list
    Then the system should return success
    When fetching collections in the list
    Then the list should be empty
