<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004 - 2008 SugarCRM Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 * Contributor(s): Chris Owen <owenc@hubris.net>, Ray Gauss II <rgauss@drscomptech.com>, Scott Eade (DST Fix)
 ********************************************************************************/
/*********************************************************************************

 * Description:
 ********************************************************************************/

require_once('include/utils.php');
require_once('modules/Calendar/Calendar.php');
require_once('modules/vCals/vCal.php');

class iCal extends vCal {

    const UTC_FORMAT = 'Ymd\THi00\Z';

    var $lineEnding = "\r\n";

    function iCal() {
        parent::vCal();
    }

    function ical_escape_text($text)
    {
        //$text = strip_tags($text);
        $text = str_replace('"', '\"', $text);
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace(",", "\,", $text);
        $text = str_replace(":", "\:", $text);
        $text = str_replace(";", "\;", $text);
        $text = str_replace("\n", "\n ", $text);
//         $text = str_replace(array("\n", "\r\n"), "\\n", $text);
        return $text;
    }

    function get_utc_date_time($dateTime) {
        return $dateTime->format(self::UTC_FORMAT);
    }

    function get_utc_time($ts) {
        global $timedate, $sugar_version;
        if (substr($sugar_version, 0, 1) > 5) {
            $timestamp = ($ts+(date('Z')-$timedate->adjustmentForUserTimeZone()*60));
        } else {
            $timestamp = ($ts);
        }
        return $this->get_utc_date_time(new SugarDateTime("@" . $ts));
    }

    function convert_min_to_hr_min($minutes) {
        $hrs = floor(abs($minutes) / 60);
        $remainderMinutes = abs($minutes) - ($hrs * 60);
        $sign = (($minutes < 0) ? "-" : "+");
        return $sign . str_pad($hrs, 2, "0", STR_PAD_LEFT) . str_pad($remainderMinutes, 2, "0", STR_PAD_LEFT);
    }

    function create_sugar_ical_todo($user_bean, $task, $moduleName, $dtstamp) {
        global $sugar_config;
        $str = "";
        $str .= "BEGIN:VTODO$this->lineEnding";
        $validDueDate = (isset($task->date_due) && $task->date_due != "" && $task->date_due != "0000-00-00");
        $validDueTime = (isset($task->time_due) && $task->time_due != "");
        $dueYear = 1970;
        $dueMonth = 1;
        $dueDay = 1;
        $dueHour = 0;
        $dueMin = 0;
        if ($validDueDate) {
            $dateDueArr = split("-", $task->date_due);
            $dueYear = (int)$dateDueArr[0];
            $dueMonth = (int)$dateDueArr[1];
            $dueDay = (int)$dateDueArr[2];

            if ($validDueTime) {
                $timeDueArr = split(":", $task->time_due);
                $dueHour = (int)$timeDueArr[0];
                $dueMin = (int)$timeDueArr[1];
            }
        }
        $date_arr = array(
             'day'=>$dueDay,
             'month'=>$dueMonth,
             'hour'=>$dueHour,
             'min'=>$dueMin,
             'year'=>$dueYear);
        $due_date_time = new SugarDateTime();
        $due_date_time->setDate($dueYear, $dueMonth, $dueDay);
        $due_date_time->setTime($dueHour, $dueMin);
        $str .= "DTSTART;TZID=" . $user_bean->getPreference('timezone') . ":" .
                    str_replace("Z", "", $this->get_utc_date_time($due_date_time)) . $this->lineEnding;
        $str .= "DTSTAMP:" . $dtstamp . $this->lineEnding;
        $str .= "SUMMARY:" . $this->ical_escape_text($task->name) . $this->lineEnding;
        $str .= "UID:" . $task->id . $this->lineEnding;
        if ($validDueDate) {
            $iCalDueDate = str_replace("-", "", $task->date_due);
            if (strlen($iCalDueDate) > 8) {
                $iCalDueDate = substr($iCalDueDate, 0, 8);
            }
            $str .= "DUE;VALUE=DATE:" . $iCalDueDate . $this->lineEnding;
        }
        if ($moduleName == "ProjectTask") {
            $str .= "DESCRIPTION:Project: " . $task->project_name. "\\n\\n" .
                $this->ical_escape_text($task->description). $this->lineEnding;
        } else {
            $str .= "DESCRIPTION:" . $this->ical_escape_text($task->description). $this->lineEnding;
        }
        $str .= "URL;VALUE=URI:" . $sugar_config['site_url'].
            "/index.php?module=".$moduleName."&action=DetailView&record=". $task->id. $this->lineEnding;
        if ($task->status == 'Completed') {
            $str .= "STATUS:COMPLETED$this->lineEnding";
            $str .= "PERCENT-COMPLETE:100$this->lineEnding";
            $str .= "COMPLETED:" . $this->get_utc_date_time($due_date_time) . $this->lineEnding;
        } else if ($task->percent_complete) {
            $str .= "PERCENT-COMPLETE:" . $task->percent_complete . $this->lineEnding;
        }
        if ($task->priority == "Low") {
            $str .= "PRIORITY:9$this->lineEnding";
        } else if ($task->priority == "Medium") {
                $str .= "PRIORITY:5$this->lineEnding";
        } else if ($task->priority == "High") {
                $str .= "PRIORITY:1$this->lineEnding";
        }
        $str .= "END:VTODO$this->lineEnding";
        return $str;
    }

    // query and create the iCal Events for SugarCRM Meetings and Calls and
    // return the string
    function create_sugar_ical(&$user_bean,&$start_date_time,&$end_date_time, $dtstamp) {
        $str = '';
        global $DO_USER_TIME_OFFSET, $sugar_config, $current_user, $timedate;

        $acts_arr = CalendarActivity::get_activities($user_bean->id,
            false,
            $start_date_time,
            $end_date_time,
            'month');

        $hide_calls = false;
        if (!empty($_REQUEST['hide_calls']) && $_REQUEST['hide_calls'] == "true") {
            $hide_calls = true;
        }

        // loop thru each activity, get start/end time in UTC, and return iCal strings
        foreach($acts_arr as $act)
        {

            $event = $act->sugar_bean;
            if (!$hide_calls || ($hide_calls && $event->object_name != "Call")) {
                $str .= "BEGIN:VEVENT$this->lineEnding";
                $str .= "SUMMARY:" . $this->ical_escape_text($event->name) . $this->lineEnding;
                $str .= "DTSTART;TZID=" . $user_bean->getPreference('timezone') . ":" .
                        str_replace("Z", "", $timedate->tzUser($act->start_time, $current_user)->format(self::UTC_FORMAT)) . $this->lineEnding;
                $str .= "DTEND;TZID=" . $user_bean->getPreference('timezone') . ":" .
                        str_replace("Z", "", $timedate->tzUser($act->end_time, $current_user)->format(self::UTC_FORMAT)) . $this->lineEnding;
                $str .= "DTSTAMP:" . $dtstamp . $this->lineEnding;
                $str .= "DESCRIPTION:" . $this->ical_escape_text($event->description) . $this->lineEnding;
                $str .= "URL;VALUE=URI:" . $sugar_config['site_url'].
                    "/index.php?module=".$event->module_dir."&action=DetailView&record=". $event->id. $this->lineEnding;
                $str .= "UID:" . $event->id . $this->lineEnding;
                if ($event->object_name == "Meeting") {
                    $str .= "LOCATION:" . $this->ical_escape_text($event->location) . $this->lineEnding;
                    $eventUsers = $event->get_meeting_users();
                    $query = "SELECT contact_id as id from meetings_contacts where meeting_id='$event->id' AND deleted=0";
                    $eventContacts = $event->build_related_list($query, new Contact());
                    $eventAttendees = array_merge($eventUsers, $eventContacts);
                    if (is_array($eventAttendees)) {
                        foreach($eventAttendees as $attendee) {
                            if ($attendee->id != $user_bean->id && !empty($attendee->email1)) {
                                $str .= 'ATTENDEE;CN="'.$attendee->get_summary_text().'":MAILTO:'. $attendee->email1 . $this->lineEnding;
                            }
                        }
                    }
                }
                if ($event->object_name == "Call") {
                    $eventUsers = $event->get_call_users();
                    $eventContacts = $event->get_contacts();
                    $eventAttendees = array_merge($eventUsers, $eventContacts);
                    if (is_array($eventAttendees)) {
                        foreach($eventAttendees as $attendee) {
                            if ($attendee->id != $user_bean->id && !empty($attendee->email1)) {
                                $str .= 'ATTENDEE;CN="'.$attendee->get_summary_text().'":MAILTO:'. $attendee->email1 . $this->lineEnding;
                            }
                        }
                    }
                }
                if ($event->reminder_time > 0 && $event->status != "Held") {
                    $str .= "BEGIN:VALARM$this->lineEnding";
                    $str .= "TRIGGER:-PT" . $event->reminder_time/60 . "M$this->lineEnding";
                    $str .= "ACTION:DISPLAY$this->lineEnding";
                    $str .= "DESCRIPTION:" . $event->name . "$this->lineEnding";
                    $str .= "END:VALARM$this->lineEnding";
                }
                $str .= "END:VEVENT$this->lineEnding";
            }

        }

        require_once('include/TimeDate.php');
        $timedate = new TimeDate();
        $today = gmdate("Y-m-d");
        $today = $timedate->handle_offset($today, $timedate->dbDayFormat, false);
        
        $fromDate = gmdate("Y-m-d", $start_date_time);
        $fromDate = $timedate->handle_offset($fromDate, $timedate->dbDayFormat, false);

        require_once('modules/ProjectTask/ProjectTask.php');
        $where = "project_task.assigned_user_id='{$user_bean->id}' ".
            "AND (project_task.status IS NULL OR (project_task.status!='Deferred')) ".
            "AND (project_task.date_start IS NULL OR project_task.date_start <= '$today')
            AND project_task.date_start >= '$fromDate'";
        $seedProjectTask = new ProjectTask();
        $projectTaskList = $seedProjectTask->get_full_list("", $where);
        if (is_array($projectTaskList)) {
            foreach($projectTaskList as $projectTask) {
                $str .= $this->create_sugar_ical_todo($user_bean, $projectTask, "ProjectTask", $dtstamp);
            }
        }

        require_once('modules/Tasks/Task.php');
        $where = "tasks.assigned_user_id='{$user_bean->id}' ".
            "AND (tasks.status IS NULL OR (tasks.status!='Deferred')) ".
            "AND (tasks.date_start IS NULL OR tasks.date_start <= '$today')
            AND tasks.date_start >= '$fromDate'";
        $seedTask = new Task();
        $taskList = $seedTask->get_full_list("", $where);
        if (is_array($taskList)) {
            foreach($taskList as $task) {
                $str .= $this->create_sugar_ical_todo($user_bean, $task, "Tasks", $dtstamp);
            }
        }

        return $str;
    }

    public function getUserTimezone($current_user) {
        $gmtTZ = new DateTimeZone("UTC");
        $userTZName = TimeDate::userTimezone($current_user);
        if (!empty($userTZName)) {
            $tz = new DateTimeZone($userTZName);
        } else {
            $tz = $gmtTZ;
        }
        return $tz;
    }

    public function getDSTRange($current_user, $year)
    {
        $tz = $this->getUserTimezone($current_user);

        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $year_date = SugarDateTime::createFromFormat("Y", $year, $tz);
            $year_end = clone $year_date;
            $year_end->setDate((int) $year, 12, 31);
            $year_end->setTime(23, 59, 59);
            $year_date->setDate((int) $year, 1, 1);
            $year_date->setTime(0, 0, 0);

            $transitions = $tz->getTransitions($year_date->getTimestamp(), $year_end->getTimestamp());

            $idx = 0;
            while ((!$transitions[$idx]["isdst"]) && ($idx < count($transitions)))
                $idx ++;
            if (!$transitions[$idx]["isdst"])
            {
                // No DST transitions found
                return array();
            }
            $startTransition = $transitions[$idx];
            while ($transitions[$idx]["isdst"])
                $idx ++;
            $endTransition = $transitions[$idx];
        } else {
            $transitions = $tz->getTransitions();

            $idx = 0;
            while (! $transitions[$idx]["isdst"] || intval(substr($transitions[$idx]["time"], 0, 4)) < intval(date("Y")))
                $idx ++;
            $startTransition = $transitions[$idx];
            while ($transitions[$idx]["isdst"] || intval(substr($transitions[$idx]["time"], 0, 4)) < intval(date("Y")))
                $idx ++;
            $endTransition = $transitions[$idx];
        }
        return array("start" => $startTransition, "end" => $endTransition);
    }

    function get_timezone_string() {
        global $current_user, $timedate;
        $timezoneName = $current_user->getPreference('timezone');

        $gmtTZ = new DateTimeZone("UTC");
        $tz = $this->getUserTimezone($current_user);
        $dstRange = $this->getDSTRange($current_user, date('Y'));

        $dstOffset = 0;
        $gmtOffset = 0;
        if (array_key_exists('start', $dstRange)) {
            $dstOffset = ($dstRange['start']['offset'] / 60);
            $startDate = new DateTime("@" . $dstRange["start"]["ts"], $gmtTZ);
            $startstamp = strtotime($timedate->asDb($startDate));
        }
        if (array_key_exists('end', $dstRange)) {
            $gmtOffset = ($dstRange['end']['offset'] / 60);
            $endDate = new DateTime("@" . $dstRange["end"]["ts"], $gmtTZ);
            $endstamp = strtotime($timedate->asDb($endDate));
        }

        $timezoneString = "BEGIN:VTIMEZONE$this->lineEnding";
        $timezoneString .= "TZID:" . $timezoneName . $this->lineEnding;
        $timezoneString .= "X-LIC-LOCATION:" . $timezoneName . $this->lineEnding;

        $timezoneString .= "BEGIN:DAYLIGHT$this->lineEnding";
        $timezoneString .= "TZOFFSETFROM:" . $this->convert_min_to_hr_min($gmtOffset) . $this->lineEnding;
        $timezoneString .= "TZOFFSETTO:" . $this->convert_min_to_hr_min($dstOffset) . $this->lineEnding;
        $timezoneString .= "DTSTART:" . str_replace("Z", "", $this->get_utc_time($startstamp)) . $this->lineEnding;
        $timezoneString .= "END:DAYLIGHT$this->lineEnding";

        $timezoneString .= "BEGIN:STANDARD$this->lineEnding";
        $timezoneString .= "TZOFFSETFROM:" . $this->convert_min_to_hr_min($dstOffset) . $this->lineEnding;
        $timezoneString .= "TZOFFSETTO:" . $this->convert_min_to_hr_min($gmtOffset) . $this->lineEnding;
        $timezoneString .= "DTSTART:" . str_replace("Z", "", $this->get_utc_time($endstamp)) . $this->lineEnding;
        $timezoneString .= "END:STANDARD$this->lineEnding";

        $timezoneString .= "END:VTIMEZONE$this->lineEnding";

        return $timezoneString;
    }

    // return a iCal vcal string
    function get_vcal_ical(&$user_focus, $num_months) {
           global $current_user, $timedate;
           $current_user = $user_focus;

           $cal_name = $user_focus->first_name. " ". $user_focus->last_name;

           $str = "BEGIN:VCALENDAR$this->lineEnding";
           $str .= "VERSION:2.0$this->lineEnding";
           $str .= "METHOD:PUBLISH$this->lineEnding";
           $str .= "X-WR-CALNAME:$cal_name (SugarCRM)$this->lineEnding";
           $str .= "PRODID:-//SugarCRM//SugarCRM Calendar//EN$this->lineEnding";
           $str .= $this->get_timezone_string();
           $str .= "CALSCALE:GREGORIAN$this->lineEnding";

           $now_date_time = $timedate->getNow(true);

           // get date 2 months from start date
            global $sugar_config;
            $timeOffset = 2;
            if (isset($sugar_config['vcal_time']) && $sugar_config['vcal_time'] != 0 && $sugar_config['vcal_time'] < 13)
            {
                $timeOffset = $sugar_config['vcal_time'];
            }
            if (!empty($num_months)) {
                $timeOffset = $num_months;
            }
           $start_date_time = $now_date_time->get("-$timeOffset months");
           $end_date_time = $now_date_time->get("+$timeOffset months");

           // get UTC time format
           $utc_now_time = $this->get_utc_date_time($now_date_time);

           $str .= $this->create_sugar_ical($user_focus,$start_date_time,$end_date_time,$utc_now_time);

           $str .= "DTSTAMP:" . $utc_now_time . $this->lineEnding;
           $str .= "END:VCALENDAR$this->lineEnding";

           return $str;
    }

}

?>