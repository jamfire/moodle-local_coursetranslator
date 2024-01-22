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
use stdClass;
use templatable;

/**
 * Translate Page Output
 *
 * Provides output class for /local/coursetranslator/translate.php
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_page implements renderable, templatable {
    private object $course;
    private array $coursedata;
    private object $mlangfilter;
    /** @var String */
    private mixed $current_lang;
    /** @var String */
    private mixed $target_lang;
    /**
     * @var array|mixed
     */
    private mixed $langs;
    private \local_coursetranslator\output\translate_form $mform;

    /**
     * Constructor
     *
     * @param object $course Moodle course record
     * @param array $coursedata Custom processed course record
     * @param object $mlangfilter Multilang2 Filter for filtering output
     */
    public function __construct($course, $coursedata, $mlangfilter) {
        $this->course = $course;
        $this->coursedata = $coursedata;
        $this->langs = get_string_manager()->get_list_of_translations();
        /**
         * @todo source lang should be identified and fixed to OTHER
         * if source lang is changed for a course than the whole translation should be void
         */
        $this->current_lang = optional_param('lang', current_language(), PARAM_NOTAGS);
        $this->target_lang = optional_param('target_lang', 'en', PARAM_NOTAGS);
        $this->mlangfilter = $mlangfilter;
        /** @todo no need form if treatment and api call is done by js. Replace by Mustache */
        // Moodle Form.
        $mform = new translate_form(null, [
                'course' => $course,
                'coursedata' => $coursedata,
                'mlangfilter' => $mlangfilter,
                'current_lang' => $this->current_lang,
                'target_lang' => $this->target_lang

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
        $target_langs = [];
        // Process langs.
        foreach ($this->langs as $key => $lang) {
            $disabletarget = $this->current_lang === $key ||
                    !in_array($key, explode(',', get_string('supported_languages', 'local_coursetranslator')));
            $disablesource = $this->target_lang === $key ||
                    !in_array($key, explode(',', get_string('supported_languages', 'local_coursetranslator')));
            array_push($langs, array(
                    'code' => $key,
                    'lang' => $lang,
                    'disabled' => $disablesource ? "disabled" : "",
                    'selected' => $this->current_lang === $key ? "selected" : ""
            ));
            array_push($target_langs, array(
                    'code' => $key,
                    'lang' => $lang,
                    'disabled' => $disabletarget ? "disabled" : "",
                    'selected' => $this->target_lang === $key ? "selected" : ""
            ));
            /*array_push($langs, array(
                    'code' => $key,
                    'lang' => $lang,
                    'disabled' => $this->target_lang === $key ? "disabled" : "",
                    'selected' => $this->current_lang === $key ? "selected" : ""
            ));
            array_push($target_langs, array(
                    'code' => $key,
                    'lang' => $lang,
                    'disabled' => ($this->current_lang === $key ? "disabled" : "",
                    'selected' => $this->target_lang === $key ? "selected" : ""
            ));*/
        }

        // Data for mustache template.
        $data->course = $this->course;
        $data->target_langs = $target_langs;
        $data->langs = $langs;
        $data->target_lang = $this->target_lang;
        $data->current_lang = $this->current_lang;

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

        foreach ($this->coursedata as $section) {
            // count for each section's headers
            foreach ($section['section'] as $s) {
                $this->computeWordcount($s->text, $wordcount, $spaces, $charcountspaces);
            }
            // count for each section's activites
            foreach ($section['activities'] as $a) {
                $this->computeWordcount($a->text, $wordcount, $spaces, $charcountspaces);
            }
        }
        // Set word and character counts to data.
        $data->wordcount = $wordcount;
        $data->charcountspaces = $charcountspaces;
        $data->charcount = $charcountspaces - $spaces;
        // Set langs
        $data->current_lang = $this->current_lang;
        $data->target_lang = $this->target_lang;
        $data->mlangfilter = $this->mlangfilter;
        // Pass data
        $data->course = $this->course;
        $data->coursedata = $this->coursedata;
        return $data;
    }

    /**
     * compute word, spaces and character's count for a single text
     *
     * @param $text
     * @param integer $wc wordcount ref
     * @param integer $sc spaces count ref
     * @param integer $csc char count excluding spaces ref
     * @return void
     */
    private function computeWordcount($text, int &$wc, int &$sc, int &$csc) {
        $tagsStriped = strip_tags($text);
        // Get the wordcount.
        $wc = $wc + strlen(str_word_count($tagsStriped));
        // Get the character count with spaces.
        $csc = $csc + strlen($tagsStriped);
        // Get the character count without spaces.
        $sc = $sc + strlen(array_key_last(preg_split('/\s+/', $tagsStriped)));
    }
}
