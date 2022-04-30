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
        foreach ($coursedata as $item) {
            $this->get_formrow($mform, $item);
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

        // Open translation item.
        $mform->addElement('html', '<div class="row align-items-start border-bottom py-3" data-row-id="' . $key . '">');

        // First column.
        $mform->addElement('html', '<div class="col-2">');
        $mform->addElement('html', '<div class="form-check">');
        $mform->addElement('html', '<input
            class="form-check-input local-coursetranslator__checkbox"
            type="checkbox"
            data-key="' . $key . '"
            disabled
        />');
        $label = '<label class="form-check-lable">';
        if ($item->tneeded) {
            $label .= ' <span class="badge badge-pill badge-danger rounded py-2" data-status-key="' . $key . '">'
                    . get_string('t_needsupdate', 'local_coursetranslator')
                    . '</span>';
        } else {
            $label .= ' <span class="badge badge-pill badge-success rounded py-2" data-status-key="' . $key . '">'
                    . get_string('t_uptodate', 'local_coursetranslator')
                    . '</span>';
        }
        $label .= '</label>';
        $mform->addElement('html', $label);
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        // Source Text.
        $mform->addElement('html', '<div
            class="col-5 px-0 pr-5 local-coursetranslator__source-text"
            data-key="' . $key . '"
        >');
        $mform->addElement('html', '<div data-sourcetext-key="' . $key . '">' . $mlangfilter->filter($item->text) . '</div>');
        $mform->addElement('html', '</div>');

        // Translation Input.
        $mform->addElement('html', '<div
            class="col-5 px-0 local-coursetranslator__translation local-coursetranslator__editor"
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

        $mform->addElement('html', '<div class="d-none col-2 px-0"></div>');
        $mform->addElement(
            'html',
            '<div data-key="' . $key
            . '" class="d-none col-10 px-0 py-5 local-coursetranslator__textarea">'
            . trim($item->text) . '</div>'
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
