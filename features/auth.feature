Feature:
  In order to access API i need to retrieve JWT token

  Background:
    Given there are FOS User with the following details:
      | username           | password | email                       |
      | adminTest          | password | adminTest@blockfolioapi.com |

  Scenario: I try to retrieve JWT token with correct credentials
    When I send a POST request to "/api/auth/login" with json data:
    """
    {
      "username": "adminTest",
      "password": "password"
    }
    """
    Then the response code should be 200
    Then the response json should have key "token"

  Scenario: I try to retrieve JWT token with wrong credentials
    When I send a POST request to "/api/auth/login" with json data:
    """
    {
      "username": "test123",
      "password": "password"
    }
    """
    Then the response code should be 401
