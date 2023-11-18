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
 * Local Course Translator
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/version.php
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'local_coursetranslator';  // Full name of the plugin (used for diagnostics).
$plugin->version      = 2022050300;                // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires     = 2020061500;                // Requires Moodle 3.9 LTS.
$plugin->supported    = [39,400];                // Supported Moodle Versions.
$plugin->maturity     = MATURITY_ALPHA;            // Maturity level.
$plugin->release      = 'v0.9.3';                  // Semantic Versioning for CHANGES.md.
$plugin->dependencies = array(                     // Dependencies.
    'filter_multilang2' => 2020101300
);
