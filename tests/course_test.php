<?php

namespace local_coursetranslator;

use advanced_testcase;
use context_course;

class course_test extends advanced_testcase {
    /**
     * Test course creation and context
     *
     * @covers \context_course
     * @return void
     * @throws \dml_exception
     */
    public function test_course(): void {
        //global $CFG;
        global $PAGE;
        global $DB;

        $course1 = $this->getDataGenerator()->create_course();
        $coursedb = $DB->get_record('course', ['id' => $course1->id], '*', MUST_EXIST);
        $coursedbid = intval($coursedb->id);
        $PAGE->set_context(context_course::instance($coursedbid));
        $this->assertEquals($PAGE->context->id, context_course::instance($coursedbid)->id);
        $PAGE->set_context(context_course::instance($course1->id));
        $this->assertEquals($PAGE->context->id, context_course::instance($course1->id)->id);
    }

    /**
     * Check modinfo
     *
     * @covers local_coursetranslator\data\course_data
     * @return void
     * @throws \moodle_exception
     */
    function test_modinfo(): void {
        $course = $this->getDataGenerator()->create_course();
        $modinfo = get_fast_modinfo($course);
        $this->assertNotNull($modinfo);
        $clone = $modinfo->get_course();
        $this->assertNotNull($clone);
        $this->assertEquals($course->id, $clone->id);
    }

    /**
     * Set it up
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest(true);
    }
}
