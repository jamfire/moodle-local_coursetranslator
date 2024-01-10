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

namespace local_coursetranslator\data;

use core\context;

/**
 * Course Data Processor
 *
 * Processess course data for moodleform. This class is logic heavy.
 *
 * @package    local_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_data {
    /** @var String */
    protected $dbtable;
    /** @var \stdClass */
    protected $course;
    /** @var \course_modinfo|null */
    protected $modinfo;
    /** @var String*/
    protected $lang;
    /** @var String */
    protected $contextid;
    /** @var \core\context */
    protected $context;
    /**
     * Class Construct
     *
     * @param \stdClass $course
     * @param string $lang
     */
    public function __construct(\stdClass $course, string $lang, context $context) {
        // Set db table.
        $this->dbtable = 'local_coursetranslator';
        // store context
        $this->context = $context;
        // Set course.
        $this->course = $course;
        // Get the context
        $this->contextid = $this->context->id;
        // Set modinfo.
        $modinfo = get_fast_modinfo($course);
        $this->modinfo = $modinfo;

        // Set language.
        $this->lang = $lang === 'other' ? '00' : $lang;
    }

    /**
     * Get Course Data via modinfo
     *
     * @return array
     */
    public function getdata() {
        $coursedata = $this->getcoursedata();
        $sectiondata = $this->getsectiondata();
        $activitydata = $this->getactivitydata();
        /*
         * section added to the activity items.
         * */
        return $this->prepareData($coursedata, $sectiondata, $activitydata);
    }
    /**
     * prepare multidimentional array to re arrange textfields to match course presentation
     */
    private function prepareData($coursedata, $sectiondata, $activitydata)
    {
        $tab = ['0'=>['section'=>$coursedata,'activities'=>[]]];
        //$cd = $coursedata;
        foreach ($sectiondata as $k=>$v ){
            $tab[$v->id] = ['section'=>[$v],'activities'=>[]];
        }
        foreach ($activitydata as $ak=>$av){
            // if the section is not found place it under the course data as general intro
            $sectionid = isset($tab[$av->section])? $av->section : "0";
            $tab[$sectionid]['activities'][$av->id] = $av;
        }
        //$sd = $sectiondata;
        //$ad = $activitydata;

        //return array_merge($coursedata, $sectiondata, $activitydata);
        return $tab;
    }
    /**
     * Get Course Data
     *
     * @return array
     */
    private function getcoursedata() {
        $coursedata = array();
        $course = $this->modinfo->get_course();

        if ($course->fullname) {
            $data = $this->build_data($course->id, $course->fullname, 0, 'course', 'fullname');
            array_push($coursedata, $data);
        }
        if ($course->shortname) {
            $data = $this->build_data($course->id, $course->shortname, 0, 'course', 'shortname');
            array_push($coursedata, $data);
        }
        if ($course->summary) {
            $data = $this->build_data($course->id, $course->summary, $course->summaryformat, 'course', 'summary');
            array_push($coursedata, $data);
        }

        return $coursedata;
    }

    /**
     * Get Section Data
     *
     * @return array
     */
    private function getsectiondata() {
        global $DB;
        $sections = $this->modinfo->get_section_info_all();
        $sectiondata = array();
        foreach ($sections as $sk => $section) {
            $record = $DB->get_record('course_sections', array('course' => $this->course->id, 'section' => $sk));
            if ($record->name) {
                $data = $this->build_data($record->id, $record->name, 0, 'course_sections', 'name');
                array_push($sectiondata, $data);
            }
            if ($record->summary) {
                $data = $this->build_data($record->id, $record->summary, $record->summaryformat, 'course_sections', 'summary');
                array_push($sectiondata, $data);
            }
        }
        return $sectiondata;
    }

    /**
     * Get Activity Data
     *
     * @return array
     */
    private function getactivitydata() {
        global $DB;
        $activitydata = array();

        foreach ($this->modinfo->instances as $instances) {
            foreach ($instances as $ik => $activity) {
                $record = $DB->get_record($activity->modname, array('id' => $ik));

                // Standard name.
                if (isset($record->name) && !empty($record->name)) {
                    $data = $this->build_data(
                        $record->id,
                        $record->name,
                        0,
                        $activity->modname,
                        'name',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }

                // Standard intro.
                if (isset($record->intro) && !empty($record->intro) && trim(strip_tags($record->intro)) !== "") {
                    $data = $this->build_data(
                        $record->id,
                        $record->intro,
                        $record->introformat,
                        'mod_'.$activity->modname,
                        'intro',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }

                // Standard content.
                if (isset($record->content) && !empty($record->content) && trim(strip_tags($record->content)) === "") {
                    $data = $this->build_data(
                        $record->id,
                        $record->content,
                        $record->contentformat,
                        $activity->modname,
                        'content',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }

                if (isset($record->page_after_submit) && !empty($record->page_after_submit)) {
                    $data = $this->build_data(
                        $record->id,
                        $record->page_after_submit,
                        $record->page_after_submitformat,
                        $activity->modname,
                        'page_after_submit',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }

                if (isset($record->instructauthors) && !empty($record->instructauthors)) {
                    $data = $this->build_data(
                        $record->id,
                        $record->instructauthors,
                        $record->instructauthorsformat,
                        $activity->modname,
                        'instructauthors',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }

                if (isset($record->instructreviewers) && !empty($record->instructreviewers)) {
                    $data = $this->build_data(
                        $record->id,
                        $record->instructreviewers,
                        $record->instructreviewersformat,
                        $activity->modname,
                        'instructreviewers',
                        $activity->id,
                            $activity->section
                    );
                    array_push($activitydata, $data);
                }
            }
        }

        return $activitydata;
    }

    /**
     * Build Data Item
     *
     * @param integer $id
     * @param string $text
     * @param integer $format
     * @param string $table
     * @param string $field
     * @param integer $cmid
     * @return \stdClass
     */
    private function build_data($id, $text, $format, $table, $field, $cmid = null, $sectionId= null) {
        global $DB;

        // Build db params.
        $params = array(
            't_id' => $id,
            't_lang' => $this->lang,
            't_table' => $table,
            't_field' => $field
        );

        // Insert tracking record if it does not exist.
        if (!$DB->record_exists($this->dbtable, $params)) {
            $time = time();
            $params['s_lastmodified'] = $time;
            $params['t_lastmodified'] = $time;
            $id = $DB->insert_record($this->dbtable, $params);
            $record = $DB->get_record($this->dbtable, array('id' => $id), 'id,s_lastmodified,t_lastmodified');
        } else {
            $record = $DB->get_record($this->dbtable, $params, 'id,s_lastmodified,t_lastmodified');
        }

        // Build item.
        $item = new \stdClass();
        $item->id = $id;
        $item->tid = $record->id;

        $item->displaytext = $this->getFileURL($text, $this->getItemId($id, $table, $cmid), $table, $field);
        $item->text = $text;
        $item->format = intval($format);
        $item->table = $table;
        $item->field = $field;
        $item->link = $this->link_builder($id, $table, $cmid);
        $item->tneeded = $record->s_lastmodified >= $record->t_lastmodified;
        $item->section = $sectionId;

        return $item;
    }

    /**
     * Link Builder
     *
     * @param integer $id
     * @param string $table
     * @param integer $cmid
     * @return string
     */
    private function link_builder($id, $table, $cmid = 0) {

        $link = null;
        switch ($table) {
            case 'course':
                $link = "/course/edit.php?id={$id}";
                break;
            case 'course_sections':
                $link = "/course/editsection.php?id={$id}";
                break;
            default:
                if ($cmid !== 0) {
                    $link = "/course/modedit.php?update={$cmid}";
                }
                break;
        }

        return $link;
    }
    function getItemId($id, $table, $cmid = 0){
        $i = 0;
        switch($table){
            case 'course':
            case 'course_sections':
                $i = $id;
            default : $i = $cmid;
        }
        return $i;
    }
    /**
     * @todo context ID is not the course context id... We should find the item id somewhere in the db i guess...
     * @param string $text
     * @return array|string|string[]
     */
    private function getFileURL(string $text, $itemId, $component, $area) {
       return file_rewrite_pluginfile_urls($text, 'pluginfile.php',$this->contextid, $component, $area, $itemId);
    }
}
