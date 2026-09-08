@tool @tool_lifecycle @manual_trigger
Feature: Select duplicate 2 step and test view and actions

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    #
    And the following "categories" exist:
      | name    | category | idnumber |
      | CAT A   | 0        | cata     |
      | CAT B   | cata     | catba    |
      | CAT C   | catba    | catc     |
      | CAT D   | catc     | catd     |
      | CAT E   | catd     | cate     |
      | CAT AA  | 0        | cataa    |
      | Archive | 0        | archive  |
      | CAT 1   | archive  | cat1     |
      | CAT 2   | cat1     | cat2     |
    # CAT A: Course A
    #   CAT B
    #      CAT C: Course C
    #         CAT D:
    #            CAT E
    # CAT AA
    # Archive: ArchCourseX
    #   CAT 1: ArchCourse 1
    #      CAT 2
    And the following "courses" exist:
      | fullname     | shortname  | category |
      | Course A     | CA         | cata     |
      | Course C     | CC         | catc     |
      | ArchCourse 1 | CArch1     | cat1     |
      | ArchCourseX  | CArchX     | archive  |
    And the following "course enrolments" exist:
      | user     | course  | role           |
      | teacher1 | CA      | editingteacher |
      | teacher1 | CArch1  | editingteacher |
      | teacher1 | CArchX  | editingteacher |
      | teacher1 | CC      | editingteacher |
    And I log in as "admin"
    And I am on workflowdrafts page
    And I click on "Create new workflow" "link"
    And I set the following fields to these values:
      | Title                      | Reuse course   |
      | Displayed workflow title   | Reuse course   |
      | rollbackdelay[number]      | 0              |
      | finishdelay[number]        | 0              |
    And I press "Save changes"
    And I select "Manual trigger" from the "tool_lifecycle-choose-trigger" singleselect
    And I set the following fields to these values:
      | Instance name              | Reuse course                       |
      | Action name                | Reuse course                       |
      | Capability                 | moodle/course:manageactivities     |
    And I press "Save changes"
    And I select "Enhanced duplicate step" from the "tool_lifecycle-choose-step" singleselect
    And I set the field "Instance name" to "Duplicate 2 step"

  @javascript
  Scenario Outline: Duplicate course and move it to another course category
    And I set the following fields to these values:
      | Move to different course category          | <move>       |
      | Consider parent category                   | <parents>    |
      | Do not create top level category on target | <notoplevel> |
      | Target course category                     | <target>     |
      | Maximum levels of source categories        | <depth>     |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Reuse course" in the "<course>" row of the "tool_lifecycle_remaining" table
    And I set the field "Course short name" to "reused_arch_course"
    And I set the field "Course full name" to "Reused ArchCourse"
    And I press "Save changes"
    When I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And the category for "<course>" is "<oldpath>"
    And the category for "Reused ArchCourse" is "<newpath>"
    Examples:


      | course       | oldpath        | target      | newpath              | move | parents | notoplevel | depth |
      | Course C | /CAT A/CAT B/CAT C | Archive     | /Archive/CAT A/CAT B/CAT C | 1  | 1   | 0          |       |
      | Course C | /CAT A/CAT B/CAT C | Archive     | /Archive/CAT A/CAT B | 1    | 1       | 0          | 2     |
      | Course C | /CAT A/CAT B/CAT C | Archive     | /Archive/CAT B/CAT C | 1    | 1       | 1          | 2     |
      | ArchCourse 1 | /Archive/CAT 1 | CAT A       | /CAT A/CAT 1         | 1    | 1       | 1          |       |
      | ArchCourse 1 | /Archive/CAT 1 | (Top level) | /CAT 1               | 1    | 1       | 1          |       |
      | ArchCourseX  | /Archive       | (Top level) | /                    | 1    | 1       | 1          |       |
      | ArchCourse 1 | /Archive/CAT 1 | CAT A       | /CAT A/Archive/CAT 1 | 1    | 1       | 0          | 0     |
      | ArchCourse 1 | /Archive/CAT 1 | (Top level) | /Archive/CAT 1       | 1    | 1       | 0          | 0     |
      | ArchCourseX  | /Archive       | (Top level) | /Archive             | 1    | 1       | 0          | 0     |

  @javascript
  Scenario Outline: Duplicate course and move it to without hierarchy
    And I set the following fields to these values:
      | Move to different course category          | <move>       |
      | Consider parent category                   | <parents>    |
      | Target course category                     | <target>     |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Reuse course" in the "<course>" row of the "tool_lifecycle_remaining" table
    And I set the field "Course short name" to "reused_arch_course"
    And I set the field "Course full name" to "Reused ArchCourse"
    And I press "Save changes"
    When I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And the category for "<course>" is "<oldpath>"
    And the category for "Reused ArchCourse" is "<newpath>"
    Examples:


      | course       | oldpath        | target      | newpath              | move | parents |
      | ArchCourse 1 | /Archive/CAT 1 | CAT A       | /CAT A               | 1    | 0       |

  @javascript
  Scenario Outline: Duplicate course and keep on old location
    And I set the following fields to these values:
      | Move to different course category          | <move>       |
    And I press "Save changes"
    And I am on workflowdrafts page
    And I press "Activate"
    And I log out

    And I log in as "teacher1"
    And I am on lifecycle view
    And I click on the tool "Reuse course" in the "<course>" row of the "tool_lifecycle_remaining" table
    And I set the field "Course short name" to "reused_arch_course"
    And I set the field "Course full name" to "Reused ArchCourse"
    And I press "Save changes"
    When I log out

    And I log in as "admin"
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And I run the scheduled task "tool_lifecycle\task\lifecycle_task"
    And I wait "2" seconds
    And the category for "<course>" is "<oldpath>"
    And the category for "Reused ArchCourse" is "<newpath>"
    Examples:
      | course       | oldpath        | newpath              | move |
      | ArchCourse 1 | /Archive/CAT 1 | /Archive/CAT 1       | 0    |
