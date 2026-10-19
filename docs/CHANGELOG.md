# Changelog

## 19.10.2026 - Version 2.0

### Neu
- Mindestversion ist jetzt IP-Symcon 9.0. Achtung: Die Bibliothek nutzt jetzt `IPSModuleStrict`, ältere Versionen von IP-Symcon werden nicht mehr unterstützt.
- Variablen nutzen die neuen Darstellungen von IP-Symcon statt der alten Variablenprofile. Achtung: Die alten Profile (`OCTO.*`, `OctoPrint.MB`, `OcotoPrint.Temperatures`) bleiben im System stehen und können gelöscht werden.
- Neue Instanz OctoTile: Cockpit-Kachel für die Kachel-Visualisierung mit Status, Fortschritt, Zeiten, Z-Höhe und Temperaturverlauf. Die Kachel startet, pausiert und bricht Drucke ab (mit Rückfrage) und stellt die Solltemperaturen ein. Achtung: Wer die Kachel sehen darf, kann den Druck steuern.
- OctoConnect: Der API Key lässt sich per Button "API Key anfordern" von OctoPrint holen (Application Keys).
- OctoConnect: Die Anmeldung am WebSocket erfolgt nur mit dem API Key, Benutzer und Passwort entfallen. Achtung: Bestehende Instanzen brauchen einen eingetragenen API Key, sonst kommen keine Statusdaten mehr an. Das gilt auch für die OctoDiscovery.
- OctoConnect: Der Instanzstatus zeigt, wenn die API URL oder der API Key fehlt, OctoPrint nicht erreichbar ist oder der Key abgelehnt wird. Fehler bei Befehlen stehen als Warnung im Meldungsfenster.
- OctoConnect: Einstellbares Aktualisierungsintervall, Terminal-Logs werden nicht mehr übertragen.
- OctoConnect: Nach einem Neustart von OctoPrint oder dem Ablauf der Session meldet sich das Modul von selbst neu an.
- OctoConnect: Die Version von OctoPrint steht in der Zusammenfassung der Instanz.
- OctoCurrentState: Neue Variablen "Letztes Ereignis" und "Zeitpunkt des letzten Ereignisses" (zum Beispiel `PrintDone`, `PrintFailed`).
- OctoPrintjob: Neue Variablen für Z-Höhe, geschätzte Druckzeit, Filamentlänge, Filamentvolumen und Dateidatum.
- OctoTemperatures: Optional ein zweiter Extruder, die Kammer-Solltemperatur ist jetzt einstellbar.
- OctoConnectionHandling: Port und Baudrate werden aus OctoPrint geladen und beim Verbinden verwendet.

### Fixes
- OctoPrintjob: Die Druckzeit wird in Sekunden gespeichert und als Dauer angezeigt, bisher war es eine Uhrzeit, die nach 24 Stunden falsch lief. Achtung: Archivierte Werte der Variable "Druckzeit" aus früheren Versionen sind Zeitstempel und passen nicht zu den neuen Werten.
- OctoConnect: Die Funktion heißt jetzt `OCTO_OctoPrintLogin()`. Achtung: Der alte Name `OCTO_OtcoPrintLogin()` bleibt als Alias erhalten, sollte aber nicht mehr verwendet werden.
- OctoConnectionHandling: Die Funktion heißt jetzt `OCTO_Disconnect()`. Achtung: Der alte Name `OCTO_Disonnect()` bleibt als Alias erhalten.
- OctoConfigurator und OctoDiscovery werden wieder geladen.
- OctoCurrentState: Fehlende Statuswerte führen nicht mehr zu Fehlern. Die Variable zum Pausieren heißt bei neuen Instanzen "Pause / Fortsetzen".
- OctoConnectionHandling: Die Verbindungseinstellungen werden korrekt ausgelesen.
- OctoConnect: Fehlerhafte Antworten und Verbindungsfehler führen nicht mehr zu Fehlern.
- OctoPrintjob: Der Anzeigename wird gesetzt, fehlende Werte führen nicht mehr zu Fehlern.
- OctoDiscovery: Die Suche funktioniert auch bei anderer Reihenfolge der mDNS-Angaben.
- OctoTemperatures: Leere Temperaturdaten führen nicht mehr zu Fehlern.
- Alle deutschen Übersetzungen überarbeitet und vervollständigt.
