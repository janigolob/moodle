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
 * Unit tests for badges
 *
 * @package    core
 * @subpackage badges
 * @copyright  2013 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/badgeslib.php');

class core_badges_certs_badgeslib_testcase extends advanced_testcase {
    protected $course;
    protected $user;
    protected $certid;
    protected $certdata;
    protected $cert_element_id;
    protected $cert_element_data;
    
    protected function setUp() {
        global $DB, $CFG;
        $this->resetAfterTest(true);
        
        $CFG->enablecompletion = true;
        
        $this->course = $this->getDataGenerator()->create_course(array('enablecompletion' => true));
        $this->user = $this->getDataGenerator()->create_user();
        
        $this->create_badge_certificate();
        $this->create_badge_certificate_element();
    }
    
    public function test_badges_get_certificates() {
        $certs = badges_get_certificates(CERT_TYPE_SITE, $this->course->id, '', '', 0, CERT_PERPAGE, $this->user->id);
        
        $this->assertEquals(count($certs), 1);
        $this->assertEquals($certs[1]->id, $this->certid);
    }
    
    public function test_create_badge_certificate() {
        $cert = new badge_certificate($this->certid);

        $this->assertInstanceOf('badge_certificate', $cert);
        $this->assertEquals($cert->id, $this->certid);
        $this->assertNotEquals($cert->id, $this->certdata->id);
        
        $this->assertEquals($cert->name, $this->certdata->name);
        $this->assertEquals($cert->description, $this->certdata->description);
        $this->assertEquals($cert->official, $this->certdata->official);
        $this->assertEquals($cert->timecreated, $this->certdata->timecreated);
        $this->assertEquals($cert->timemodified, $this->certdata->timemodified);
        $this->assertEquals($cert->usercreated, $this->certdata->usercreated);
        $this->assertEquals($cert->usermodified, $this->certdata->usermodified);
        $this->assertEquals($cert->issuername, $this->certdata->issuername);
        $this->assertEquals($cert->issuercontact, $this->certdata->issuercontact);
        $this->assertEquals($cert->format, $this->certdata->format);
        $this->assertEquals($cert->orientation, $this->certdata->orientation);
        $this->assertEquals($cert->unit, $this->certdata->unit);
        $this->assertEquals($cert->type, $this->certdata->type);
        $this->assertEquals($cert->courseid, $this->certdata->courseid);
        $this->assertEquals($cert->status, $this->certdata->status);
    }
    /*
    public function test_delete_badge_certificate() {
        $cert = new badge_certificate($this->certid);

        $cert->delete();
        
        $this->setExpectedException('moodle_exception');
        
        new badge_certificate($this->certid);
    }*/
    
    public function test_badge_certificate_status() {
        $this->resetAfterTest(true);
    
        $cert = new badge_certificate($this->certid);
        $old_status = $cert->status;
        
        $cert->set_status(CERT_STATUS_ACTIVE);
        $this->assertAttributeNotEquals($old_status, 'status', $cert);
        $this->assertAttributeEquals(CERT_STATUS_ACTIVE, 'status', $cert);
        $this->assertTrue($cert->is_active());
        $this->assertFalse($cert->is_locked());
        
        $cert->set_status(CERT_STATUS_ACTIVE_LOCKED);
        $this->assertAttributeEquals(CERT_STATUS_ACTIVE_LOCKED, 'status', $cert);
        $this->assertTrue($cert->is_active());
        $this->assertTrue($cert->is_locked());
        
        $cert->set_status(CERT_STATUS_INACTIVE_LOCKED);
        $this->assertAttributeEquals(CERT_STATUS_INACTIVE_LOCKED, 'status', $cert);
        $this->assertFalse($cert->is_active());
        $this->assertTrue($cert->is_locked());
    }
    
    public function test_create_badge_certificate_element() {
        $cert_element = new badge_cert_element($this->cert_element_id);

        $this->assertInstanceOf('badge_cert_element', $cert_element);
        $this->assertEquals($cert_element->id, $this->cert_element_id);
        $this->assertNotEquals($cert_element->id, $this->cert_element_data->id);
        
        $this->assertEquals($cert_element->certid, $this->certid);
        $this->assertEquals($cert_element->x, $this->cert_element_data->x);
        $this->assertEquals($cert_element->y, $this->cert_element_data->y);
        $this->assertEquals($cert_element->size, $this->cert_element_data->size);
        $this->assertEquals($cert_element->family, $this->cert_element_data->family);
        $this->assertEquals($cert_element->rawtext, $this->cert_element_data->rawtext);
        $this->assertEquals($cert_element->align, $this->cert_element_data->align);
    }
    
    public function test_delete_badge_certificate_element() {
        $this->resetAfterTest(true);
        
        $cert = new badge_certificate($this->certid);
        $cert_element = new badge_cert_element($this->cert_element_id);
        
        $this->assertTrue($cert->has_elements());
        
        $cert_element->delete();
        
        $cert = new badge_certificate($this->certid);
        
        $this->assertFalse($cert->has_elements());
        
        $this->setExpectedException('moodle_exception');
        
        new badge_cert_element($this->cert_element_id);
    }
    
    public function test_badge_certificate_element_status() {
        $cert = new badge_certificate($this->certid);
        $cert_element = new badge_cert_element($this->cert_element_id);
        
        $old_status = $cert->status;
        
        $cert->set_status(CERT_STATUS_ACTIVE);
        $this->assertTrue($cert_element->is_active());
        $this->assertFalse($cert_element->is_locked());
        
        $cert->set_status(CERT_STATUS_ACTIVE_LOCKED);
        $this->assertTrue($cert_element->is_active());
        $this->assertTrue($cert_element->is_locked());
        
        $cert->set_status(CERT_STATUS_INACTIVE_LOCKED);
        $this->assertFalse($cert_element->is_active());
        $this->assertTrue($cert_element->is_locked());
    }
    
    public function test_badges_get_certelements() {
        $this->resetAfterTest(true);
        
        $cert = new badge_certificate($this->certid);
        $elms = badges_get_certelements($this->certid);
        
        $this->assertEquals(count($elms), 1);
        $this->assertEquals($elms[1]->certid, $this->certid);
        $this->assertEquals($elms[1]->id, $this->cert_element_id);
        
        $cert_element = new badge_cert_element($this->cert_element_id);
        $cert_element->delete();
        
        $elms1 = badges_get_certelements($this->certid);
        $this->assertEquals(count($elms1), 0);
    }
    
    private function create_badge_certificate() {
        global $DB;
        
        $fordb = new stdClass();
        $fordb->id = null;
        $fordb->name = "Test badge certificate";
        $fordb->description = "Testing badge certificate";
        $fordb->official = 0;
        $fordb->timecreated = time();
        $fordb->timemodified = time();
        $fordb->usercreated = $this->user->id;
        $fordb->usermodified = $this->user->id;
        $fordb->issuername = "Test issuer";
        $fordb->issuercontact = "issuer@example.com";
        $fordb->format = 'A4';
        $fordb->orientation = 'P';
        $fordb->unit = 'mm';
        $fordb->type = CERT_TYPE_SITE;
        $fordb->courseid = $this->course->id;
        $fordb->status = CERT_STATUS_INACTIVE;
        
        $this->certid = $DB->insert_record('badge_certificate', $fordb, true);
        $this->certdata = $fordb;
    }
    
    private function create_badge_certificate_element() {
        global $DB;
        
        $fordb = new stdClass();
        $fordb->id = null;
        $fordb->certid = $this->certid;
        $fordb->x = rand(1, 200);
        $fordb->y = rand(1, 200);
        $fordb->size = rand(1, 200);
        $fordb->family = '';
        $fordb->rawtext = '';
        $fordb->align = null;
        
        $this->cert_element_id = $DB->insert_record('badge_certificate_elms', $fordb, true);
        $this->cert_element_data = $fordb;
    }
}
