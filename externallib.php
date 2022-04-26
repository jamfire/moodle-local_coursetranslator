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

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

/**
 * Local Course Translator Web Service
 *
 * Adds a webservice available via ajax for the Translate Content page.
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/External_functions_API
 */
class local_coursetranslator_external extends external_api {

    /**
     * Update Translation Parameters
     *
     * Adds validaiton parameters for translations
     *
     * @return external_function_parameters
     */
    public static function update_translation_parameters() {
        return new external_function_parameters(
            array(
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid'  => new external_value(PARAM_INT, 'course id'),
                            'id'        => new external_value(PARAM_INT, 'id of table record'),
                            'table'     => new external_value(PARAM_RAW, 'table to update text'),
                            'field'     => new external_value(PARAM_RAW, 'table field to update'),
                            'text'      => new external_value(PARAM_RAW, 'text to be upserted'),
                        )
                    )
                )
            )
        );
    }

    /**
     * Update Translation
     *
     * Add translation to the database
     *
     * @param object $translation
     * @return array
     */
    public static function update_translation($data) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::update_translation_parameters(), array('data' => $data));

        $transaction = $DB->start_delegated_transaction();

        $response = array();

        foreach ($params['data'] as $data) {

            purge_all_caches();

            // Check for null values and throw errors @todo.

            // Security checks.
            $context = context_course::instance($data['courseid']);
            self::validate_context($context);
            require_capability('local/coursetranslator:edittranslations', $context);

            // Update the record.
            $dataobject = array();
            $dataobject['id'] = $data['id'];
            $dataobject[$data['field']] = $data['text'];
            $record->id = $DB->update_record($data['table'], (object) $dataobject);
            $response[] = array(
                'id' => $record->id,
                'dataobject' => serialize($dataobject)
            );
        }

        // Commit the transaction.
        $transaction->allow_commit();

        return $response;
    }

    /**
     * Return Translation
     *
     * Returns updated translation to the user from web service.
     *
     * @return external_multiple_structure
     */
    public static function update_translation_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'            => new external_value(PARAM_INT, 'id of table record'),
                    'dataobject'    => new external_value(PARAM_RAW, 'serialized dataobject'),
                )
            )
        );
    }

}
