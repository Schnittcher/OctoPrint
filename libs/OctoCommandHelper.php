<?php

declare(strict_types=1);

/**
 * Sendet Befehle an die übergeordnete Instanz OctoConnect.
 * Die Instanz OctoConnect wandelt den Befehl in eine Anfrage an die OctoPrint-API um.
 */
trait OctoCommandHelper
{
    private function sendCommand(string $Command, array|string $Params = ''): string
    {
        $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

        $Buffer['Command'] = $Command;
        $Buffer['Params'] = $Params;
        $Data['Buffer'] = $Buffer;
        return $this->SendDataToParent(json_encode($Data));
    }
}
