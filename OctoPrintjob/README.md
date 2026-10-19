# OctoPrintjob
Diese Instanz zeigt die Daten des aktuellen Druckjobs: Datei, Fortschritt, Zeiten, Z-Höhe und Filamentverbrauch. Sie wird normalerweise über den OctoConfigurator angelegt.

## Inhaltsverzeichnis
- [OctoPrintjob](#octoprintjob)
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
Dateiname | `Filename` | Text | Name der Druckdatei.
Pfad | `Path` | Text | Pfad der Datei in OctoPrint.
Anzeigename | `Display` | Text | Anzeigename der Datei. Fehlt er, wird der Pfad verwendet.
Speicherort | `Origin` | Text | Speicherort der Datei, zum Beispiel `local`.
Größe | `Size` | Kommazahl (MB) | Größe der Datei.
Fortschritt | `Completion` | Ganzzahl (%) | Fortschritt des Drucks.
Dateiposition | `Filepos` | Kommazahl (MB) | Aktuelle Position in der Datei.
Druckzeit | `Printtime` | Ganzzahl (Sekunden) | Bisherige Druckzeit, dargestellt als Dauer.
Restzeit | `PrinttimeLeft` | Ganzzahl (Sekunden) | Geschätzte Restzeit, dargestellt als Dauer.
Geschätzte Druckzeit | `PrinttimeEstimated` | Ganzzahl (Sekunden) | Von OctoPrint berechnete Gesamtdauer der Datei.
Z-Höhe | `CurrentZ` | Kommazahl (mm) | Aktuelle Höhe des Druckkopfs.
Filamentlänge | `FilamentLength` | Kommazahl (m) | Benötigte Filamentlänge der Datei, Summe über alle Extruder.
Filamentvolumen | `FilamentVolume` | Kommazahl (cm³) | Benötigtes Filamentvolumen der Datei, Summe über alle Extruder.
Dateidatum | `FileDate` | Datum/Uhrzeit | Änderungsdatum der Datei.

Alle Variablen sind nur lesbar.

## 3. Funktionen
Es gibt keine öffentlichen Funktionen.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
