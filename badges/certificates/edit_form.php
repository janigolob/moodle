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
 * Form classes for editing badge certificates
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @copyright  2014 onwards Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Form to edit badge certificate details.
 *
 */
class edit_cert_details_form extends moodleform {

    /**
     * Defines the form
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;
        $cert = (isset($this->_customdata['badgecertificate'])) ? $this->_customdata['badgecertificate'] : false;
        $action = $this->_customdata['action'];

        $mform->addElement('header', 'badgecertificatedetails', get_string('badgecertificatedetails', 'badges'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '70'));
        // Using PARAM_FILE to avoid problems later when downloading badge certificate files.
        $mform->setType('name', PARAM_FILE);
        $mform->addRule('name', null, 'required');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'badges'), 'wrap="virtual" rows="8" cols="70"');
        $mform->setType('description', PARAM_CLEANHTML);
        $mform->addRule('description', null, 'required');

        if (has_capability('moodle/badges:assignofficialcertificate', $PAGE->context)) {
            $mform->addElement('checkbox', 'official', get_string('officialtype', 'badges'), get_string('officialtypedesc', 'badges'));
            $mform->setDefault('official', 0);
        } else {
            $mform->addElement('hidden', 'official', 0);
            $mform->setType('official', PARAM_INT);
        }

        $formatoptions = array(
            'A3'      => get_string('certificateformat:A3', 'badges'),
            'A4'      => get_string('certificateformat:A4', 'badges'),
            'B4'      => get_string('certificateformat:B4', 'badges'),
            'B5'      => get_string('certificateformat:B5', 'badges'),
            'LEGAL'   => get_string('certificateformat:Legal', 'badges'),
            'LETTER'  => get_string('certificateformat:Letter', 'badges'),
            'TABLOID' => get_string('certificateformat:Tabloid', 'badges'),
        );
        $mform->addElement('select', 'format', get_string('certificateformat', 'badges'), $formatoptions);
        $mform->setDefault('format', 'A4');
        $mform->addRule('format', null, 'required');

        $orientationoptions = array();
        $orientationoptions[] =& $mform->createElement('radio', 'orientation', '', get_string('certificateorientation:portrait', 'badges'), 'P');
        $orientationoptions[] =& $mform->createElement('static', 'portrait_break', null, '<br/>');
        $orientationoptions[] =& $mform->createElement('radio', 'orientation', '', get_string('certificateorientation:landscape', 'badges'), 'L');
        $mform->addGroup($orientationoptions, 'orientationgr', get_string('certificateorientation', 'badges'), array(' '), false);
        $mform->setDefault('orientation', 'P');
        $mform->addRule('orientationgr', null, 'required');

        $unitoptions = array();
        $unitoptions[] =& $mform->createElement('radio', 'unit', '', get_string('certificateunit:pt', 'badges'), 'pt');
        $unitoptions[] =& $mform->createElement('static', 'pt_break', null, '<br/>');
        $unitoptions[] =& $mform->createElement('radio', 'unit', '', get_string('certificateunit:mm', 'badges'), 'mm');
        $unitoptions[] =& $mform->createElement('static', 'mm_break', null, '<br/>');
        $unitoptions[] =& $mform->createElement('radio', 'unit', '', get_string('certificateunit:cm', 'badges'), 'cm');
        $unitoptions[] =& $mform->createElement('static', 'cm_break', null, '<br/>');
        $unitoptions[] =& $mform->createElement('radio', 'unit', '', get_string('certificateunit:in', 'badges'), 'in');
        $mform->addGroup($unitoptions, 'unitgr', get_string('certificateunit', 'badges'), array(' '), false);
        $mform->setDefault('unit', 'mm');
        $mform->addRule('unitgr', null, 'required');

        if (!isset($cert->certbgimage) or empty($cert->certbgimage)) {
            $imageoptions = array('maxbytes' => 262144, 'accepted_types' => array('web_image'));
            $mform->addElement('filepicker', 'certbgimage', get_string('backgroundimage', 'badges'), null, $imageoptions);
            $mform->addHelpButton('certbgimage', 'backgroundimage', 'badges');
        } else {
            $mform->addElement('static', 'certbgimage', get_string('currentimage', 'badges'));
        }

        $mform->addElement('header', 'issuerdetails', get_string('issuerdetails', 'badges'));

        $mform->addElement('text', 'issuername', get_string('name'), array('size' => '70'));
        $mform->setType('issuername', PARAM_NOTAGS);
        $mform->addRule('issuername', null, 'required');
        if (isset($CFG->badges_defaultissuername)) {
            $mform->setDefault('issuername', $CFG->badges_defaultissuername);
        }
        $mform->addHelpButton('issuername', 'issuername', 'badges');

        $mform->addElement('text', 'issuercontact', get_string('contact', 'badges'), array('size' => '70'));
        if (isset($CFG->badges_defaultissuercontact)) {
            $mform->setDefault('issuercontact', $CFG->badges_defaultissuercontact);
        }
        $mform->setType('issuercontact', PARAM_RAW);
        $mform->addHelpButton('issuercontact', 'contact', 'badges');

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_TEXT);

        if ($action == 'new') {
            $this->add_action_buttons(true, get_string('createcertbutton', 'badges'));
        } else {
            // Add hidden fields.
            $mform->addElement('hidden', 'id', $cert->id);
            $mform->setType('id', PARAM_INT);

            $this->add_action_buttons();
            $this->set_data($cert);

            // Freeze all elements if badge certificate is active or locked.
            if ($cert->is_active() || $cert->is_locked()) {
                $mform->hardFreezeAllVisibleExcept(array());
            }
        }
    }

    /**
     * Load in existing data as form defaults
     *
     * @param stdClass|array $default_values object or array of default values
     */
    public function set_data($cert) {
        $default_values = array();
        parent::set_data($cert);

        $default_values['currentimage'] = $cert->certbgimage;
        parent::set_data($default_values);
    }

    /**
     * Validates form data
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if (!empty($data['issuercontact']) && !validate_email($data['issuercontact'])) {
            $errors['issuercontact'] = get_string('invalidemail');
        }

        // Check for duplicate badge certificate names.
        if ($data['action'] == 'new') {
            $duplicate = $DB->record_exists_select('badge_certificate', 'name = :name',
                array('name' => $data['name']));
        } else {
            $duplicate = $DB->record_exists_select('badge_certificate', 'name = :name AND id != :certid',
                array('name' => $data['name'], 'certid' => $data['id']));
        }

        if ($duplicate) {
            $errors['name'] = get_string('error:duplicatecertname', 'badges');
        }

        return $errors;
    }
}
