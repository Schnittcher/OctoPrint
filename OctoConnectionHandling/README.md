# OctoConnectionHandling
   Mit dieser Instanz ist es möglich die Verbindungen zum 3D Drucker herzustellen.

   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Keine Konfiguration notwendig.
     
   ## 2. Funktionen
   
   **OCTO_Connect($InstanceID)**\
   Mit dieser Funktion wird die Verbindung zum Drucker hergestellt.

   ```php
   OCTO_Connect(25537);
   ```

  **OCTO_Disconnect($InstanceID)**\
   Mit dieser Funktion kann die Verbindung zum Drucker getrennt werden.

   ```php
   OCTO_Disconnect(25537);
   ```