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








## Behat-Tests mit Mailversand (Erinnerungsmails)

Das Szenario „Reminder mail is repeated to department after each expired deadline“
(Tag `@behat_email`) prüft die tatsächlich versendeten Mails. Dafür wird ein
Mail-Catcher (Mailpit) benötigt. Ist keiner konfiguriert, wird das Szenario
übersprungen (nicht als Fehler gewertet), und Moodle verschickt während Behat
gar keine Mails.

### 1. Mailpit starten

Mailpit fängt alle Mails ab, statt sie zuzustellen (SMTP auf Port 1025, Web-Oberfläche/API auf Port 8025):

```
docker run -d --name mailpit -p 1025:1025 -p 8025:8025 axllent/mailpit
```

Die abgefangenen Mails lassen sich unter http://localhost:8025 ansehen.

### 2. `config.php` erweitern

Die Konstanten müssen **vor** `require_once(__DIR__ . '/lib/setup.php');` stehen:

```php
define('TEST_EMAILCATCHER_MAIL_SERVER', 'localhost:1025');   // SMTP von Mailpit
define('TEST_EMAILCATCHER_API_SERVER', 'http://localhost:8025/'); // API von Mailpit, mit abschließendem "/"

oder von mir ermittelt:
#  define('TEST_EMAILCATCHER_MAIL_SERVER', 'mailpit:1025');
#  define('TEST_EMAILCATCHER_API_SERVER', 'http://mailpit:8025/_/mail/');


```

Hinweise:
* `TEST_EMAILCATCHER_MAIL_SERVER` wird vom **Webserver/PHP** benutzt (Moodle setzt damit
  `smtphosts` und hebt `noemailever` für die Behat-Seite auf).
  `TEST_EMAILCATCHER_API_SERVER` wird vom **Behat-Prozess** benutzt, um die Mails abzufragen.
  Laufen Webserver oder Behat in Docker/WSL, müssen die Hostnamen aus Sicht des jeweiligen
  Prozesses stimmen (z. B. `mailpit:1025` im Docker-Netz statt `localhost:1025`).
* Der abschließende `/` bei der API-URL ist wichtig, da die API-Pfade relativ angehängt werden.

### 3. Behat-Umgebung neu initialisieren und Test starten

```
php admin/tool/behat/cli/init.php
vendor/bin/behat --config <behat_dataroot>/behatrun/behat/behat.yml --tags=@lifecyclestep_departmentapprove
```

Nur den Mailtest ausführen:

```
vendor/bin/behat --config <behat_dataroot>/behatrun/behat/behat.yml --tags="@lifecyclestep_departmentapprove&&@behat_email"
```

Funktioniert die Konfiguration grundsätzlich, läuft auch der Core-Test `lib/tests/behat/email.feature`
(Tag `@behat_test`) durch.

### Achtung

* Alle Tests teilen sich ein Mailpit-Postfach. Bei parallelen Behat-Läufen (`--parallel`)
  können sich die Mailzählungen gegenseitig stören. Den Mailtest daher nicht parallel ausführen.
* Das Szenario leert zu Beginn das Postfach (`I empty the email inbox`) und Moodle leert es
  nach jedem `@behat_email`-Szenario automatisch.
