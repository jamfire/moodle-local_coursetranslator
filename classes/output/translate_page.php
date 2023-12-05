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
    private object $course;
    private array $coursedata;
    private object $mlangfilter;
    /**
     * @var array|false|float|int|mixed|string|null
     */
    private mixed $current_lang;
    /**
     * @var array|mixed
     */
    private mixed $langs;
    private \local_coursetranslator\output\translate_form $mform;

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
            'lang' => $this->current_lang
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
            array_push($langs, array(
                'code' => $key,
                'lang' => $lang,
                'selected' => $this->current_lang === $key ? "selected" : ""
            ));
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

        foreach ($this->coursedata as $section){
            // count for each section's headers
            foreach ($section['section'] as $s){
                $this->computeWordcount($s->text, $wordcount, $spaces, $charcountspaces);
            }
            // count for each section's activites
            foreach ($section['activities'] as $a){
                $this->computeWordcount($a->text, $wordcount, $spaces, $charcountspaces);
            }
        }
        // Set word and character counts to data.
        $data->wordcount = $wordcount;
        $data->charcountspaces = $charcountspaces;
        $data->charcount = $charcountspaces - $spaces;

        return $data;
    }
    /**
     * compute word, spaces and character's count for a single text
     * @param $text
     * @param $wc Wordcount ref
     * @param $sc Spaces count ref
     * @param $csc Char count including sapecs ref
     * @return void
     */
    private function computeWordcount($text, &$wc, &$sc, &$csc){
        $tagsStriped = strip_tags($text);
        // Get the wordcount.
        $wc = $wc + str_word_count($tagsStriped);
        // Get the character count with spaces.
        $csc = $csc + strlen($tagsStriped);
        // Get the character count without spaces.
        $sc = $sc + array_key_last(preg_split('/\s+/', $tagsStriped));
    }
}
