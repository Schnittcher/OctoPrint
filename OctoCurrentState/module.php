<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/OctoCommandHelper.php';

class OctoCurrentState extends IPSModuleStrict
{
    use OctoCommandHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        $this->RegisterVariableString('CurrentState', $this->Translate('Current State'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], 0);

        $yesNo = [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'OPTIONS'      => json_encode([
                ['Value' => false, 'Caption' => $this->Translate('No'), 'IconValue' => '', 'IconActive' => false, 'ColorActive' => true, 'ColorValue' => 0xFF8000, 'ContentColorActive' => false],
                ['Value' => true, 'Caption' => $this->Translate('Yes'), 'IconValue' => '', 'IconActive' => false, 'ColorActive' => true, 'ColorValue' => 0x00FF00, 'ContentColorActive' => false]
            ])
        ];

        $this->RegisterVariableBoolean('Operational', $this->Translate('Operational'), $yesNo, 1);
        $this->RegisterVariableBoolean('Printing', $this->Translate('Printing'), $yesNo, 2);
        $this->RegisterVariableBoolean('Cancelling', $this->Translate('Cancelling'), $yesNo, 3);
        $this->RegisterVariableBoolean('Pausing', $this->Translate('Pausing'), $yesNo, 4);
        $this->RegisterVariableBoolean('Resuming', $this->Translate('Resuming'), $yesNo, 5);
        $this->RegisterVariableBoolean('Finishing', $this->Translate('Finishing'), $yesNo, 6);
        $this->RegisterVariableBoolean('ClosedOrError', $this->Translate('Closed or Error'), $yesNo, 7);
        $this->RegisterVariableBoolean('Error', $this->Translate('Error'), $yesNo, 8);
        $this->RegisterVariableBoolean('Paused', $this->Translate('Paused'), $yesNo, 9);
        $this->RegisterVariableBoolean('Ready', $this->Translate('Ready'), $yesNo, 10);
        $this->RegisterVariableBoolean('SDReady', $this->Translate('SD Card ready'), $yesNo, 11);

        $this->RegisterVariableString('LastEvent', $this->Translate('Last Event'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], 12);
        $this->RegisterVariableInteger('LastEventTime', $this->Translate('Last Event Time'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_DATE_TIME,
            'DATE'         => 1,
            'TIME'         => 2
        ], 13);

        $this->RegisterVariableString('ActionStart', $this->Translate('Start'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode([
                ['Value' => 'start', 'Caption' => $this->Translate('Start'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0x00FF00],
                ['Value' => 'restart', 'Caption' => $this->Translate('Restart'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0xFF8000],
                ['Value' => 'cancel', 'Caption' => $this->Translate('Cancel'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0xFF0000]
            ])
        ], 14);
        $this->EnableAction('ActionStart');

        $this->RegisterVariableString('ActionPausing', $this->Translate('Pause / Resume'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode([
                ['Value' => 'pause', 'Caption' => $this->Translate('Pause'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0xFF8000],
                ['Value' => 'resume', 'Caption' => $this->Translate('Resume'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0x00FF00]
            ])
        ], 15);
        $this->EnableAction('ActionPausing');
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

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'ActionStart':
                $params = [
                    'command' => $Value,
                ];
                $this->sendCommand('OCT.ActionStart', $params);
                break;
            case 'ActionPausing':
                $params = [
                    'command' => 'pause',
                    'action'  => $Value,
                ];
                $this->sendCommand('OCT.ActionPausing', $params);
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
        if (!is_array($buffer)) {
            return '';
        }

        if (isset($buffer['type']) && is_string($buffer['type'])) {
            // Ereignis von OctoPrint, zum Beispiel PrintDone
            $this->SetValue('LastEvent', $buffer['type']);
            $this->SetValue('LastEventTime', time());
            return '';
        }

        if (array_key_exists('state', $buffer)) {
            if (array_key_exists('text', $buffer['state'])) {
                $this->SetValue('CurrentState', (string) $buffer['state']['text']);
            }
            if (array_key_exists('flags', $buffer['state'])) {
                $flags = [
                    'Operational'   => 'operational',
                    'Printing'      => 'printing',
                    'Cancelling'    => 'cancelling',
                    'Pausing'       => 'pausing',
                    'Resuming'      => 'resuming',
                    'Finishing'     => 'finishing',
                    'ClosedOrError' => 'closedOrError',
                    'Error'         => 'error',
                    'Paused'        => 'paused',
                    'Ready'         => 'ready',
                    'SDReady'       => 'sdReady'
                ];
                foreach ($flags as $ident => $flag) {
                    $this->SetValue($ident, (bool) ($buffer['state']['flags'][$flag] ?? false));
                }
            }
        }
        return '';
    }
}
