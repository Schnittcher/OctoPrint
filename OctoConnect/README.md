# OctoConnect
Diese Splitter-Instanz stellt die Verbindung zum OctoPrint-Server her. Sie sendet Befehle per HTTP an die OctoPrint-API und verteilt die Statusdaten, die über den WebSocket Client eintreffen, an die Kind-Instanzen. Sie zeigt ihren Zustand im Instanzstatus an (API URL oder Key fehlt, OctoPrint nicht erreichbar, Key ungültig). Normalerweise wird sie über die OctoPrint Discovery angelegt, sie kann aber auch von Hand angelegt werden. Als übergeordnete Instanz dient ein WebSocket Client mit der Adresse `ws://<IP>:<Port>/sockjs/websocket`.

## Inhaltsverzeichnis
- [OctoConnect](#octoconnect)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Variablen](#2-variablen)
  - [3. Funktionen](#3-funktionen)
  - [4. Spenden](#4-spenden)
  - [5. Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
API URL | Adresse des OctoPrint-Servers mit Port, zum Beispiel `http://192.168.1.10:5000`.
API Key | Schlüssel für die OctoPrint-API. Er wird für die Befehle und für die Anmeldung am WebSocket benötigt. Ein Benutzername und ein Passwort sind nicht nötig.
Aktualisierungsintervall | Wie oft OctoPrint den Status sendet, als Vielfaches von 0,5 Sekunden. Standard 2 (jede Sekunde). Größere Werte verringern die Last.

Der Button **API Key anfordern** holt einen Key über die Application Keys von OctoPrint (ab OctoPrint 1.3.10, das Plugin muss aktiv sein):
1. API URL eintragen und die Änderungen übernehmen.
2. Auf den Button klicken. Die Anfrage "IP-Symcon OctoPrint" in der OctoPrint-Weboberfläche bestätigen. Dafür bleiben 90 Sekunden Zeit.
3. Der Key wird in das Feld eingetragen. Danach die Änderungen noch einmal übernehmen.

Alternativ kann ein vorhandener User-Key oder der globale API Key von Hand eingetragen werden. Der Key braucht die Rechte, den Status zu lesen und Befehle auszuführen.

Der Button **Anmelden** meldet sich erneut am WebSocket an.

Fehler bei Befehlen (zum Beispiel "Pause" ohne laufenden Druck) stehen als Warnung im Meldungsfenster mit dem HTTP-Code von OctoPrint.

## 2. Variablen
Diese Instanz legt keine Variablen an. Die Daten gehen an die Kind-Instanzen.

## 3. Funktionen

`string OCTO_sendHTTPRequest(integer $InstanzID, string $Endpoint, array $Params, string $Method);`
Sendet eine Anfrage an die OctoPrint-API (`GET`, `POST`, `PUT` oder `DELETE`, Standard `GET`) und gibt die Antwort als Text zurück. Bei einem Verbindungsfehler ist der Rückgabewert leer.

`void OCTO_OctoPrintLogin(integer $InstanzID);`
Meldet sich mit dem API Key bei OctoPrint an und übergibt die Session an den WebSocket. Wird beim Verbinden und bei `reauthRequired` automatisch aufgerufen. Der alte Name `OCTO_OtcoPrintLogin()` bleibt als veralteter Alias erhalten.

`void OCTO_RequestAPIKey(integer $InstanzID);`
Fordert einen API Key über die Application Keys an. Die Funktion wartet bis zu 90 Sekunden auf die Bestätigung in OctoPrint und ist für den Button im Formular gedacht.

```php
OCTO_OctoPrintLogin(12345);
```

## 4. Spenden
Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
