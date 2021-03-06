Feature: Collect the pull requests put to review
  In order to let the squad follow the progress of a pull request (PR)
  As an author
  I want to put a specific PR to review

  @nominal
  Scenario: Put a PR to review
    When an author puts a PR to review
    Then the PR is added to the list of followed PRs
    And the squad should be notified that the PR has been successfully put to review

  @nominal
  Scenario: Put a PR to review multiple times
    Given an author puts a PR to review
    When an author puts a PR to review a second time
    Then the PR is updated with the new message id
    And the squad should be notified that the PR has been successfully put to review

  @secondary
  Scenario: Put a PR belonging to an unsupported repository to review
    When an author puts a PR belonging to an unsupported repository to review
    Then the PR is not added to the list of followed PRs

  @secondary
  Scenario: Put a PR to review on an unsupported channel
    When an author puts a PR to review on an unsupported channel
    Then the PR is not added to the list of followed PRs
