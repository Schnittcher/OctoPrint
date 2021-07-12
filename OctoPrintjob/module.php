<?php

declare(strict_types=1);
//require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
    class OctoPrintjob extends IPSModule
    {
        //use VariableProfileHelper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{FDCD30E9-73C5-AC19-0470-20B71111BD91}');

            $this->RegisterVariableString('Filename', $this->Translate('Filename'), '', 0);
            $this->RegisterVariableString('Path', $this->Translate('Path'), '', 1);
            $this->RegisterVariableString('Display', $this->Translate('Display'), '', 2);
            $this->RegisterVariableString('Origin', $this->Translate('Origin'), '', 3);
            $this->RegisterVariableFloat('Size', $this->Translate('Size'), '', 4);

            $this->RegisterVariableFloat('Completion', $this->Translate('Completion'), '', 5);
            $this->RegisterVariableInteger('Filepos', $this->Translate('Fileposition'), '', 6);
            $this->RegisterVariableInteger('Printtime', $this->Translate('Print Time'), '', 7);
            $this->RegisterVariableInteger('PrinttimeLeft', $this->Translate('Print Time left'), '', 8);
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
        }

        public function ReceiveData($JSONString)
        {
            $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
            $data = json_decode($JSONString, true);
            $buffer = json_decode($data['Buffer'], true);

            //IPS_LogMessage('PrintJob Buffer', print_r($buffer, true));

            if (array_key_exists('file', $buffer)) {
                $this->SetValue('Filename', $buffer['file']['name']);
                $this->SetValue('Path', $buffer['file']['path']);
                $this->SetValue('Display', $buffer['file']['path']);
                $this->SetValue('Origin', $buffer['file']['origin']);
                $this->SetValue('Size', $buffer['file']['size']);
            }
            if (array_key_exists('completion', $buffer)) {
                $this->SetValue('Completion', $buffer['completion']);
                $this->SetValue('Filepos', $buffer['filepos']);
                $this->SetValue('Printtime', $buffer['printTime']);
                $this->SetValue('PrinttimeLeft', $buffer['printTimeLeft']);
            }
        }
    }