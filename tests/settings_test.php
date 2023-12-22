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
 * Local Course Translator Web Service
 *
 * Adds a webservice available via ajax for the Translate Content page.
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/PHPUnit
 */

namespace local_coursetranslator;
define('CLI_SCRIPT', true);
use PHPUnit\Framework\TestCase;
require_once (__DIR__.'/../../../lib/phpunit/classes/advanced_testcase.php');

/**
 * Settings Test
 */
final class settings_test extends core\advanced_testcase {

    public function test_usedeepl() {
        require_once(__DIR__ . '/../../../config.php');
        global $CFG;
        require_once($CFG->dirroot . '/local/coursetranslator/settings.php');

        $this->assertNotNull($CFG);
        $this->assertEquals(2, 1 + 1);
    }
    public function test_settings(){

    }

}
