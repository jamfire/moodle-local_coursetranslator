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
 * Local Course Translator Settings Page
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Admin_settings
 */

defined('MOODLE_INTERNAL') || die();
//$test = 1;
if (has_capability('moodle/site:config', \context_system::instance())) {
    global $ADMIN;
    // Create new settings page.
    $settings = new admin_settingpage('local_coursetranslator', get_string('pluginname', 'local_coursetranslator'));

    // Add to admin menu.
    $ADMIN->add('localplugins', $settings);

    // Use deepl machine translation.
    $settings->add(
            new admin_setting_configcheckbox(
                    'local_coursetranslator/usedeepl',
                    get_string('usedeepl', 'local_coursetranslator'),
                    get_string('usedeepl_desc', 'local_coursetranslator'),
                    false
            )
    );

    // DeepL apikey.
    $settings->add(
            new admin_setting_configtext(
                    'local_coursetranslator/apikey',
                    get_string('apikey', 'local_coursetranslator'),
                    get_string('apikey_desc', 'local_coursetranslator'),
                    null,
                    PARAM_RAW_TRIMMED,
                    40
            )
    );

    // DeepL Free or Pro?
    $settings->add(
            new admin_setting_configcheckbox(
                    'local_coursetranslator/deeplpro',
                    get_string('deeplpro', 'local_coursetranslator'),
                    get_string('deeplpro_desc', 'local_coursetranslator'),
                    false
            )
    );

    // Use translation page autotranslation.
    $settings->add(
            new admin_setting_configcheckbox(
                    'local_coursetranslator/useautotranslate',
                    get_string('useautotranslate', 'local_coursetranslator'),
                    get_string('useautotranslate_desc', 'local_coursetranslator'),
                    false
            )
    );

}
