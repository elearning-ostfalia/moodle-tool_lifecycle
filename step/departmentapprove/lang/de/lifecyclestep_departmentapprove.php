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
 * Lang strings for email step
 *
 * @package lifecyclestep_departmentapprove
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action_prevented_deletion'] = '{$a} verhinderte Löschung';
$string['course_approved'] = 'Kursgenehmigung verlängert';
$string['course_not_approved'] = 'Kursangebot soll eingestellt werden';
$string['email_content'] = 'Vorlage für Emails in Klartext';
$string['email_content_default'] = 'Sehr geehrte Dame, sehr geehrter Herr,

in Open Moodle gibt es einen oder mehrere Kurse aus Ihrem Verantwortungsbereich,
die eine Bestätigung erfordern, damit sie auch in Zukunft angeboten werden können.

Bitte prüfen Sie, ob folgende Kurse in der nächsten Zeit angeboten werden sollen:

##courses##

Senden Sie Ihre Antwort an:
xxx@ostfalia.de

Mit freundlichen Grüßen aus dem Rechenzentrum
';
$string['email_content_help'] = 'Stellen Sie die Vorlage für Emails ein. (in Klartext, alternativ können Sie auch die HTML-Vorlage unten einstellen.)' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link##'
        . '<br>' . 'Betroffene Kurse: ##courses##'
        . '<br>' . 'Kurznamen betroffener Kurse: ##shortcourses##'
        . '<br>' . 'Empfänger-Emaill-Adresse: ##sender##'
       . '</p>';
$string['email_content_html'] = 'HTML-Vorlage für Emails';

$string['email_content_html_default'] = 'Sehr geehrte Dame, sehr geehrter Herr,<br><br>
in Open Moodle gibt es einen oder mehrere Kurse, die eine Bestätigung des Dekanats erfordern.';
$string['email_content_html_help'] = 'Stellen sie die HTML-Vorlage für Emails ein. (in HTML-Format; falls gesetzt, wird es an Stelle der Klartext-Vorlage benutzt!)' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link-html##'
        . '<br>' . 'Betroffene Kurse: ##courses-html##'
        . '<br>' . 'Empfänger mit mailto-Link: ##sender-html##'
        . '</p>';



$string['email_responsetimeout'] = 'Zeit, die der Nutzer hat, um zu reagieren';
$string['email_subject'] = 'Betreffvorlage';
$string['email_subject_default'] = 'Verlängerung der Kursgenehmigung erforderlich';
$string['email_subject_help'] = 'Stellen Sie die Vorlage für den Emailbetreff ein.' . '<p>' . 'Sie können die folgenden Platzhalter benutzen:'
        . '<br>' . 'Vorname des Empfängers: ##firstname##'
        . '<br>' . 'Nachname des Empfängers: ##lastname##'
        . '<br>' . 'Link zur Antwortseite: ##link##'
        . '<br>' . 'Betroffene Kurse: ##courses##'
        . '<br>' . 'Kurznamen betroffener Kurse: ##shortcourses-html##'
        . '</p>';
$string['plugindescription'] = 'In diesem Schritt wird das Dekanat (o.ä.) um Bestätigung eines getriggerten Kurses gebeten.';
$string['pluginname'] = 'Dekanats-Bestätigungsschritt';
$string['email_sender'] = 'Reply-to Email-Adresse';
$string['email_sender_help'] = 'Email-Adresse, an die geantwortet werden soll.';

$string['status_message_requiresattention'] = 'Kurs ist für Bestätigung des Dekanats (oder anderem Zuständigem) vorgemerkt';

$string['departmentapproval'] = 'Verlängerung der Kursgenehmigung';
$string['teachers'] = 'Lehrende';
$string['approve'] = 'Genehmigung verlängern';
$string['reallyreject'] = 'Soll das Kursangebot "{$a}" wirklich eingestellt werden?';
$string['reject'] = 'Kursangebot einstellen';
$string['rejectedbydepartment'] = 'Die Mitteilung, dass das Kursangebot eingestellt werden soll, wurde weitergeleitet.';
$string['approvedbydepartment'] = 'Die Kursgenehmigung wurde verlängert. Der Kurs kann weiter angeboten werden.';
$string['invalidtoken'] = 'Der Antrag wurde bereits bearbeitet oder der Link ist ungültig.';
$string['invalidcourse'] = 'Der Kurs wurde nicht gefunden.';
$string['alreadyapproved'] = 'Die Genehmigung für diesen Kurs wurde bereits verlängert. Eine erneute Bearbeitung ist nicht möglich.';
$string['alreadyrejected'] = 'Für diesen Kurs wurde bereits mitgeteilt, dass das Kursangebot eingestellt werden soll. Eine erneute Bearbeitung ist nicht möglich.';
$string['admin_subject_rejected'] = 'Open Moodle Kursangebot einstellen';
$string['admin_subject_approved'] = 'Open Moodle Kursgenehmigung verlängert';
$string['admin_subject_no_reaction'] = 'Open Moodle Kursgenehmigung: keine Rückmeldung der Einrichtung';
$string['admin_body_no_reaction'] = 'Die zuständige Einrichtung ({$a->contact}) hat innerhalb der Frist nicht auf die Anfrage zur Verlängerung der Kursgenehmigung reagiert. Soeben wurde eine Erinnerung an die Einrichtung verschickt, der Link bleibt weiterhin gültig. Erfolgt bis zum {$a->nextdeadline} keine Rückmeldung, wird eine weitere Erinnerung verschickt. Betroffen ist der folgende Kurs:';
$string['reminder_subject_prefix'] = 'Erinnerung: ';
$string['admin_body_rejected'] = 'Wir möchten Sie darüber informieren, dass der folgende Kurs in Open Moodle nicht weiter angeboten werden kann. 
Die zuständige Einrichtung ({$a->contact}) hat soeben mitgeteilt, dass das Kursangebot eingestellt werden soll.';
$string['admin_body_approved'] = 'Der folgende Kurs kann in Open Moodle fortgeführt werden. 
Die zuständige Einrichtung hat die Genehmigung soeben verlängert. ';

$string['coursereasonforrejecting'] = 'Begründung für die Einstellung des Kursangebots';
$string['coursereasonforrejectingemail'] = 'Die Mitteilung wird an die Person gesendet, die für den Kurs verantwortlich ist';

$string['contact_customfield'] = 'Kursfeld mit der Mail der Einrichtung';
$string['contact_customfield_help'] = 'Kursfeld sollte Datentyp Text haben und die E-Mail-Adresse des Kontakts für die übergeordnete Einrichtung haben';

$string['timestamp_customfield'] = 'Kursfeld mit Zeitstempel der Bestätigung';
$string['timestamp_customfield_help'] = 'Kursfeld, in dem der Zeitpunkt der Bestätigung eingetragen werden soll';
$string['requestdata'] = 'Angaben bei Kursbeantragung ({$a})';
$string['req_withcosts'] = 'Kostenpflichtiges Angebot';
$string['req_partners'] = 'Externe Partner';
$string['req_foreign'] = 'Externe mit Hauptwohnsitz außerhalb Deutschlands';
$string['req_noneu'] = 'Externe mit Hauptwohnsitz außerhalb der EU';
$string['req_countries'] = 'Länder';
