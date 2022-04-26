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
 * Add Translate Link to Edit Settings
 *
 * @param object $navigation
 * @param object $course
 * @return void
 */
function local_coursetranslator_extend_navigation_course($navigation, $course) {

    // Get current language.
    $lang = current_language();

    // Build a moodle url.
    $url = new moodle_url("/local/coursetranslator/translate.php?course_id=$course->id&course_lang=other");

    // Get title of translate page for navigation menu.
    $title = get_string('pluginname', 'local_coursetranslator');

    // Navigation node.
    $translatecontent = navigation_node::create(
        $title,
        $url,
        navigation_node::TYPE_CUSTOM,
        $title,
        'translate',
        new pix_icon('icon', 'icon', 'local_coursetranslator')
    );
    $navigation->add_node($translatecontent);
}

