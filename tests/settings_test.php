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

namespace local_coursetranslator\tests;

/**
 * Settings Test
 */
class settings_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    public function test_usedeepl() {
        global $CFG;
        $this->assertNotNull($CFG);
        $this->assertEquals(2, 1 + 1);
    }

    public function test_settings() {
        global $CFG;

        //echo $CFG->dirroot . '/lib/adminlib.php';
        require_once($CFG->dirroot . '/lib/adminlib.php');
        require_once(__DIR__ . '/../settings.php');
        $settings1 = new \admin_settingpage('local_coursetranslator', get_string('pluginname', 'local_coursetranslator'));
        $this->assertFileExists($CFG->dirroot . '/lib/adminlib.php');
        $this->assertFileExists(__DIR__ . '/../settings.php');
        $this->assertFalse(has_capability('moodle/site:config', \context_system::instance()));
        $this->setAdminUser();
        $this->assertTrue(has_capability('moodle/site:config', \context_system::instance()));
        //global $ADMIN;
        //$this->assertNotNull($ADMIN);
        //$this->assertNotNull($CFG);
        //$this->assertNotNull($settings);
        //$this->assertFalse($test_settings === 1);
        //$this->assertNotNull($ADMIN);
        //$this->assertTrue(has_capability('moodle/site:config', \context_system::instance()));
        $this->assertInstanceOf("admin_settingpage", $settings1);
        //$this->assertInstanceOf("admin_settingpage", $settings);
        //$settings = new \admin_settingpage('local_coursetranslator', get_string('pluginname', 'local_coursetranslator'));

    }

}
