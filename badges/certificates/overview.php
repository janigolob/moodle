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
 * Badge certificate overview page
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

$certid = required_param('id', PARAM_INT);

require_login();

if (empty($CFG->enablebadges)) {
    print_error('badgesdisabled', 'badges');
}

$cert = new badge_certificate($certid);
$context = $cert->get_context();
$navurl = new moodle_url('/badges/certificates/index.php', array('type' => $cert->type));

if ($cert->type == CERT_TYPE_COURSE) {
    if (empty($CFG->badges_allowcoursebadges)) {
        print_error('coursebadgesdisabled', 'badges');
    }
    require_login($cert->courseid);
    $navurl = new moodle_url('/badges/certificates/index.php', array('type' => $cert->type, 'id' => $cert->courseid));
    $PAGE->set_pagelayout('standard');
    navigation_node::override_active_url($navurl);
} else {
    $PAGE->set_pagelayout('admin');
    navigation_node::override_active_url($navurl, true);
}

$currenturl = new moodle_url('/badges/certificates/overview.php', array('id' => $cert->id));

$PAGE->set_context($context);
$PAGE->set_url($currenturl);
$PAGE->set_heading($cert->name);
$PAGE->set_title($cert->name);
$PAGE->navbar->add($cert->name);

echo $OUTPUT->header();
echo $OUTPUT->heading($cert->name);

$output = $PAGE->get_renderer('core', 'badges');
echo $output->print_badgecert_status_box($cert);
$output->print_badgecert_tabs($certid, $context, 'overview');
echo $output->print_badgecert_overview($cert, $context);

echo $OUTPUT->footer();