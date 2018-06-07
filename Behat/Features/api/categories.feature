Feature: Get categories for a specified branch
  In order to get categories for a specified branch
  As a client software developer
  I need to be able to retrieve them through the API.

  Scenario: Get categories
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/branch/1/categories"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
        "TODO": "TODO"
    }
    """