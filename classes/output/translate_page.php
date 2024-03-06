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

use renderable;
use renderer_base;
use templatable;
use stdClass;
use local_coursetranslator\output\translate_form;

/**
 * Translate Page Output
 *
 * Provides output class for /local/coursetranslator/translate.php
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_page implements renderable, templatable {
    /**
     * Constructor
     *
     * @param object $course Moodle course record
     * @param array  $coursedata Custom processed course record
     * @param object $mlangfilter Multilang2 Filter for filtering output
     */
    public function __construct($course, $coursedata, $mlangfilter) {
        $this->course = $course;
        $this->coursedata = $coursedata;
        $this->langs = get_string_manager()->get_list_of_translations();
        $this->langs['other'] = get_string('t_other', 'local_coursetranslator');
        $this->current_lang = optional_param('course_lang', 'other', PARAM_NOTAGS);
        $this->mlangfilter = $mlangfilter;

        // Moodle Form.
        $mform = new translate_form(null, [
            'course' => $course,
            'coursedata' => $coursedata,
            'mlangfilter' => $mlangfilter,
            'lang' => $this->current_lang,
        ]);

        $this->mform = $mform;
    }

    /**
     * Export Data to Template
     *
     * @param renderer_base $output
     * @return object
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $langs = [];
        // Process langs.
        foreach ($this->langs as $key => $lang) {
            array_push($langs, [
                'code' => $key,
                'lang' => $lang,
                'selected' => $this->current_lang === $key ? "selected" : "",
            ]);
        }

        // Data for mustache template.
        $data->course = $this->course;
        $data->langs = $langs;
        $data->lang = $this->langs[$this->current_lang];

        // Hacky fix but the only way to adjust html...
        // This could be overridden in css and I might look at that fix for the future.
        $renderedform = $this->mform->render();
        $renderedform = str_replace('col-md-3 col-form-label d-flex pb-0 pr-md-0', 'd-none', $renderedform);
        $renderedform = str_replace('class="col-md-9 form-inline align-items-start felement"', '', $renderedform);
        $data->mform = $renderedform;

        // Get word and character counts.
        $wordcount = 0;
        $charcountspaces = 0;
        $spaces = 0;
        foreach ($this->coursedata as $item) {
            $text = $this->mlangfilter->filter($item->text);

            // Get the wordcount.
            $wcwords = strip_tags($text);
            $wordcount = $wordcount + str_word_count($wcwords);

            // Get the character count with spaces.
            $cswords = strip_tags($text);
            $charcountspaces = $charcountspaces + strlen($cswords);

            // Get the character count without spaces.
            $ccwords = strip_tags($text);
            $spaces = $spaces + array_key_last(preg_split('/\s+/', $ccwords));
        }

        // Set word and character counts to data.
        $data->wordcount = $wordcount;
        $data->charcountspaces = $charcountspaces;
        $data->charcount = $charcountspaces - $spaces;

        return $data;
    }
}
