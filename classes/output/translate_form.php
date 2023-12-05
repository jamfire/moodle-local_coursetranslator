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

namespace local_coursetranslator\output;

use moodleform;

// Load the files we're going to need.
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/form/editor.php");
require_once("$CFG->dirroot/local/coursetranslator/classes/editor/MoodleQuickForm_cteditor.php");

/**
 * Translate Form Output
 *
 * Provides output class for /local/coursetranslator/translate.php
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_form extends moodleform {
    private mixed $lang;

    /**
     * Define Moodle Form
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        // Get course data.
        $course = $this->_customdata['course'];
        $coursedata = $this->_customdata['coursedata'];
        $this->lang = $this->_customdata['lang'];

        // Start moodle form.
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        \MoodleQuickForm::registerElementType(
            'cteditor',
            "$CFG->libdir/form/editor.php",
            '\local_coursetranslator\editor\MoodleQuickForm_cteditor'
        );

        // Open Form.
        $mform->addElement('html', '<div class="container-fluid local-coursetranslator__form">');

        // Loop through course data to build form.
        $sectioncount = 1;
        foreach ($coursedata as $section){
            // Loop section's headers
            $mform->addElement('html', "<h3>Section $sectioncount</h3>");
            //$mform->addElement('html', "<em>$sectioncount</em>");
            foreach ($section['section'] as $s){
                $this->get_formrow($mform, $s);
            }
            // loop section's activites
            foreach ($section['activities'] as $a){

                $this->get_formrow($mform, $a);
            }
            $sectioncount++;
        }

        // Close form.
        $mform->addElement('html', '</div>');
    }

    /**
     * Generate Form Row
     *
     * @param \MoodleQuickForm $mform
     * @param \stdClass $item
     * @return void
     */
    private function get_formrow(\MoodleQuickForm $mform, \stdClass $item) {

        // Get mlangfilter to filter text.
        $mlangfilter = $this->_customdata['mlangfilter'];

        // Build a key for js interaction.
        $key = "$item->table[$item->id][$item->field]";
        $keyid = "{$item->table}-{$item->id}-{$item->field}";
        /**
         * @todo check the need update status
         */
        // Data status.
        $status = $item->tneeded ? 'needsupdate' : 'updated';

        // Open translation item.
        $mform->addElement(
            'html',
            '<div class="row align-items-start border-bottom py-3" data-row-id="' . $key . '" data-status="' . $status . '">'
        );

        // First column.
        $mform->addElement('html', '<div class="col-2">');
        $mform->addElement('html', '<div class="form-check">');
        $mform->addElement('html', '<input
            class="form-check-input"
            data-action="local-coursetranslator/checkbox"
            type="checkbox"
            data-key="' . $key . '"
            disabled
        />');
        $label = '<label class="form-check-label">';
        if ($item->tneeded) {
            $label .= ' <span class="badge badge-pill badge-danger rounded py-1" data-status-key="' . $key . '">'
                    . get_string('t_needsupdate', 'local_coursetranslator')
                    . '</span>';
        } else {
            $label .= ' <span class="badge badge-pill badge-success rounded py-1" data-status-key="' . $key . '">'
                    . get_string('t_uptodate', 'local_coursetranslator')
                    . '</span>';
        }
        $label .= '</label>';
        $label .= '<a href="' . $item->link . '" target="_blank" title="' . get_string('t_edit', 'local_coursetranslator') . '">';
        $label .= '<i class="fa fa-pencil-square-o mr-1" aria-hidden="true"></i>';
        $label .= '</a>';
        $label .= '<a data-toggle="collapse" title="' . get_string('t_viewsource', 'local_coursetranslator') . '" href="#'
            . $keyid . '" role="button" aria-expanded="false" aria-controls="'
            . $keyid . '"><i class="fa fa-code" aria-hidden="true"></i></a>';
        $mform->addElement('html', $label);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        // Source Text.
        $mform->addElement('html', '<div
            class="col-5 px-0 pr-5 local-coursetranslator__source-text"
            data-key="' . $key . '"
        >');
        $mform->addElement('html', '<div data-sourcetext-key="' . $key . '">' . $mlangfilter->filter($item->text) . '</div>');
        $mform->addElement('html', '<div>');

        $mform->addElement('html', '<div class="collapse" id="' . $keyid . '">');
        /**
         * @todo display source and translated text as tabs
         */
        $mform->addElement('html','<div 
            data-key="' . $key . '" 
            class="mt-3 card card-body"
            data-action="local-coursetranslator/textarea"
            >'
            . trim($item->text) . '</div>'
        );
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        // Translation Input.
        $mform->addElement('html', '<div
            class="col-4 px-0 local-coursetranslator__translation"
            data-action="local-coursetranslator/editor"
            data-key="' . $key . '"
            data-table="' . $item->table . '"
            data-id="' . $item->id . '"
            data-field="' . $item->field . '"
            data-tid="' . $item->tid . '"
        >');
        // Plain text input.
        if ($item->format === 0) {
            $mform->addElement('html', '<div
                class="format-' . $item->format . ' border py-2 px-3"
                contenteditable="true"
                data-format="' . $item->format . '"
            ></div>');
        }
        // HTML input.
        if ($item->format === 1) {
            $mform->addElement('cteditor', $key, $key);
            $mform->setType($key, PARAM_RAW);
        }


        $mform->addElement('html', '</div>');
        // adding validator btn
        $saveToggleBtn = '
            <i class="col-1 align-content-center fa fa-floppy-o mr-1" data-toggle="" data-validate-'.$keyid.' ></i>
        ';
       /* $saveToggleBtn = '<input type="checkbox" checked
            data-toggle="toggle" 
            data-on="Ready" 
            data-off="Not Ready" 
            data-onstyle="success" 
            data-offstyle="danger">';
*/
        $mform->addElement('html','<div class="col-1 align-content-center"
            data-key-validator="' . $key . '">'.
               $saveToggleBtn
                . '</div>'
        );
        // Close translation item.
        $mform->addElement('html', '</div>');

    }

    /**
     * Process data
     *
     * @param \stdClass $data
     * @return void
     */
    public function process(\stdClass $data) {

    }

    /**
     * Specificy Translation Access
     *
     * @return void
     */
    public function require_access() {
        require_capability('local/multilingual:edittranslations', \context_system::instance()->id);
    }
}
