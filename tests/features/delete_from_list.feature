Feature: Deleting materials from list
  Users should be able to delete materials from their list.

  Scenario: User should be able to delete material
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When deleting "123-kat:2" from the list
    Then the system should return success
    And fetching the list should return:
      | material  |
      | 123-kat:3 |
      | 123-kat:1 |

  Scenario: Deleting a material requires a valid pid
    Given a known user
    And they have the following items on the list:
      | material  |
      | 123-kat:1 |
      | 123-kat:2 |
      | 123-kat:3 |
    When deleting "banana" from the list
    Then the system should return validation error
    And fetching the list should return:
      | material  |
      | 123-kat:3 |
      | 123-kat:2 |
      | 123-kat:1 |

  Scenario: User should be able to delete basis/katalog material, even with other agency
    Given a known user
    And they have the following items on the list:
      | material      |
      | 123-katalog:1 |
      | 123-katalog:2 |
      | 321-basis:3 |
    When deleting "312-basis:2" from the list
    And deleting "123-katalog:3" from the list
    Then the system should return success
    And fetching the list should return:
        | material      |
        | 123-katalog:1 |
