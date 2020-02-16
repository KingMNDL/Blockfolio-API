Feature:
  Swagger should be available

  Scenario: I try to access docs
    When I send a GET request to "/api/doc"
    Then the response code should be 200
