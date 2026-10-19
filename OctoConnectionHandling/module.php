<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/OctoCommandHelper.php';

class OctoConnectionHandling extends IPSModuleStrict
{
    use OctoCommandHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        $this->RegisterVariableString('Ports', $this->Translate('Ports'), $this->getEnumerationPresentation([]), 0);
        $this->EnableAction('Ports');

        $this->RegisterVariableString('Baudrates', $this->Translate('Baudrates'), $this->getEnumerationPresentation([]), 1);
        $this->EnableAction('Baudrates');

        $this->RegisterVariableBoolean('State', $this->Translate('Connection'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_SWITCH
        ], 2);
        $this->EnableAction('State');
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

        // Auswahl für Ports und Baudraten von OctoPrint laden
        if (IPS_GetKernelRunlevel() === KR_READY && $this->HasActiveParent()) {
            $this->getConnectSettings();
        }
    }

    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => ['{FDCD30E9-73C5-AC19-0470-20B71111BD91}']
        ]);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'State':
                if ($Value) {
                    $this->Connect();
                } else {
                    $this->Disconnect();
                }
                break;
            case 'Ports':
            case 'Baudrates':
                $this->SetValue($Ident, (string) $Value);
                break;
            default:
                $this->SendDebug('RequestAction :: Ident', 'Invalid Ident', 0);
                break;
        }
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        $data = json_decode($JSONString, true);
        $buffer = json_decode($data['Buffer'], true);

        if (is_array($buffer) && array_key_exists('type', $buffer)) {
            switch ($buffer['type']) {
                case 'Connected':
                    $this->SetValue('Ports', (string) ($buffer['payload']['port'] ?? ''));
                    $this->SetValue('Baudrates', (string) ($buffer['payload']['baudrate'] ?? ''));
                    $this->SetValue('State', true);
                    break;
                case 'Disconnected':
                    $this->SetValue('State', false);
                    break;
            }
        }
        return '';
    }

    public function Connect(): void
    {
        // Ohne Angaben verwendet OctoPrint die gespeicherte Einstellung oder erkennt den Port automatisch
        $params = [];
        $port = $this->GetValue('Ports');
        $baudrate = $this->GetValue('Baudrates');
        if ($port !== '') {
            $params['port'] = $port;
        }
        if ($baudrate !== '' && is_numeric($baudrate)) {
            $params['baudrate'] = (int) $baudrate;
        }

        $this->sendCommand('CH.Connect', $params === [] ? '' : $params);
    }

    public function Disconnect(): void
    {
        $this->sendCommand('CH.Disconnect');
    }

    // Veralteter Name mit Tippfehler, bleibt für bestehende Skripte erhalten
    public function Disonnect(): void
    {
        $this->Disconnect();
    }

    public function getConnectSettings(): bool
    {
        $JSONData = $this->sendCommand('CH.GetConnectionSettings');
        $this->SendDebug('getConnectSettings :: Received JSON', $JSONData, 0);
        $Data = json_decode($JSONData, true);

        if (!is_array($Data) || !isset($Data['options']['ports'], $Data['options']['baudrates'])) {
            return false;
        }

        $this->MaintainVariable('Ports', $this->Translate('Ports'), VARIABLETYPE_STRING, $this->getEnumerationPresentation($Data['options']['ports']), 0, true);
        $this->MaintainVariable('Baudrates', $this->Translate('Baudrates'), VARIABLETYPE_STRING, $this->getEnumerationPresentation($Data['options']['baudrates']), 1, true);
        return true;
    }

    private function getEnumerationPresentation(array $values): array
    {
        $options = [];
        foreach ($values as $value) {
            $options[] = ['Value' => (string) $value, 'Caption' => (string) $value, 'IconActive' => false, 'IconValue' => '', 'Color' => -1];
        }
        return [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode($options)
        ];
    }
}
