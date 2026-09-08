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
 * Lang strings for duplicate step
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['createparents'] = 'Hierarchie berücksichtigen';
$string['createparents_help'] = 'Wenn der alte Kurs unter A->B->C gespeichert und D die Zielkategorie ist, dann wird der Kurs unter (A->)D->B->C erzeugt.';
$string['maxdepth'] = 'Maximale Anzahl Quell-Kursbereiche';
$string['maxdepth_help'] = 'Gibt an, wieviele Ebenen der Ausgangskategorie in die Zielkategorie übernommen werden sollen (leer/0 = alle).';
$string['move'] = 'neuen Kurs verschieben';
$string['move_help'] = 'Wenn nicht angewählt, wird der neue Kurs in dem Kursbereich erzeugt, in dem sich auch der alte Kurs befindet. Anderenfalls kann er gleich am richtigen Platz erzeugt werden.';
$string['notoplevel'] = 'Top-level-Ausgangsbereich nicht erzeugen (z.B. wegen Archiv)';
$string['notoplevel_help'] = 'Wenn der alte Kurs unter A->B->C gespeichert und D die Zielkategorie ist, dann wird der Kurs unter D->B->C erzeugt. Dies ist sinnvoll, wenn der Ausgangsbereich ein Archiv ist.';
$string['plugindescription'] = 'In diesem Schritt wird jeder getriggerte Kurs dupliziert und damit weitergearbeitet.';
$string['pluginname'] = 'erweiterter Kurs-Duplizieren-Schritt';
$string['privacy:metadata'] = 'Dieses Subplugin speichert keine persönlichen Daten.';
$string['targetcategory'] = 'Zielkursbereich';
$string['targetcategory_help'] = 'Kursbereich, in dem der neue Kurs erstellt werden soll';
