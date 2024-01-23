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

/**
 * Settings Test
 */
class settings_test extends \advanced_testcase {
    /**
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * @coversNothing
     * @return void
     */
    public function test_usedeepl(): void {
        global $CFG;
        $this->assertNotNull($CFG);
        $this->assertEquals(2, 1 + 1);
    }

    /**
     * @covers \admin_settingpage
     * @covers \context_system
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_settings(): void {
        global $CFG;
        require_once($CFG->dirroot . '/lib/adminlib.php');
        require_once(__DIR__ . '/../settings.php');
        $settings1 = new \admin_settingpage('local_coursetranslator', get_string('pluginname', 'local_coursetranslator'));
        $this->assertFileExists($CFG->dirroot . '/lib/adminlib.php');
        $this->assertFileExists(__DIR__ . '/../settings.php');
        $this->assertFalse(has_capability('moodle/site:config', \context_system::instance()));
        $this->setAdminUser();
        $this->assertTrue(has_capability('moodle/site:config', \context_system::instance()));
        $this->assertInstanceOf("admin_settingpage", $settings1);
    }

}
