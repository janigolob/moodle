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
 * First step page for creating a new badge certificate
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @copyright  2014 onwards Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @author     Gregor Anzelj <gregor.anzelj@gmail.com>
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/certificates/editelement_form.php');

$certid = optional_param('id', 0, PARAM_INT);

require_login();

if (empty($CFG->enablebadges)) {
    print_error('badgesdisabled', 'badges');
}

$cert = new badge_certificate($certid);
$type = $cert->type;
$context = $cert->get_context();

if (empty($CFG->badges_allowcoursebadges) && ($type == CERT_TYPE_COURSE)) {
    print_error('coursebadgesdisabled', 'badges');
}

$title = get_string('createelement', 'badges');

if (($type == CERT_TYPE_COURSE) && ($course = $DB->get_record('course', array('id' => $certid)))) {
    require_login($course);
    $coursecontext = context_course::instance($course->id);
    $PAGE->set_context($coursecontext);
    $PAGE->set_pagelayout('course');
    $PAGE->set_url('/badges/certificates/newelement.php', array('type' => $type, 'id' => $certid));
    $heading = format_string($course->fullname, true, array('context' => $coursecontext)) . ": " . $title;
    $PAGE->set_heading($heading);
    $PAGE->set_title($heading);
} else {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('admin');
    $PAGE->set_url('/badges/certificates/newelement.php', array('type' => $type, 'id' => $certid));
    $PAGE->set_heading($title);
    $PAGE->set_title($title);
}

require_capability('moodle/badges:createcertificate', $PAGE->context);

$fordb = new stdClass();
$fordb->id = null;

$form = new edit_cert_element_form($PAGE->url, array('action' => 'new'));

if ($form->is_cancelled()) {
    redirect(new moodle_url('/badges/certificates/elements.php', array('id' => $certid)));
} else if ($data = $form->get_data()) {
    // Creating new badge certificate element here.
    $fordb->certid = $certid;
    $fordb->x = $data->x;
    $fordb->y = $data->y;
    $fordb->rawtext = $data->rawtext;
    $fordb->size = $data->size;
    $fordb->family = $data->family;
    $fordb->align = $data->align;

    $newid = $DB->insert_record('badge_certificate_elms', $fordb, true);

    redirect(new moodle_url('/badges/certificates/elements.php', array('id' => $certid)));
}

echo $OUTPUT->header();
echo $OUTPUT->box('', 'notifyproblem hide', 'check_connection');

$form->display();

echo $OUTPUT->footer();