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

            $this->MaintainVariable('BedActualTemperature', $this->Translate('Bed actual Temperature'), 2, '~Temperature', 3, $this->ReadPropertyBoolean('BedTemperatures') == true);
            $this->MaintainVariable('BedTargetTemperature', $this->Translate('Bed target Temperature'), 2, '~Temperature', 4, $this->ReadPropertyBoolean('BedTemperatures') == true);

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
    }