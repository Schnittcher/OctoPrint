# OctoDiscovery
Diese Instanz sucht per mDNS (Zeroconf) nach OctoPrint-Servern im Netzwerk und legt für einen gefundenen Server die benötigten Instanzen an: den OctoConfigurator, OctoConnect und den WebSocket Client. Voraussetzung ist eine Zeroconf-Instanz in IP-Symcon.

## Inhaltsverzeichnis
- [OctoDiscovery](#octodiscovery)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
API Key | API Key des OctoPrint-Servers. Er wird beim Anlegen in die Instanz OctoConnect übernommen. Er kann dort auch später über den Button **API Key anfordern** geholt werden.

Die Liste zeigt die gefundenen Server mit IP-Adresse, Name, Version und UUID. Über **Erstellen** werden die Instanzen angelegt. Danach im OctoConfigurator die gewünschten Instanzen (Status, Druckjob, Temperaturen, Verbindung, System) erstellen.

## 2. Variablen
Diese Instanz legt keine Variablen an.

## 3. Funktionen

`array OCTO_mDNSDiscoverOctoPrintServer(integer $InstanzID);`
Sucht OctoPrint-Server per mDNS und gibt für jeden Treffer IP-Adresse, Name, Port, Version und UUID zurück. Ist keine Zeroconf-Instanz vorhanden, ist das Ergebnis leer.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
