# OctoSystem
Mit dieser Instanz kann das System von OctoPrint bedient werden: Herunterfahren, Neustart des Systems und Neustart von OctoPrint. Sie wird normalerweise über den OctoConfigurator angelegt.

## Inhaltsverzeichnis
- [OctoSystem](#octosystem)
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
Systemsteuerung | `SystemControl` | Text | Steuerbar: `shutdown` (Herunterfahren), `reboot` (Neustart des Systems) oder `restart` (Neustart von OctoPrint). Die Befehle müssen in OctoPrint eingerichtet sein.

## 3. Funktionen
Es gibt keine öffentlichen Funktionen.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
