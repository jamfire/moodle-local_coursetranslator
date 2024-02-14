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
defined('MOODLE_INTERNAL') || die();
global $CFG;

use context_system;
use moodleform;
use MoodleQuickForm;
use stdClass;

// Load the files we're going to need.

require_once("$CFG->libdir/form/editor.php");
require_once("$CFG->dirroot/local/coursetranslator/classes/editor/MoodleQuickForm_cteditor.php");

/**
 * Translate Form Output
 *
 * @todo should use Mustache templating rather than extending a form as communication is done with JS...
 * Provides output class for /local/coursetranslator/translate.php
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_form extends moodleform {
    /**
     * Target language
     *
     * @var String
     */
    private $targetlang;
    /**
     * Source language
     *
     * @var String
     */
    private $currentlang;

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
        $this->targetlang = $this->_customdata['target_lang'];
        $this->currentlang = $this->_customdata['current_lang'];

        // Start moodle form.
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        MoodleQuickForm::registerElementType('cteditor', "$CFG->libdir/form/editor.php",
                '\local_coursetranslator\editor\MoodleQuickForm_cteditor');

        // Open Form.
        $mform->addElement('html', '<div class="container-fluid local-coursetranslator__form">');

        // Loop through course data to build form.
        $sectioncount = 1;
        foreach ($coursedata as $section) {
            // Loop section's headers.
            $mform->addElement('html', "<div class='row bg-light py-2'><h3 class='text-center'>Module $sectioncount</h3></div>");
            foreach ($section['section'] as $s) {
                $this->get_formrow($mform, $s);
            }
            // Loop section's activites.
            foreach ($section['activities'] as $a) {
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
     * @param MoodleQuickForm $mform
     * @param stdClass $item
     * @param string $cssclass
     * @return void
     * @throws \coding_exception
     */
    private function get_formrow(MoodleQuickForm $mform, stdClass $item, string $cssclass = "") {

        // Get mlangfilter to filter text.
        $mlangfilter = $this->_customdata['mlangfilter'];

        // Build a key for js interaction.
        $key = "$item->table[$item->id][$item->field]";
        $keyid = "{$item->table}-{$item->id}-{$item->field}";
        // Data status.
        $status = $item->tneeded ? 'needsupdate' : 'updated';

        // Open translation item.
        $mform->addElement('html',
                "<div class='$cssclass row align-items-start border-bottom py-2' data-row-id='$key' data-status='$status'>");

        // First column.
        if ($this->targetlang === $this->currentlang) {
            $buttonclass = 'badge-dark';
            $titlestring = get_string('t_canttranslate', 'local_coursetranslator', $this->targetlang);
        } else if ($item->tneeded) {
            if (str_contains($item->text, "{mlang " . $this->targetlang)) {
                $buttonclass = 'badge-warning';
                $titlestring = get_string('t_needsupdate', 'local_coursetranslator');
            } else {
                $buttonclass = 'badge-danger';
                $titlestring = get_string('t_nevertranslated', 'local_coursetranslator', $this->targetlang);
            }

        } else {
            $buttonclass = 'badge-success';
            $titlestring = get_string('t_uptodate', 'local_coursetranslator');
        }
        $mform->addElement('html', '<div class="col-1 px-1">');
        $mform->addElement('html', '<span title="' . $titlestring . '" class="badge badge-pill ' . $buttonclass .
                '" style="font-size:.6rem;top:.3rem;left:-1rem;position:absolute;">&nbsp;</span>');
        $mform->addElement('html', '<div class="form-check">');

        $mform->addElement('html', '<input
            class="form-check-input"
            title="' . $titlestring . '"
            data-action="local-coursetranslator/checkbox"
            type="checkbox"
            data-key="' . $key . '"
            disabled
        />');
        $warning = $this->check_filed_has_other_and_sourcetag(trim($item->text)) ?
                '<i title="' . get_string('t_warningsource', 'local_coursetranslator', $this->currentlang) . '"
                    class="fa fa-warning" aria-hidden="true" data-status="sourceTextWarings" ></i>' : "";
        $mform->addElement('html', '<span
                    title="' . get_string('t_viewsource', 'local_coursetranslator') . '"
                    id="toggleMultilang"
                    aria-controls="' . $keyid . '"
                    role="button">
                       <i class="fa fa-language px-10" aria-hidden="true"></i>
                    </span>&nbsp;' . $warning);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        // Source Text.
        $mform->addElement('html', '<div class="col-5 px-0 pr-5 local-coursetranslator__source-text" data-key="' . $key . '">');
        // Edit button.
        $mform->addElement('html', '<span class="col-1 px-0 ">
                        <a style="top:.4rem;left:-2rem;position:absolute;" href="' . $item->link . '" target="_blank" title="' .
                get_string('t_edit', 'local_coursetranslator') . '">
                            <i class="fa fa-pencil-square-o px-2" aria-hidden="true"></i>
                        </a>
                     </span>');
        // Text editor.
        $mform->addElement('html', '<div class="collapse show" data-sourcetext-key="' . $key . '"
                data-sourcetext-raw="' . htmlentities($mlangfilter->filter($item->text)) . '">' .
                $mlangfilter->filter($item->displaytext) . ' </div>');

        $mform->addElement('html', '<div class="collapse" id="' . $keyid . '">');
        $mform->addElement('html', '<div
            data-key="' . $key . '"
            data-action="local-coursetranslator/textarea"
            >' . trim($item->text) . '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        // Translation Input.
        $mform->addElement('html', '<div
            class="col-5 px-0 local-coursetranslator__translation"
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
        // Adding validator btn.
        $savetogglebtn =
                '<i class="col-1 align-content-center fa mr-1"
                data-status="local-coursetranslator/wait" data-toggle="" data-validate-' .
                $keyid . ' role="status"></i>';

        $mform->addElement('html', '<div class="col-1 align-content-center"
            data-key-validator="' . $key . '">' . $savetogglebtn . '</div>');
        // Close translation item.
        $mform->addElement('html', '</div>');
    }

    /**
     * Checks if the multilang tag OTHER and the current/source language is already there to warn the user that the tags will be
     * overridden and deleted.
     *
     * @param string $t
     * @return bool
     */
    function check_filed_has_other_and_sourcetag(string $t) {
        return str_contains($t, '{mlang other}') && str_contains($t, "{mlang $this->currentlang}");
    }

    /**
     * Process data
     *
     * @param stdClass $data
     * @return void
     */
    public function process(stdClass $data) {

    }

    /**
     * Specificy Translation Access
     *
     * @return void
     */
    public function require_access() {
        require_capability('local/multilingual:edittranslations', context_system::instance());
    }
}
