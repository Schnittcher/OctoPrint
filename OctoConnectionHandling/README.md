# OctoConnectionHandling
Mit dieser Instanz wird die Verbindung zwischen OctoPrint und dem 3D-Drucker hergestellt und getrennt. Sie wird normalerweise über den OctoConfigurator angelegt.

## Inhaltsverzeichnis
- [OctoConnectionHandling](#octoconnectionhandling)
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

Die Auswahl für Ports und Baudraten wird beim Übernehmen der Konfiguration von OctoPrint geladen.

## 2. Variablen

Variable | Ident | Typ | Beschreibung
------------ | ------------ | ------------ | ----------------
Ports | `Ports` | Text | Serieller Port des Druckers. Steuerbar (Aufzählung aus den Ports von OctoPrint). Wird beim Verbinden verwendet. Ist die Variable leer, entscheidet OctoPrint.
Baudraten | `Baudrates` | Text | Baudrate der Verbindung. Steuerbar (Aufzählung). Wird beim Verbinden verwendet, ist sie leer, entscheidet OctoPrint.
Verbindung | `State` | Ja/Nein | Zeigt, ob OctoPrint mit dem Drucker verbunden ist. Steuerbar: Einschalten verbindet, Ausschalten trennt.

## 3. Funktionen

`void OCTO_Connect(integer $InstanzID);`
Verbindet OctoPrint mit dem Drucker. Port und Baudrate stammen aus den Variablen `Ports` und `Baudrates`.

`void OCTO_Disconnect(integer $InstanzID);`
Trennt die Verbindung zum Drucker. `OCTO_Disonnect()` bleibt als veralteter Alias erhalten.

`boolean OCTO_getConnectSettings(integer $InstanzID);`
Lädt die verfügbaren Ports und Baudraten von OctoPrint und aktualisiert die Auswahl. Rückgabe `false`, wenn OctoPrint nicht antwortet.

```php
OCTO_Connect(12345);
```

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
