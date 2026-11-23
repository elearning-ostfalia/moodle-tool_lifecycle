@lifecyclestep_departmentapprove @tool_lifecycle @javascript
Feature: Lifecycle: Department responsible persons can approve courses
  As a department responsible person
  I'm able to approve or reject requests

  ## -------------------
  ## Bitte darauf achten, dass keine anderen Plugins während des Tests tasks ausführen wollen
  ## -------------------

  Background:
    Given the following "custom field categories" exist:
      | name  | component   | area   | itemid |
      | Other | core_course | course | 0      |
    And the following "custom fields" exist:
      | name          | category | type     | shortname | configdata                |
      | Contact       | Other    | text     | contact   | {"defaultvalue":"Hello"}  |
      | Timestamp     | Other    | date     | timestamp | {"includetime":0}                                   |

    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | teacher2 | Teacher | 2 | teacher2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | groupmode | customfield_contact |
      | Course 1 | C1        | 0        | 1         | mail@test.org       |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher2 | C1 | teacher |

    And I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | Department approval |
      | Displayed workflow title   | Department approval  |
      | rollbackdelay[number]      | 1              |
      | finishdelay[number]        | 2              |
    And I press "Save changes"
    And I select "Manual trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | Department approval                       |
      | Action name                | Department approval                       |
      | Capability                 | moodle/course:manageactivities     |
    And I press "Save changes"
    And I select "Department approval step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Department approval"
    And I set the following fields to these values:
      | Instance name          | Department approval |
      | Reply-to email address | xyz@test.org        |
      | Content HTML Template  | testing        |
    And I press "Save changes"
    # Follow-up step: it is only reached if the department rejects the course.
    And I select "Make Course Invisible Step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Make invisible"
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    When I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Department approval" in the "Course 1" row of the "tool_lifecycle_remaining" table
    When I log out

    And I log in as "admin"

    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    When I log out

  Scenario: Lifecycle: Department approves course
    When I visit department approval link
    Then I should see "Extension of course approval"
    And the following fields match these values:
      | Course full name | Course 1    |
      | Teachers         | Teacher 1   |
#      | Course summary   | Test course 1 Lorem ipsum   |
    When I press "Extend approval"
    Then I should see "The course approval has been extended. The course can continue to be offered."

    # Check if timestamp is set
    When I log in as "admin"
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets

    Then the following fields match these values:
      | Timestamp      | ##today##     |
    And the field "customfield_timestamp[enabled]" matches value "1"

  Scenario: Lifecycle: Department rejects course request
    When I visit department approval link
    When I press "Discontinue course offering..."

    Then I should see "Your reasons for discontinuing the course offering"
    And I set the field "This will be emailed to the course responsible person" to "test"
    When I press "Discontinue course offering"

    Then I should see "Your notice that the course offering is to be discontinued was forwarded."

    # Check that timestamp is not set
    And I log in as "admin"
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets
    Then the field "customfield_timestamp[enabled]" matches value "0"

  Scenario: Lifecycle: Department opens invalid link
    When I visit "/admin/tool/lifecycle/step/departmentapprove/department_confirm.php?token=123"
    Then I should see "The request has already been processed, or the link is invalid"

  Scenario: Lifecycle: Department is reminded after deadline and can still approve
    Given the department approval deadline has expired
    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    # Reminder has been sent and a new deadline has been set.
    Then the department approval deadline should be in the future
    And I log out

    # The link is still valid after the reminder.
    When I visit department approval link
    Then I should see "Extension of course approval"
    When I press "Extend approval"
    Then I should see "The course approval has been extended. The course can continue to be offered."

  @behat_email
  Scenario: Lifecycle: Reminder mail is repeated to department after each expired deadline
    Given an email catcher server is configured
    And I empty the email inbox
    And I log in as "admin"

    # Deadline not yet expired => no reminder.
    When I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    Then user "mail@test.org" should have 0 emails with subject containing "Reminder:"
    And user "admin" should have 0 emails with subject containing "no response from department"

    # First expiry => first reminder to department, admin is informed.
    When the department approval deadline has expired
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    Then user "mail@test.org" should have 1 email with subject containing "Reminder:"
    And user "admin" should have 1 email with subject containing "no response from department"
    And the department approval deadline should be in the future

    # New deadline not yet expired => no further reminder.
    When I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    Then user "mail@test.org" should have 1 email with subject containing "Reminder:"
    And user "admin" should have 1 email with subject containing "no response from department"

    # Second expiry => reminder is repeated, admin is informed again.
    When the department approval deadline has expired
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    Then user "mail@test.org" should have 2 emails with subject containing "Reminder:"
    And user "admin" should have 2 emails with subject containing "no response from department"

  Scenario: Lifecycle: Approval ends the process and delays the course
    # Entering the step must not delay the course.
    Then the course "C1" should not be delayed for workflow "Department approval"
    When I visit department approval link
    And I press "Extend approval"
    Then I should see "The course approval has been extended. The course can continue to be offered."

    When I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    Then the course "C1" should not be in a lifecycle process
    And the course "C1" should be delayed for workflow "Department approval" because of rollback
    # The follow-up step has not been executed.
    And the course "C1" should be visible

  Scenario: Lifecycle: Rejection continues the process with the next step
    Then the course "C1" should not be delayed for workflow "Department approval"
    When I visit department approval link
    And I press "Discontinue course offering..."
    And I set the field "This will be emailed to the course responsible person" to "test"
    And I press "Discontinue course offering"
    Then I should see "Your notice that the course offering is to be discontinued was forwarded."

    When I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    # The follow-up step "Make invisible" has been executed and finished the workflow.
    Then the course "C1" should be invisible
    And the course "C1" should not be in a lifecycle process
    And the course "C1" should be delayed for workflow "Department approval" because of finish
