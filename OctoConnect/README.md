# OctoConnect
   Diese Splitter Instanz stellt die Verbindung zum OctoPrint Server her.

   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   API URL | Her wird die URL zum OctoPrint Server inkl. Port eingetragen. Beispiel: http://10.10.0.60:5000
   API Key | Für die Nutzung der API, ist ein API Key notwendig, dieser wird hier eingetragen. Der API Key kann unter Einstellungen -> Application Keys generiert werden.
   Benutzer | Hier wird der OctoPrint benutzer eingetragen.
   Passwort| Hier wird das Passwort zu dem OctoPrint User eingetragen.

     
   ## 2. Funktionen
   
   **OCTO_OctoPrintLogin($InstanceID)**\
   Mit dieser Funktion ist es möglich manuell den Login mit dem Websocket Client vom OctoPrint Server durchzuführen.

   ```php
   OCTO_OctoPrintLogin(25537); //Login
   ```