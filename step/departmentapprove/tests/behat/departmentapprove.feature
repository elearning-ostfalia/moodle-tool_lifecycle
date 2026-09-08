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
    Then I should see "Renewal of course approval"
    And the following fields match these values:
      | Course full name | Course 1    |
      | Teachers         | Teacher 1   |
#      | Course summary   | Test course 1 Lorem ipsum   |
    When I press "Approve course"
    Then I should see "The course has been approved and can now continue to be offered."

    # Check if timestamp is set
    When I log in as "admin"
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets

    Then the following fields match these values:
      | Timestamp      | ##today##     |
    And the field "customfield_timestamp[enabled]" matches value "1"

  Scenario: Lifecycle: Department rejects course request
    When I visit department approval link
    When I press "Do not approve the course any further..."

    Then I should see "Your reasons for rejecting this course"
    And I set the field "This will be emailed to the course responsible person" to "test"
    When I press "Reject"

    Then I should see "The denial of the course approval was forwarded."

    # Check that timestamp is not set
    And I log in as "admin"
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets
    Then the field "customfield_timestamp[enabled]" matches value "0"

  Scenario: Lifecycle: Department opens invalid link
    When I visit "/admin/tool/lifecycle/step/departmentapprove/department_confirm.php?token=123"
    Then I should see "The request has already been processed, or the link is invalid"