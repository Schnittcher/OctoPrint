# OctoTemperatures
Diese Instanz zeigt die Temperaturen von Extruder, Bett und Kammer und setzt die Solltemperaturen. Sie wird normalerweise über den OctoConfigurator angelegt.

## Inhaltsverzeichnis
- [OctoTemperatures](#octotemperatures)
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
Extruder-0-Temperaturen | Legt die Variablen für den Extruder 0 an. Standard: an.
Extruder-1-Temperaturen | Legt die Variablen für den Extruder 1 an. Standard: aus.
Bett-Temperaturen | Legt die Variablen für das Bett an. Standard: an.
Kammer-Temperaturen | Legt die Variablen für die Kammer an. Standard: aus.

## 2. Variablen
Die Variablen entstehen je nach Auswahl in der Konfiguration. Alle Werte in °C.

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Extruder 0 Ist-Temperatur | `Tool0ActualTemperature` | Kommazahl (°C) | Ist-Temperatur des Extruders 0.
Extruder 0 Soll-Temperatur | `Tool0TargetTemperature` | Kommazahl (°C) | Solltemperatur des Extruders 0. Steuerbar (0 bis 400 °C, Schritt 1).
Extruder 1 Ist-Temperatur | `Tool1ActualTemperature` | Kommazahl (°C) | Ist-Temperatur des Extruders 1.
Extruder 1 Soll-Temperatur | `Tool1TargetTemperature` | Kommazahl (°C) | Solltemperatur des Extruders 1. Steuerbar (0 bis 400 °C, Schritt 1).
Bett Ist-Temperatur | `BedActualTemperature` | Kommazahl (°C) | Ist-Temperatur des Betts.
Bett Soll-Temperatur | `BedTargetTemperature` | Kommazahl (°C) | Solltemperatur des Betts. Steuerbar (0 bis 400 °C, Schritt 1).
Kammer Ist-Temperatur | `ChamberActualTemperature` | Kommazahl (°C) | Ist-Temperatur der Kammer.
Kammer Soll-Temperatur | `ChamberTargetTemperature` | Kommazahl (°C) | Solltemperatur der Kammer. Steuerbar (0 bis 400 °C, Schritt 1).

Wird eine Auswahl abgeschaltet, entfernt die Instanz die zugehörigen Variablen. Bei Instanzen aus früheren Versionen heißen die Variablen weiter so, wie sie angelegt wurden. Maßgeblich ist der Ident.

## 3. Funktionen
Es gibt keine öffentlichen Funktionen. Die Solltemperaturen werden über die steuerbaren Variablen gesetzt.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
