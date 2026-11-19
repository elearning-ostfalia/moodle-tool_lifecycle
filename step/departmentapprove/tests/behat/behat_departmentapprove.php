<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat extensions for lifecyclestep_departmentapprove
 *
 * @package    lifecyclestep_departmentapprove
 * @copyright  2026 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Moodle\BehatExtension\Exception\SkippedException;

require_once(__DIR__ . '/../../../../../../../lib/behat/behat_base.php');

class behat_departmentapprove extends behat_base {

    /**
     * open link as department person
     *
     * @When I visit department approval link
     * @param
     */
    public function i_visit_department_approval_link() {
        global $DB;
        $records = $DB->get_records('lifecyclestep_departmentapprove');
        if (!$records) {
            throw new ExpectationException('No approval record found', $this->getSession());
        }
        $record = reset($records);
        $token = $record->confirmtoken;

        $this->execute('behat_general::i_visit', '/admin/tool/lifecycle/step/departmentapprove/department_confirm.php?token=' . $token);
    }

    /**
     * Simulate that the response deadline of all pending department approvals has expired.
     *
     * @Given /^the department approval deadline has expired$/
     */
    public function the_department_approval_deadline_has_expired() {
        global $DB;
        if (!$DB->record_exists('lifecyclestep_departmentapprove', [])) {
            throw new ExpectationException('No approval record found', $this->getSession());
        }
        $DB->set_field('lifecyclestep_departmentapprove', 'confirmtokenexpires', time() - 1);
    }

    /**
     * Check that the response deadline of all pending department approvals lies in the future
     * (i.e. a new deadline has been set after a reminder).
     *
     * @Then /^the department approval deadline should be in the future$/
     */
    public function the_department_approval_deadline_should_be_in_the_future() {
        global $DB;
        $records = $DB->get_records('lifecyclestep_departmentapprove');
        if (!$records) {
            throw new ExpectationException('No approval record found', $this->getSession());
        }
        foreach ($records as $record) {
            if ($record->confirmtokenexpires <= time()) {
                throw new ExpectationException('Deadline ' . userdate($record->confirmtokenexpires) .
                    ' is not in the future', $this->getSession());
            }
        }
    }

    /**
     * Count the emails sent to a user (email address or username) with a subject containing the given text.
     * Requires an email catcher (TEST_EMAILCATCHER_MAIL_SERVER / TEST_EMAILCATCHER_API_SERVER),
     * otherwise the scenario is skipped.
     *
     * @Then /^user "(?P<user_string>(?:[^"]|\\")*)" should have (?P<count_number>\d+) emails? with subject containing "(?P<subject_string>(?:[^"]|\\")*)"$/
     * @param string $user email address or username
     * @param int $expected expected number of emails
     * @param string $subject part of the subject
     */
    public function user_should_have_emails_with_subject_containing(string $user, int $expected, string $subject) {
        if (!defined('TEST_EMAILCATCHER_MAIL_SERVER') || !defined('TEST_EMAILCATCHER_API_SERVER')) {
            throw new SkippedException(
                'The TEST_EMAILCATCHER_MAIL_SERVER and TEST_EMAILCATCHER_API_SERVER constants must be ' .
                'defined in config.php to use the mailcatcher steps.');
        }

        $address = $user;
        if (strpos($user, '@') === false) {
            $userrecord = $this->get_user_by_identifier($user);
            if (!$userrecord) {
                throw new ExpectationException("No user found with identifier {$user}", $this->getSession());
            }
            $address = $userrecord->email;
        }

        $catcher = new \core\test\mailpit_email_catcher(TEST_EMAILCATCHER_API_SERVER);
        $count = 0;
        foreach ($catcher->get_messages() as $message) {
            if ($message->has_recipient($address) && strpos($message->get_subject(), $subject) !== false) {
                $count++;
            }
        }
        if ($count !== $expected) {
            throw new ExpectationException(
                sprintf('Expected %d emails to %s with subject containing "%s", but found %d',
                    $expected, $address, $subject, $count),
                $this->getSession());
        }
    }

}
