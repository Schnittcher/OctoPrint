# OctoTile
Diese Instanz ist ein Cockpit für den Drucker in der Kachel-Visualisierung: Sie zeigt Status, Fortschritt, Dateiname, Rest- und Druckzeit, Z-Höhe, alle Temperaturen mit Verlauf und das letzte Ereignis, und sie bedient den Druck und die Solltemperaturen. Sie wird normalerweise über den OctoConfigurator angelegt und liest dieselben Daten wie OctoCurrentState direkt von OctoConnect.

## Inhaltsverzeichnis
- [OctoTile](#octotile)
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

Die Kachel übernimmt Schrift, Größe und Farben der Visualisierung. Sie hängt nicht von den Einstellungen anderer Instanzen ab: Sie zeigt jeden Temperatursensor, den OctoPrint meldet (Düse, zweite Düse, Bett, Kammer).

**Bedienung**
* **Starten:** Startet die ausgewählte Datei. Der Knopf erscheint, wenn der Drucker bereit ist und eine Datei ausgewählt ist.
* **Pausieren und Fortsetzen:** Je nach Zustand des Drucks.
* **Abbrechen:** Fragt zur Sicherheit nach. Ohne Antwort verschwindet die Rückfrage nach 8 Sekunden.
* **Solltemperatur:** Je Sensor die Knöpfe −5, +5 und Aus. Die Grenzen sind 0 bis 400 °C.

Wer die Kachel sehen darf, kann den Druck steuern. Vergeben Sie die Rechte an der Kachel entsprechend.

Der Temperaturverlauf zeigt die letzten Messpunkte. Beim Öffnen der Kachel liefert das Modul bis zu 240 Punkte, danach wächst der Verlauf mit jedem Update.

Unter IP-Symcon 9.0 gibt es den Vollbildtyp der Kachel nicht, dort bleibt die maximierte Ansicht leer. Ab 9.1 wird die Kachel im Vollbild dargestellt.

## 2. Variablen
Diese Instanz legt keine Variablen an. Nach einem Neustart von IP-Symcon füllt sich die Kachel beim nächsten Update von OctoPrint wieder, der Temperaturverlauf beginnt dann neu.

## 3. Funktionen
Es gibt keine öffentlichen Funktionen.

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
