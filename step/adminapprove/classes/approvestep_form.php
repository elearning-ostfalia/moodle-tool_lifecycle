<?php

namespace lifecyclestep_adminapprove;

use core\output\html_writer;
use moodle_url;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;

/**
 * form for approve step actions
 */
class approvestep_form extends \moodleform
{
    private $stepid;
    private $courseid;
    private $category;
    private $coursename;
    private $pagesize;

    public function __construct($stepid, $courseid, $category, $coursename, $pagesize = 0)
    {
        $this->stepid = $stepid;
        $this->courseid = $courseid;
        $this->category = $category;
        $this->coursename = $coursename;
        $this->pagesize = $pagesize;
        parent::__construct();
    }
    /**
     * @inheritDoc
     */
    protected function definition()
    {
        global $PAGE;
        $mform = $this->_form;

        // Create table with optional link for displaying all records.
        $table = new decision_table($this->stepid, $this->courseid, $this->category, $this->coursename);
        $table->define_baseurl($PAGE->url);
        // $table->set_default_per_page(30);

        // Put the table with input elements into the form so that we get the selection on submission.
        ob_start();
        $table->out($this->pagesize, false);
        $output = ob_get_contents();
        ob_end_clean();

        $mform->addElement('html', $output);

        $displaylist = [];

        $rollbackcustlabel =
            settings_manager::get_settings($this->stepid, settings_type::STEP)['rollbackbuttonlabel'] ?? null;
        $rollbackcustlabel = !empty($rollbackcustlabel) ?
            $rollbackcustlabel : get_string('rollback', 'lifecyclestep_adminapprove');

        $proceedcustlabel =
            settings_manager::get_settings($this->stepid, settings_type::STEP)['proceedbuttonlabel'] ?? null;
        $proceedcustlabel = !empty($proceedcustlabel) ?
            $proceedcustlabel : get_string('proceed', 'lifecyclestep_adminapprove');

        $params = ['action' => 'proceed', 'stepid' => $this->stepid, 'sesskey' => sesskey()];
        $url = new moodle_url($PAGE->url, $params);
        $displaylist[$url->out(false)] = $proceedcustlabel;

        $params = ['action' => 'rollback', 'stepid' => $this->stepid, 'sesskey' => sesskey()];
        $url = new moodle_url($PAGE->url, $params);
        $displaylist[$url->out(false)] = $rollbackcustlabel;


        $label = html_writer::tag('label', get_string('withselectedcourses', 'lifecyclestep_adminapprove'),
            ['for' => 'formactionid', 'class' => 'col-form-label d-inline']);
        // Create element for action on selected records.
        $selectactionparams = [
            'id' => 'formactionid',
            // window.onbeforeunload = null: suppress warning about unchanged data
            // this.form.submit(): force submit by selecting an action
            // this.form.action=this.value: set action url to selected url
            'onchange' => "window.onbeforeunload = null;this.form.action=this.value;this.form.submit();",
            'data-action' => 'toggle',
            'data-togglegroup' => 'lifecycle-adminapprove-table',
            'data-toggle' => 'action',
            'disabled' => true
        ];
        $select = html_writer::select($displaylist, 'formaction', '', ['' => 'choosedots'], $selectactionparams);

        $a = html_writer::div($label . $select);
        $c = html_writer::div($a, 'btn-group');
        $d = html_writer::div($c, 'form-inline');
        $mform->addElement('html', html_writer::div($d, 'buttons'));
    }
}