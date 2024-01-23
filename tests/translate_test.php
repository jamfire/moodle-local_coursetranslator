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
 * Test cases
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/PHPUnit
 */

namespace local_coursetranslator;

use advanced_testcase;
use context_course;
use filter_multilang2;
use local_coursetranslator\data\course_data;
use local_coursetranslator\output\translate_page;

/**
 * Translate Test
 */
class translate_test extends advanced_testcase {
    public function test_course() {
        require_once(__DIR__ . '/../../../config.php');
        global $CFG;
        global $PAGE;
        global $DB;
        $this->trace_to_cli(__DIR__, 'Directory');
        $course1 = $this->getDataGenerator()->create_course();
        $this->assertIsString($course1->id);
        $this->assertNotNull($DB);
        $coursedb = $DB->get_record('course', array('id' => $course1->id), '*', MUST_EXIST);
        $this->assertIsString($coursedb->id);
        $coursedbid = intval($coursedb->id);
        $this->assertIsInt($coursedbid);
        $this->assertEquals($course1->id, $coursedb->id);
        $PAGE->set_context(context_course::instance($coursedbid));
        $this->assertEquals($PAGE->context->id, context_course::instance($coursedbid)->id);
        $PAGE->set_context(context_course::instance($course1->id));
        $this->assertEquals($PAGE->context->id, context_course::instance($course1->id)->id);
    }

    private function trace_to_cli(mixed $var, string $info) {
        echo "\n" . $info . "\n";
        var_dump($var);
        ob_flush();
    }

    public function test_plugin_config() {
        global $CFG;
        $this->assertNotNull(get_config('local_coursetranslator', 'apikey'));
        $this->assertMatchesRegularExpression('/^0|1$/', get_config('local_coursetranslator', 'useautotranslate'));
        $this->assertNotEquals('', get_string('supported_languages', 'local_coursetranslator'));
        $this->assertTrue(strlen(get_string('supported_languages', 'local_coursetranslator')) > 0);
        $this->assertNotEquals('', current_language());
        $this->assertTrue(strlen(current_language()) > 0);
    }

    public function test_mlang_filter() {
        global $CFG;
        $this->assertFileExists($CFG->dirroot . '/filter/multilang2/filter.php');
        require_once($CFG->dirroot . '/filter/multilang2/filter.php');
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $mlangfilter = new filter_multilang2($context, array());
        $this->assertNotNull($mlangfilter);
        $this->assertIsString($mlangfilter->filter($course->fullname));
        $this->assertTrue($mlangfilter->filter($course->fullname) > 0);
    }

    public function test_course_data() {
        global $CFG;
        $this->assertFileExists($CFG->dirroot . '/local/coursetranslator/classes/output/translate_page.php');
        $this->assertFileExists($CFG->dirroot . '/local/coursetranslator/classes/data/course_data.php');
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $coursedata = new course_data($course, $CFG->lang, $context);
        $this->assertNotNull($coursedata);
        $this->assertIsArray($coursedata->getdata());
        $renderable = new translate_page($course, $coursedata->getdata(),
                new filter_multilang2($context, array()));
        $this->assertNotNull($renderable);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }
}
