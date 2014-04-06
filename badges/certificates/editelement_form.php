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
 * Form to edit badge certificate elements.
 *
 */
class edit_cert_element_form extends moodleform {

    /**
     * Defines the form
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;
        $element = (isset($this->_customdata['certificateelement'])) ? $this->_customdata['certificateelement'] : false;
        $action = $this->_customdata['action'];

        $mform->addElement('header', 'badgecertificateelement', get_string('badgecertificateelement', 'badges'));
        $mform->addElement('text', 'rawtext', get_string('rawtext', 'badges'), array('size' => '70'));
        $mform->addHelpButton('rawtext', 'rawtext', 'badges');
        $mform->setType('rawtext', PARAM_RAW);
        $mform->addRule('rawtext', null, 'required');
        $mform->addRule('rawtext', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'x', get_string('elementposition:x', 'badges'), array('size' => '4'));
        $mform->setType('x', PARAM_INT);
        $mform->setDefault('x', 0);
        $mform->addRule('x', null, 'required');
        $mform->addRule('x', null, 'maxlength', 4);

        $mform->addElement('text', 'y', get_string('elementposition:y', 'badges'), array('size' => '4'));
        $mform->setType('y', PARAM_INT);
        $mform->setDefault('y', 0);
        $mform->addRule('y', null, 'required');
        $mform->addRule('y', null, 'maxlength', 4);

        $mform->addElement('text', 'size', get_string('elementsize', 'badges'), array('size' => '3'));
        $mform->setType('size', PARAM_INT);
        $mform->setDefault('size', 12);
        $mform->addRule('size', null, 'required');
        $mform->addRule('size', null, 'maxlength', 3);

        $familyoptions = array(
            'freesans'    => get_string('elementfamily:freesans', 'badges'),
            'freeserif'   => get_string('elementfamily:freeserif', 'badges'),
        );
        $mform->addElement('select', 'family', get_string('elementfamily', 'badges'), $familyoptions);
        $mform->addRule('family', null, 'required');
        $mform->setDefault('family', 'freesans');

        $alignoptions = array(
            'L'      => get_string('elementalign:L', 'badges'),
            'C'      => get_string('elementalign:C', 'badges'),
            'R'      => get_string('elementalign:R', 'badges'),
            'I'      => get_string('elementalign:I', 'badges'), // Invert
            'T'      => get_string('elementalign:T', 'badges'), // Top down
            'B'      => get_string('elementalign:B', 'badges'), // Bottom up
            ''      => get_string('elementalign:0', 'badges'),
        );
        $mform->addElement('select', 'align', get_string('elementalign', 'badges'), $alignoptions);
        $mform->setDefault('align', '');

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_TEXT);

        if ($action == 'new') {
            $this->add_action_buttons(true, get_string('createcertelmbutton', 'badges'));
        } else {
            // Add hidden fields.
            $mform->addElement('hidden', 'id', $element->id);
            $mform->setType('id', PARAM_INT);

            $this->add_action_buttons();
            $this->set_data($element);

            // Freeze all elements if badge certificate is active or locked.
            if ($element->is_active() || $element->is_locked()) {
                $mform->hardFreezeAllVisibleExcept(array());
            }
        }
    }

    /**
     * Load in existing data as form defaults
     *
     * @param stdClass|array $default_values object or array of default values
     */
    public function set_data($element) {
        $default_values = array();
        parent::set_data($element);

        parent::set_data($default_values);
    }

    /**
     * Validates form data
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        return $errors;
    }
}
