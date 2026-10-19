<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/OctoCommandHelper.php';

class OctoTemperatures extends IPSModuleStrict
{
    use OctoCommandHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        $this->RegisterPropertyBoolean('Tool0Temperatures', true);
        $this->RegisterPropertyBoolean('Tool1Temperatures', false);
        $this->RegisterPropertyBoolean('BedTemperatures', true);
        $this->RegisterPropertyBoolean('ChamberTemperatures', false);
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

        $actual = [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' °C',
            'DIGITS'       => 1
        ];
        $target = [
            'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
            'MIN'          => 0,
            'MAX'          => 400,
            'STEP_SIZE'    => 1,
            'SUFFIX'       => ' °C',
            'DIGITS'       => 1
        ];

        $tool0 = $this->ReadPropertyBoolean('Tool0Temperatures');
        $this->MaintainVariable('Tool0ActualTemperature', $this->Translate('Tool 0 actual Temperature'), VARIABLETYPE_FLOAT, $actual, 1, $tool0);
        $this->MaintainVariable('Tool0TargetTemperature', $this->Translate('Tool 0 target Temperature'), VARIABLETYPE_FLOAT, $target, 2, $tool0);
        if ($tool0) {
            $this->EnableAction('Tool0TargetTemperature');
        }

        $bed = $this->ReadPropertyBoolean('BedTemperatures');
        $this->MaintainVariable('BedActualTemperature', $this->Translate('Bed actual Temperature'), VARIABLETYPE_FLOAT, $actual, 3, $bed);
        $this->MaintainVariable('BedTargetTemperature', $this->Translate('Bed target Temperature'), VARIABLETYPE_FLOAT, $target, 4, $bed);
        if ($bed) {
            $this->EnableAction('BedTargetTemperature');
        }

        $chamber = $this->ReadPropertyBoolean('ChamberTemperatures');
        $this->MaintainVariable('ChamberActualTemperature', $this->Translate('Chamber actual Temperature'), VARIABLETYPE_FLOAT, $actual, 5, $chamber);
        $this->MaintainVariable('ChamberTargetTemperature', $this->Translate('Chamber target Temperature'), VARIABLETYPE_FLOAT, $target, 6, $chamber);
        if ($chamber) {
            $this->EnableAction('ChamberTargetTemperature');
        }

        $tool1 = $this->ReadPropertyBoolean('Tool1Temperatures');
        $this->MaintainVariable('Tool1ActualTemperature', $this->Translate('Tool 1 actual Temperature'), VARIABLETYPE_FLOAT, $actual, 7, $tool1);
        $this->MaintainVariable('Tool1TargetTemperature', $this->Translate('Tool 1 target Temperature'), VARIABLETYPE_FLOAT, $target, 8, $tool1);
        if ($tool1) {
            $this->EnableAction('Tool1TargetTemperature');
        }
    }

    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => ['{FDCD30E9-73C5-AC19-0470-20B71111BD91}']
        ]);
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        $data = json_decode($JSONString, true);
        $buffer = json_decode($data['Buffer'], true);
        if (!is_array($buffer) || !isset($buffer[0]) || !is_array($buffer[0])) {
            return '';
        }

        // Bei mehreren Messpunkten gilt der letzte
        $temps = end($buffer);

        $sensors = [
            'tool0'   => ['Tool0Temperatures', 'Tool0'],
            'tool1'   => ['Tool1Temperatures', 'Tool1'],
            'bed'     => ['BedTemperatures', 'Bed'],
            'chamber' => ['ChamberTemperatures', 'Chamber']
        ];
        foreach ($sensors as $key => [$property, $prefix]) {
            if (isset($temps[$key]) && is_array($temps[$key]) && $this->ReadPropertyBoolean($property)) {
                if (isset($temps[$key]['actual'])) {
                    $this->SetValue($prefix . 'ActualTemperature', (float) $temps[$key]['actual']);
                }
                if (isset($temps[$key]['target'])) {
                    $this->SetValue($prefix . 'TargetTemperature', (float) $temps[$key]['target']);
                }
            }
        }
        return '';
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'BedTargetTemperature':
                $this->sendCommand('Temp.BedTargetTemperature', [
                    'command' => 'target',
                    'target'  => (float) $Value
                ]);
                break;
            case 'ChamberTargetTemperature':
                $this->sendCommand('Temp.ChamberTargetTemperature', [
                    'command' => 'target',
                    'target'  => (float) $Value
                ]);
                break;
            case 'Tool0TargetTemperature':
                $this->sendCommand('Temp.Tool0TargetTemperature', [
                    'command'  => 'target',
                    'targets'  => ['tool0' => (float) $Value]
                ]);
                break;
            case 'Tool1TargetTemperature':
                $this->sendCommand('Temp.Tool1TargetTemperature', [
                    'command'  => 'target',
                    'targets'  => ['tool1' => (float) $Value]
                ]);
                break;
            default:
                $this->SendDebug('RequestAction :: Ident', 'Invalid Ident', 0);
                break;
        }
    }
}
