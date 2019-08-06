Feature: List migration
  It should be possible to migrate lists.

  Scenario: Migrated list gets bound to user GUID
    Given a known user
    And a migrated list for ouid "the ouid":
      | material |
      | first    |
      | second   |
      | third    |
    When the user runs migrate with "the ouid"
    Then fetching the list should return:
      | material |
      | third    |
      | second   |
      | first    |
