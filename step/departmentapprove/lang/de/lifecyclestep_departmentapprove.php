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
$string['course_approved'] = 'Kurs bestätigt';
$string['course_not_approved'] = 'Kurs nicht bestätigt';
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
$string['email_subject_default'] = 'Kursbestätigung erforderlich';
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

$string['departmentapproval'] = 'Erneuerung der Kursgenehmigung';
$string['teachers'] = 'Lehrende';
$string['approve'] = 'Kurs genehmigen';
$string['reallyreject'] = 'Soll der Kurs "{$a}" wirklich nicht mehr weiter angeboten werden?';
$string['reject'] = 'Kurs nicht weiter genehmigen';
$string['rejectedbydepartment'] = 'Die Ablehnung der Kursgenehmigung wurde weitergeleitet.';
$string['approvedbydepartment'] = 'Der Kurs wurde genehmigt und kann nun weiter angeboten werden.';
$string['invalidtoken'] = 'Der Antrag wurde bereits bearbeitet oder der Link ist ungültig.';
$string['invalidcourse'] = 'Der Kurs wurde nicht gefunden.';
$string['admin_subject_rejected'] = 'Open Moodle Kursgenehmigung abgelehnt';
$string['admin_subject_approved'] = 'Open Moodle Kursgenehmigung erteilt';
$string['admin_body_rejected'] = 'Der folgende Kurs darf in Open Moodle NICHT mehr angeboten werden. 
Die zuständige Einrichtung ({$a->contact}) hat die Genehmigung soeben widerrufen.';
$string['admin_body_approved'] = 'Der folgende Kurs wird in Open Moodle fortgeführt. 
Die Genehmigung der Einrichtung wurde soeben erteilt. ';

$string['coursereasonforrejecting'] = 'Begründung für das Ablehnen der Genehmigung';
$string['coursereasonforrejectingemail'] = 'Die Mitteilung wird an die Person gesendet, die für den Kurs verantwortlich ist';

$string['contact_customfield'] = 'Kursfeld mit der Mail der Einrichtung';
$string['contact_customfield_help'] = 'Kursfeld sollte Datentyp Text haben und die E-Mail-Adresse des Kontakts für die übergeordnete Einrichtung haben';

$string['timestamp_customfield'] = 'Kursfeld mit Zeitstempel der Bestätigung';
$string['timestamp_customfield_help'] = 'Kursfeld, in dem der Zeitpunkt der Bestätigung eingetragen werden soll';