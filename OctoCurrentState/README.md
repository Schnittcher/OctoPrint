# OctoCurrentState
Diese Instanz zeigt den aktuellen Status von OctoPrint und des Druckers, das letzte Ereignis (zum Beispiel "Druck fertig") und erlaubt, einen Druck zu starten, zu pausieren und abzubrechen. Sie wird normalerweise über den OctoConfigurator angelegt.

## Inhaltsverzeichnis
- [OctoCurrentState](#octocurrentstate)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
UUID | Kennung des OctoPrint-Servers. Wird vom OctoConfigurator gesetzt und ordnet die Instanz dem Server zu. Bei einer von Hand angelegten Instanz bleibt sie leer.

## 2. Variablen

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Aktueller Status | `CurrentState` | Text | Statustext von OctoPrint, zum Beispiel "Printing".
Betriebsbereit | `Operational` | Ja/Nein | Der Drucker ist betriebsbereit.
Druckt | `Printing` | Ja/Nein | Ein Druck läuft.
Wird abgebrochen | `Cancelling` | Ja/Nein | Der Druck wird gerade abgebrochen.
Wird pausiert | `Pausing` | Ja/Nein | Der Druck wird gerade pausiert.
Wird fortgesetzt | `Resuming` | Ja/Nein | Der Druck wird gerade fortgesetzt.
Wird beendet | `Finishing` | Ja/Nein | Der Druck wird gerade beendet.
Geschlossen oder Fehler | `ClosedOrError` | Ja/Nein | Die Verbindung zum Drucker ist geschlossen oder gestört.
Fehler | `Error` | Ja/Nein | Der Drucker meldet einen Fehler.
Pausiert | `Paused` | Ja/Nein | Der Druck ist pausiert.
Bereit | `Ready` | Ja/Nein | Der Drucker ist bereit für einen Druck.
SD-Karte bereit | `SDReady` | Ja/Nein | Die SD-Karte des Druckers ist bereit.
Letztes Ereignis | `LastEvent` | Text | Name des letzten Ereignisses von OctoPrint: `Startup`, `Shutdown`, `Connected`, `Disconnected`, `Error`, `FileSelected`, `PrintStarted`, `PrintDone`, `PrintFailed`, `PrintCancelled`, `PrintPaused` oder `PrintResumed`. Eignet sich als Auslöser für Benachrichtigungen.
Zeitpunkt des letzten Ereignisses | `LastEventTime` | Datum/Uhrzeit | Wann das letzte Ereignis eintraf.
Starten | `ActionStart` | Text | Steuerbar: `start`, `restart` oder `cancel`. Wirkt auf den ausgewählten Druckjob.
Pause / Fortsetzen | `ActionPausing` | Text | Steuerbar: `pause` oder `resume`.

Hinweis: Bei Instanzen aus früheren Versionen heißt die Variable `ActionPausing` weiterhin "Pausieren". Maßgeblich ist der Ident.

## 3. Funktionen
Es gibt keine öffentlichen Funktionen. Die Steuerung erfolgt über die Variablen `ActionStart` und `ActionPausing`.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
