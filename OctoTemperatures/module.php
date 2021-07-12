<?php

declare(strict_types=1);
//require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
    class OctoTemperatures extends IPSModule
    {
        //use VariableProfileHelper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{FDCD30E9-73C5-AC19-0470-20B71111BD91}');

            $this->RegisterPropertyBoolean('Tool0Temperatures', true);
            $this->RegisterPropertyBoolean('BedTemperatures', true);
            $this->RegisterPropertyBoolean('ChamerTemperatures', false);
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();

            $this->MaintainVariable('Tool0ActualTemperature', $this->Translate('Tool 0 actual Temperature'), 2, '~Temperature', 1, $this->ReadPropertyBoolean('Tool0Temperatures') == true);
            $this->MaintainVariable('Tool0TargetTemperature', $this->Translate('Tool 0 target Temperature'), 2, '~Temperature', 2, $this->ReadPropertyBoolean('Tool0Temperatures') == true);
            if ($this->ReadPropertyBoolean('Tool0Temperatures') == true) {
                $this->EnableAction('Tool0TargetTemperature');
            }

            $this->MaintainVariable('BedActualTemperature', $this->Translate('Bed actual Temperature'), 2, '~Temperature', 3, $this->ReadPropertyBoolean('BedTemperatures') == true);
            $this->MaintainVariable('BedTargetTemperature', $this->Translate('Bed target Temperature'), 2, '~Temperature', 4, $this->ReadPropertyBoolean('BedTemperatures') == true);
            if ($this->ReadPropertyBoolean('BedTemperatures') == true) {
                $this->EnableAction('BedTargetTemperature');
            }

            $this->MaintainVariable('ChamberActualTemperature', $this->Translate('Chamber actual Temperature'), 2, '~Temperature', 5, $this->ReadPropertyBoolean('ChamerTemperatures') == true);
            $this->MaintainVariable('ChamberTargetTemperature', $this->Translate('Chamber target Temperature'), 2, '~Temperature', 6, $this->ReadPropertyBoolean('ChamerTemperatures') == true);
        }

        public function ReceiveData($JSONString)
        {
            $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
            $data = json_decode($JSONString, true);
            $buffer = json_decode($data['Buffer'], true);

            IPS_LogMessage('Temps Buffer', print_r($buffer, true));

            if (array_key_exists('tool0', $buffer[0]) && ($this->ReadPropertyBoolean('Tool0Temperatures'))) {
                $this->SetValue('Tool0ActualTemperature', $buffer[0]['tool0']['actual']);
                $this->SetValue('Tool0TargetTemperature', $buffer[0]['tool0']['target']);
            }
            if (array_key_exists('bed', $buffer[0]) && ($this->ReadPropertyBoolean('BedTemperatures'))) {
                $this->SetValue('BedActualTemperature', $buffer[0]['bed']['actual']);
                $this->SetValue('BedTargetTemperature', $buffer[0]['bed']['target']);
            }
            if (array_key_exists('chamber', $buffer[0]) && ($this->ReadPropertyBoolean('ChamberTemperatures'))) {
                $this->SetValue('ChamberActualTemperature', $buffer[0]['chamber']['actual']);
                $this->SetValue('ChamberTargetTemperature', $buffer[0]['chamber']['target']);
            }
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'BedTargetTemperature':
                    $params = [
                        'command' => 'target',
                        'target'  => $Value
                    ];
                    $this->sendCommand('Temp.BedTargetTemperature', $params);
                    break;
                case 'Tool0TargetTemperature':
                    $params = [
                        'command'  => 'target',
                        'targets'  => [
                            'tool0' => $Value
                        ]
                    ];
                    $this->sendCommand('Temp.Tool0TargetTemperature', $params);
                    break;
                    break;
                default:
                    $this->SetValue($Ident, $Value);
                    break;
            }
        }

        private function sendCommand($Command, $Params)
        {
            $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

            $Buffer['Command'] = $Command;
            $Buffer['Params'] = $Params;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $Data = json_decode($this->SendDataToParent($Data), true);
        }
    }