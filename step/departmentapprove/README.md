# moodle-lifecyclestep_departmentapprove

Dieses Plugin soll für Open Moodle einen Schritt innerhalb des lifecycle-Plugins 
zum Einholen der Kursbestätigung vom Dekanat realisieren.

Fragen: 

* Wie soll der Dekan bestätigen?
- formlos per Mail als Antwort auf die Anforderungsmail? oder
- durch Klicken auf einen Link, der in der Mail gesendet wird (mit Verlängern/nicht-Verlängern-Antwort)

Torsten G: durch formlose Mail, die anschließend von den Admins ausgewertet werden muss


* was soll passieren, wenn der Kurs nicht bestätigt wird (Ablauf Timer oder Ablehnung durch Dekanat)?
=> den Lehrenden informieren (ggf. telefonisch abklären, wie weiter vorgegangen wird)
=> den Kurs irgendwie sperren (unsichtbar schalten)
=> den Lehrenden aus dem Kurs entfernen


* Was sollte bei einer Bestätigung passieren?
=> Zeitpunkt speichern, damit man weiß wann das nächste Mal gefragt werden muss
=> ansonsten den Workflow beenden (rollback?)






