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
 * @todo MDL-0 should use Mustache templating rather than extending a form as communication is done with JS...
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
            $mform->addElement('html', "<div class='row bg-light p-2'><h3 class='text-center'>Module $sectioncount</h3></div>");
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

        // Column 1 settings.
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
        $titlestring = htmlentities($titlestring);
        // Thew little badge showing the status of the translations.
        $bulletstatus = "<span id='previousTranslationStatus' title='$titlestring'
                    class='badge badge-pill $buttonclass'>&nbsp;</span>";
        // The checkbox to select items for batch actions.
        $checkbox = "<input title='$titlestring' type='checkbox' data-key='$key'
            class='mx-2'
            data-action='local-coursetranslator/checkbox'
            disabled/>";
        // Multilanguage tag.
        // Invisible when nothing translated already.
        // Can be as bootstrap info when there is a multilang tag in the source.
        // Will be a danger tag if the content has already an OTHER and the TARGET language tag.
        $hasotherandsourcetag = $this->check_field_has_other_and_sourcetag(trim($item->text));
        $alreadyhasmultilang = $this->has_multilang(trim($item->text));
        $visibilityclass = $alreadyhasmultilang ? '' : 'invisible';
        $badgeclass = $hasotherandsourcetag ? 'danger' : 'info';
        $titlestring = $hasotherandsourcetag ?
                get_string('t_warningsource', 'local_coursetranslator',
                        strtoupper($this->currentlang)) :
                get_string('t_viewsource', 'local_coursetranslator');
        $mutlilangspantag =
                "<span
                    title='$titlestring'
                    id='toggleMultilang'
                    aria-controls='$keyid'
                    role='button'
                    class='ml-1 btn btn-sm btn-outline-$badgeclass $visibilityclass'>
                    <i class='fa fa-language' aria-hidden='true'></i></span>";

        // Column 1 layout.
        $mform->addElement('html', '<div class="col-1 px-1">');
        $mform->addElement('html', $bulletstatus);

        $mform->addElement('html', $checkbox);

        $mform->addElement('html', '</div>');
        // Column 2 settings.
        // Edit button.
        $editbuttontitle = get_string('t_edit', 'local_coursetranslator');
        $editinplacebutton = "<a class='p-1 btn btn-sm btn-outline-info'
                        id='local-coursetranslator__sourcelink' href='{$item->link}' target='_blank'
                            title='$editbuttontitle'>
                            <i class='fa fa-pencil' aria-hidden='true'></i>
                        </a>";
        // Source Text.
        $sourcetextdiv = "<div class='col-5 px-0 pr-5 local-coursetranslator__source-text' data-key='$key'>";

        // Source text textarea.
        $rawsourcetext = htmlentities($mlangfilter->filter($item->text));
        $mlangfiltered = $mlangfilter->filter($item->displaytext);
        $sourcetextarea = "<div class='collapse show' data-sourcetext-key='$key'
                data-sourcetext-raw='$rawsourcetext'>$mlangfiltered</div>";
        // Collapsible multilang textarea.
        $trimedtext = trim($item->text);
        $multilangtextarea = "<div class='collapse' id='$keyid'>";
        $multilangtextarea .= "<div data-key='$key'
            data-action='local-coursetranslator/textarea'>$trimedtext</div>";
        $multilangtextarea .= '</div>';
        // Column 2 layout.
        $mform->addElement('html', $sourcetextdiv);
        $mform->addElement('html', $editinplacebutton);
        $mform->addElement('html', $mutlilangspantag);
        $mform->addElement('html', $sourcetextarea);
        $mform->addElement('html', $multilangtextarea);

        // Closing sourcetext div.
        $mform->addElement('html', '</div>');

        // Column 3 settings.
        // Translation Input div.
        $translatededitor = "<div
            class='col-5 px-0 local-coursetranslator__translation'
            data-action='local-coursetranslator/editor'
            data-key='$key'
            data-table='{$item->table}'
            data-id='{$item->id}'
            data-field='{$item->field}'
            data-tid='{$item->tid}'>";
        // No wisiwig editor text fields.
        $nowisiwig = "<div
                class='format-{$item->format} border py-2 px-3'
                contenteditable='true'
                data-format='{$item->format}'></div>";
        // Status icon/button.
        $savetogglebtn = "<span class='btn-outline-secondary disabled' data-status='local-coursetranslator/wait'
                role='status' aria-disabled='true'><i class='fa'
                ></i></span>";
        // Status surrounding div.
        $statusdiv = "<div class='col-1 align-content-center'
            data-key-validator='$key'>$savetogglebtn</div>";
        // Column 3 Layout.
        $mform->addElement('html', $translatededitor);
        // Plain text input.
        if ($item->format === 0) {
            $mform->addElement('html', $nowisiwig);
        }
        // HTML input.
        if ($item->format === 1) {
            $mform->addElement('cteditor', $key, $key);
            $mform->setType($key, PARAM_RAW);
        }
        // Closing $translatededitor.
        $mform->addElement('html', '</div>');
        // Adding validator btn.
        $mform->addElement('html', $statusdiv);
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
    private function check_field_has_other_and_sourcetag(string $t): bool {
        return str_contains($t, '{mlang other}') && str_contains($t, "{mlang $this->currentlang}");
    }

    /**
     * As the title says.
     *
     * @param string $t
     * @return bool
     */
    private function has_multilang(string $t): bool {
        return str_contains($t, '{mlang}');
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
