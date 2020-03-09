Feature: Add to list
  Users should be able to add materials to their list.

  Scenario: Add material to list
    Given a known user that has no items on list
    When "123-kat:1" is added to the list
    Then the system should return success
    And "123-kat:1" should be on the list

  Scenario: Add material to existing list
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When "123-kat:4" is added to the list
    Then the system should return success
    And "123-kat:4" should be on the list

  Scenario: Materials should only be added once
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
    When "123-kat:1" is added to the list
    Then the system should return success
    And fetching the list should return:
        | material  |
        | 123-kat:1 |

  Scenario Outline: Adding a material requires a valid pid
    Given a known user
    When "<material>" is added to the list
    Then the system should return <response>

    Examples:
      | material                   | response         |
      | 870970-basis:50710270      | success          |
      | 775100-katalog:50966399    | success          |
      | 159014-lokalbibl:ADL142808 | success          |
      | banana                     | validation error |

  Scenario: Materials in katalog/basis should only be added once, even if it has another agency
    Given a known user
    And they have the following items on the list:
      | material      |
      | 123-katalog:1 |
      | 321-basis:2 |
    When "321-basis:1" is added to the list
    And "123-katalog:2" is added to the list
    Then the system should return success
    And fetching the list should return:
      | material      |
      | 123-katalog:2 |
      | 321-basis:1   |
