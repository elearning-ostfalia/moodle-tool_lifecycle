<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Interaction lib for querying course details from the user.
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use lifecyclestep_duplicate2\form_duplicate2;
use tool_lifecycle\local\entity\process;
use tool_lifecycle\local\entity\step_subplugin;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\local\response\step_interactive_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../interactionlib.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/../duplicate/interactionlib.php');

require_once(__DIR__ . '/classes/form_duplicate2.php');

/**
 * Interaction lib for querying course details from the user.
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class interactionduplicate2 extends interactionduplicate {
    /**
     * Called when a user triggered an action for a process instance.
     * @param process $process instance of the process the action was triggered upon.
     * @param step_subplugin $step instance of the step the process is currently in.
     * @param string $action action string
     * @return step_interactive_response defines if the step still wants to process this course
     *      - proceed: the step has finished and respective controller class can take over.
     *      - stillprocessing: the step still wants to process the course and is responsible for rendering the site.
     *      - noaction: the action is not defined for the step.
     *      - rollback: the step has finished and respective controller class should rollback the process.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function handle_interaction($process, $step, $action = 'default') {
        global $PAGE, $DB;
        $form = new form_duplicate2($PAGE->url, $process->id, $step->id);
        if ($form->is_cancelled()) {
            return step_interactive_response::rollback();
        }
        if ($data = $form->get_submitted_data()) {
            if ($foundcourses = $DB->get_records('course', ['shortname' => $data->shortname])) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }

                $foundcoursenamestring = implode(',', $foundcoursenames);
                \core\notification::add(get_string('shortnametaken', '', $foundcoursenamestring));
                $this->render_form($form);
                return step_interactive_response::still_processing();
            }
            process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSESHORTNAME, $data->shortname);
            process_data_manager::set_process_data($process->id, $step->id, duplicate::PROC_DATA_COURSEFULLNAME, $data->fullname);
            return step_interactive_response::no_action();
        }
        if ($action == self::ACTION_DUPLICATE_FORM || $action == 'default') {
            $this->render_form($form);
            return step_interactive_response::still_processing();
        }
        return step_interactive_response::no_action();
    }

    /**
     * Renders the duplication form including respective headers and footers.
     * @param \moodleform $mform Form to be rendered.
     */
    private function render_form($mform) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('tool_lifecycle');

        echo $renderer->header();
        $mform->display();
        echo $renderer->footer();
    }

    /**
     * Returns the display name for the given action.
     * Used for the past actions table in view.php.
     * @param string $action Identifier of action
     * @param string $user Html-link with username as text that refers to the user profile.
     * @return string action display name
     * @throws \coding_exception
     */
    public function get_action_string($action, $user) {
        return "";
    }
}
