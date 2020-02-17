Feature:
  Asset api should let authenticated User to CRUD his assets

  Background:
    Given there are FOS User with the following details:
      | username           | password | email                       |
      | user_one           | password | user_one@blockfolioapi.com  |
      | user_two           | password | user_two@blockfolioapi.com  |

    Given there are Assets with following details:
      | label     | value   | currency | user      |
      | binance   | 1       | BTC      | user_one  |
      | usb_stick | 0.5     | BTC      | user_two  |

  Scenario: I try to retrieve assets without valid JWT token
    When I send a GET request to "/api/assets/all"
    Then the response code should be 401

  Scenario: I try to retrieve assets with valid JWT token
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a GET request to "/api/assets/all"
    Then the response code should be 200

  Scenario: I try to retrieve asset info which doesnt exist
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a GET request to "/api/assets/get/5"
    Then the response code should be 404

  Scenario: I try to retrieve asset info which exists and belongs to user
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a GET request to "/api/assets/get/1"
    Then the response code should be 200

  Scenario: I try to retrieve asset info which exists and doesnt belong to authenticated user
    Given I am successfully authenticated as a FOS user: "user_two"
    When I send a GET request to "/api/assets/get/1"
    Then the response code should be 403

  Scenario: I try to create asset with correct data
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a POST request to "/api/assets/create" with json data:
    """
    {
      "label": "binance",
      "value": "1",
      "currency": "BTC"
    }
    """
    Then the response code should be 201

  Scenario: I try to create asset with wrong data
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a POST request to "/api/assets/create" with json data:
    """
    {
      "label": "",
      "value": "-1",
      "currency": ""
    }
    """
    Then the response code should be 500

  Scenario: I try to update asset which exists and belongs to user
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a PUT request to "/api/assets/update/1" with json data:
    """
    {
      "label": "binance",
      "value": "1",
      "currency": "BTC"
    }
    """
    Then the response code should be 204

  Scenario: I try to update asset which exists but doesnt belong to user
    Given I am successfully authenticated as a FOS user: "user_two"
    When I send a PUT request to "/api/assets/update/1" with json data:
    """
    {
      "label": "binance",
      "value": "1",
      "currency": "BTC"
    }
    """
    Then the response code should be 403

  Scenario: I try to update asset which doesnt exist
    Given I am successfully authenticated as a FOS user: "user_two"
    When I send a PUT request to "/api/assets/update/3" with json data:
    """
    {
      "label": "binance",
      "value": "1",
      "currency": "BTC"
    }
    """
    Then the response code should be 404


  Scenario: I try to retrieve delete asset which exists and belongs to user
    Given I am successfully authenticated as a FOS user: "user_one"
    When I send a DELETE request to "/api/assets/delete/1"
    Then the response code should be 204

  Scenario: I try to retrieve delete asset which exists and doesnt belongs to user
    Given I am successfully authenticated as a FOS user: "user_TWO"
    When I send a DELETE request to "/api/assets/delete/1"
    Then the response code should be 403

  Scenario: I try to retrieve delete asset which doesnt exist
    Given I am successfully authenticated as a FOS user: "user_TWO"
    When I send a DELETE request to "/api/assets/delete/3"
    Then the response code should be 404