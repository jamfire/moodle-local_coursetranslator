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

namespace local_coursetranslator\external;

use core_external\external_api;

/**
 * Local Course Translator get_field Web Service
 *
 * Adds a webservice available via ajax for the Translate Content page.
 *
 * @package    local_coursetranslator
 * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/External_functions_API
 */
class get_field extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'data' => new external_multiple_structure(
                        new external_single_structure([
                                'courseid' => new external_value(
                                        PARAM_INT, 'course id',
                                        VALUE_REQUIRED
                                ),
                                'id' => new external_value(
                                        PARAM_INT,
                                        'id of table record',
                                        VALUE_REQUIRED
                                ),
                                'table' => new external_value(
                                        PARAM_RAW,
                                        'table to update text',
                                        VALUE_REQUIRED
                                ),
                                'field' => new external_value(
                                        PARAM_RAW,
                                        'table field to update',
                                        VALUE_REQUIRED
                                ),
                        ]))]);
    }

    /**
     * @return external_single_structure
     * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     * @see        https://docs.moodle.org/dev/External_functions_API
     * @package    local_coursetranslator
     */
    public static function execute_returns(): external_single_structure {
        return new external_multiple_structure(
                new external_single_structure([
                        'text' => new external_value(
                                PARAM_RAW,
                                'updated text of field'
                        )
                ])
        );
    }

    /**
     * @return void
     * @copyright  2024 Bruno Baudry <bruno.baudry@bfh.ch>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     * @see        https://docs.moodle.org/dev/External_functions_API
     * @package    local_coursetranslator
     */
    public static function execute($data) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['data' => $data]);
        $transaction = $DB->start_delegated_transaction();
        $response = [];
        foreach ($params['data'] as $d) {
            // Check for null values and throw errors.
            if ($d->courseid === 0) {
                throw new invalid_parameter_exception('invalid group id');
            }
            if ($d->id === 0) {
                throw new invalid_parameter_exception('invalid table id');
            }
            // Security checks.
            $context = context_course::instance($d->courseid);
            self::validate_context($context);
            require_capability('local/coursetranslator:edittranslations', $context);

            // Get the original record.
            $record = (array) $DB->get_record($d['table'], ['id' => $d['id']]);
            $text = $record[$d['field']];

            $response[] = ['text' => $text];
        }

        // Commit the transaction.
        $transaction->allow_commit();

        return $response;
    }
}
