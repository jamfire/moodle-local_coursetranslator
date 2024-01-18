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
 * @todo use deepl-php instead of js ajax for further maintainability and absctraction
 * @todo check images tag handling in deepl.
 */
/**
 * Local Course Translator Translate Page
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Output_API
 */

// Get libs.
require_once(__DIR__ . '/../../config.php');
global $CFG;
global $PAGE;
global $DB;
require_once($CFG->dirroot . '/filter/multilang2/filter.php');
require_once('./classes/output/translate_page.php');
require_once('./classes/data/course_data.php');
require_once($CFG->dirroot . '/lib/editorlib.php');

// Needed vars for processing.
$courseid = required_param('course_id', PARAM_INT);
$lang = optional_param('target_lang', 'other', PARAM_NOTAGS);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Setup page.
$context = context_course::instance($courseid);
$PAGE->set_context($context);
require_login();
require_capability('local/coursetranslator:edittranslations', $context);

// Get js data.
$jsconfig = new stdClass();
$jsconfig->apikey = get_config('local_coursetranslator', 'apikey');
$jsconfig->autotranslate = boolval(get_config('local_coursetranslator', 'useautotranslate'))
        && in_array($lang, explode(',', get_string('supported_languages', 'local_coursetranslator')));
$jsconfig->lang = $lang;
$jsconfig->currentlang = current_language();
$jsconfig->syslang = $CFG->lang;
$jsconfig->courseid = $courseid;
$jsconfig->deeplurl = boolval(get_config('local_coursetranslator', 'deeplpro'))
        ? 'https://api.deepl.com/v2/translate?'
        : 'https://api-free.deepl.com/v2/translate?';
$jsconfig->multiplemlang = get_string('t_multiplemlang', 'local_coursetranslator');
$jsconfig->autosavedmsg = get_string('t_autosaved', 'local_coursetranslator');
$jsconfig->needsupdate = get_string('t_needsupdate', 'local_coursetranslator');
$jsconfig->uptodate = get_string('t_uptodate', 'local_coursetranslator');
$jsconfig->debug = $CFG->debug;

$mlangfilter = new \filter_multilang2($context, array());

// Set initial page layout.
$title = get_string('pluginname', 'local_coursetranslator');
$PAGE->set_url('/local/coursetranslator/translate.php', array('course_id' => $courseid));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('base');
$PAGE->set_course($course);

//$jsconfig->ed = editors_get_enabled();
$defaultEditor = strstr($CFG->texteditors, ',', true);
$userPrefs = get_user_preferences();
// get users prefrences to pass the editor's type to js
$jsconfig->userPrefs = $userPrefs['htmleditor'] ?? $defaultEditor;

// adding PAges CSS
$PAGE->requires->css('/local/coursetranslator/styles.css');
// Adding page's JS
$PAGE->requires->js_call_amd('local_coursetranslator/coursetranslator', 'init', array($jsconfig));

// Get the renderer.
$output = $PAGE->get_renderer('local_coursetranslator');

// Output header.
echo $output->header();

// Course name heading.
echo $output->heading($mlangfilter->filter($course->fullname));

// Output translation grid.
$coursedata = new \local_coursetranslator\data\course_data($course, $lang, $context);
$renderable = new \local_coursetranslator\output\translate_page($course, $coursedata->getdata(), $mlangfilter);
echo $output->render($renderable, $course);

// Output footer.
echo $output->footer();
