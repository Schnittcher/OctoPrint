<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/OctoCommandHelper.php';
require_once __DIR__ . '/../libs/OctoCockpitCommands.php';

class OctoTile extends IPSModuleStrict
{
    use OctoCommandHelper;

    // Temperaturverlauf: so viele Messpunkte bleiben im Puffer, die Kachel sammelt danach selbst weiter
    private const HISTORY_POINTS = 240;
    private const SENSORS = ['tool0', 'tool1', 'bed', 'chamber'];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        // Die Konstanten für den Kachel-Typ gibt es erst ab Symcon 9.1, auf 9.0 fehlt auch INSTANCE_VISUALIZATION_TYPE_HTML.
        $this->SetVisualizationType(defined('INSTANCE_VISUALIZATION_TYPE_HTML_FULLSCREEN') ? INSTANCE_VISUALIZATION_TYPE_HTML_FULLSCREEN : 1);
    }

    public function Destroy(): void
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => ['{FDCD30E9-73C5-AC19-0470-20B71111BD91}']
        ]);
    }

    /**
     * Aktionen der Kachel: start, cancel, pause, resume und target (Wert als JSON mit sensor und value).
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        $command = OctoCockpitCommands::build($Ident, $Value);
        if ($command === null) {
            $this->SendDebug('RequestAction', 'Invalid action ' . $Ident . ' ' . json_encode($Value), 0);
            return;
        }
        $this->SendDebug('RequestAction', $Ident . ' -> ' . $command[0], 0);
        $this->sendCommand($command[0], $command[1]);
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        $data = json_decode($JSONString, true);
        $buffer = json_decode((string) ($data['Buffer'] ?? ''), true);
        if (!is_array($buffer)) {
            return '';
        }

        $tile = $this->loadTile();

        if (isset($buffer['type']) && is_string($buffer['type'])) {
            // Ereignis von OctoPrint
            $tile['event'] = $buffer['type'];
        } else {
            if (isset($buffer['state']['text'])) {
                $tile['state'] = (string) $buffer['state']['text'];
            }
            if (isset($buffer['state']['flags']) && is_array($buffer['state']['flags'])) {
                $flags = $buffer['state']['flags'];
                $tile['operational'] = (bool) ($flags['operational'] ?? false);
                $tile['printing'] = (bool) ($flags['printing'] ?? false);
                $tile['paused'] = (bool) ($flags['paused'] ?? false);
                $tile['error'] = (bool) ($flags['error'] ?? false);
            }
            if (isset($buffer['job']['file']) && is_array($buffer['job']['file'])) {
                $tile['file'] = (string) ($buffer['job']['file']['display'] ?? $buffer['job']['file']['name'] ?? '');
            }
            if (isset($buffer['progress']) && is_array($buffer['progress'])) {
                $tile['completion'] = round((float) ($buffer['progress']['completion'] ?? 0), 1);
                $tile['printTime'] = isset($buffer['progress']['printTime']) ? (int) $buffer['progress']['printTime'] : null;
                $tile['printTimeLeft'] = isset($buffer['progress']['printTimeLeft']) ? (int) $buffer['progress']['printTimeLeft'] : null;
            }
            if (isset($buffer['currentZ'])) {
                $tile['z'] = (float) $buffer['currentZ'];
            }
            if (!empty($buffer['temps']) && is_array($buffer['temps'])) {
                $this->readTemperatures($tile, $buffer['temps']);
            }
        }

        $this->SetBuffer('Tile', json_encode($tile));
        $this->UpdateVisualizationValue($this->tileMessage($tile, false));
        return '';
    }

    public function GetVisualizationTile(): string
    {
        $Html = file_get_contents(__DIR__ . '/module.html');
        if ($Html === false) {
            $this->SendDebug(__FUNCTION__, 'module.html could not be read', 0);
            return '';
        }

        // handleMessage steht erst im HTML, deshalb folgen die Startdaten dahinter
        return $Html . '<script>handleMessage(' . json_encode($this->tileMessage($this->loadTile(), true)) . ');</script>';
    }

    /**
     * Übernimmt alle Messpunkte. Die Kachel zeigt jeden Sensor, den OctoPrint meldet, unabhängig von anderen Instanzen.
     */
    private function readTemperatures(array &$tile, array $points): void
    {
        $history = isset($tile['history']) && is_array($tile['history']) ? $tile['history'] : [];

        foreach ($points as $point) {
            if (!is_array($point)) {
                continue;
            }
            $entry = ['t' => (int) ($point['time'] ?? time())];
            foreach (self::SENSORS as $sensor) {
                if (isset($point[$sensor]['actual'])) {
                    $tile['temps'][$sensor] = [
                        'actual' => round((float) $point[$sensor]['actual'], 1),
                        'target' => round((float) ($point[$sensor]['target'] ?? 0), 1)
                    ];
                    $entry[$sensor] = round((float) $point[$sensor]['actual'], 1);
                }
            }
            if (count($entry) > 1) {
                $history[] = $entry;
            }
        }
        $tile['history'] = array_slice($history, -self::HISTORY_POINTS);
    }

    private function loadTile(): array
    {
        $tile = json_decode($this->GetBuffer('Tile'), true);
        return is_array($tile) ? $tile : [];
    }

    /**
     * Nachricht an die Kachel. Die Texte sind übersetzt, die module.html enthält keine Sprache.
     * Den Verlauf bekommt die Kachel nur beim Öffnen, danach sammelt sie ihn aus den Updates selbst.
     */
    private function tileMessage(array $tile, bool $withHistory): string
    {
        $history = $tile['history'] ?? [];
        unset($tile['history']);
        $message = [
            'data'   => $tile,
            'labels' => [
                'offline'     => $this->Translate('No data from OctoPrint'),
                'noJob'       => $this->Translate('No active print job'),
                'timeLeft'    => $this->Translate('Time left'),
                'elapsed'     => $this->Translate('Elapsed'),
                'tool0'       => $this->Translate('Nozzle'),
                'tool1'       => $this->Translate('Nozzle 2'),
                'bed'         => $this->Translate('Bed'),
                'chamber'     => $this->Translate('Chamber'),
                'height'      => $this->Translate('Z Height'),
                'event'       => $this->Translate('Last Event'),
                'start'       => $this->Translate('Start'),
                'pause'       => $this->Translate('Pause'),
                'resume'      => $this->Translate('Resume'),
                'cancel'      => $this->Translate('Cancel'),
                'confirm'     => $this->Translate('Cancel the print?'),
                'yes'         => $this->Translate('Yes'),
                'no'          => $this->Translate('No'),
                'off'         => $this->Translate('Off')
            ]
        ];
        if ($withHistory) {
            $message['history'] = $history;
        }
        return json_encode($message);
    }
}
