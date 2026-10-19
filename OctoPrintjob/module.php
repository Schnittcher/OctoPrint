<?php

declare(strict_types=1);

class OctoPrintjob extends IPSModuleStrict
{
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        $text = ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION];
        $megabytes = [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' MB',
            'DIGITS'       => 1
        ];
        $duration = [
            'PRESENTATION' => VARIABLE_PRESENTATION_DURATION,
            'FORMAT'       => 2
        ];

        $this->RegisterVariableString('Filename', $this->Translate('Filename'), $text, 0);
        $this->RegisterVariableString('Path', $this->Translate('Path'), $text, 1);
        $this->RegisterVariableString('Display', $this->Translate('Display'), $text, 2);
        $this->RegisterVariableString('Origin', $this->Translate('Origin'), $text, 3);
        $this->RegisterVariableFloat('Size', $this->Translate('Size'), $megabytes, 4);

        $this->RegisterVariableInteger('Completion', $this->Translate('Completion'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' %'
        ], 5);
        $this->RegisterVariableFloat('Filepos', $this->Translate('Fileposition'), $megabytes, 6);
        $this->RegisterVariableInteger('Printtime', $this->Translate('Print Time'), $duration, 7);
        $this->RegisterVariableInteger('PrinttimeLeft', $this->Translate('Print Time left'), $duration, 8);
        $this->RegisterVariableInteger('PrinttimeEstimated', $this->Translate('Print Time estimated'), $duration, 9);
        $this->RegisterVariableFloat('CurrentZ', $this->Translate('Z Height'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' mm',
            'DIGITS'       => 2
        ], 10);
        $this->RegisterVariableFloat('FilamentLength', $this->Translate('Filament Length'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' m',
            'DIGITS'       => 2
        ], 11);
        $this->RegisterVariableFloat('FilamentVolume', $this->Translate('Filament Volume'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'SUFFIX'       => ' cm³',
            'DIGITS'       => 2
        ], 12);
        $this->RegisterVariableInteger('FileDate', $this->Translate('File Date'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_DATE_TIME,
            'DATE'         => 1,
            'TIME'         => 1
        ], 13);
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

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        $data = json_decode($JSONString, true);
        $buffer = json_decode($data['Buffer'], true);
        if (!is_array($buffer)) {
            return '';
        }

        if (array_key_exists('file', $buffer) && is_array($buffer['file'])) {
            $this->SetValue('Filename', (string) ($buffer['file']['name'] ?? ''));
            $this->SetValue('Path', (string) ($buffer['file']['path'] ?? ''));
            $this->SetValue('Display', (string) ($buffer['file']['display'] ?? $buffer['file']['path'] ?? ''));
            $this->SetValue('Origin', (string) ($buffer['file']['origin'] ?? ''));
            $this->SetValue('Size', (float) ($buffer['file']['size'] ?? 0) / 1024 / 1024);
            $this->SetValue('FileDate', (int) ($buffer['file']['date'] ?? 0));
        }
        if (array_key_exists('estimatedPrintTime', $buffer)) {
            $this->SetValue('PrinttimeEstimated', (int) round((float) $buffer['estimatedPrintTime']));
        }
        if (isset($buffer['filament']) && is_array($buffer['filament'])) {
            // Summe über alle Extruder, OctoPrint liefert Länge in mm und Volumen in cm³
            $length = 0.0;
            $volume = 0.0;
            foreach ($buffer['filament'] as $tool) {
                $length += (float) ($tool['length'] ?? 0);
                $volume += (float) ($tool['volume'] ?? 0);
            }
            $this->SetValue('FilamentLength', $length / 1000);
            $this->SetValue('FilamentVolume', $volume);
        }
        if (array_key_exists('completion', $buffer)) {
            $this->SetValue('Completion', (int) round((float) $buffer['completion']));
            $this->SetValue('Filepos', (float) ($buffer['filepos'] ?? 0) / 1024 / 1024);
            if (!is_null($buffer['printTime'] ?? null)) {
                $this->SetValue('Printtime', (int) $buffer['printTime']);
            }
            $this->SetValue('PrinttimeLeft', (int) ($buffer['printTimeLeft'] ?? 0));
        }
        if (isset($buffer['currentZ'])) {
            $this->SetValue('CurrentZ', (float) $buffer['currentZ']);
        }
        return '';
    }
}
