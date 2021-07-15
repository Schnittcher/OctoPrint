<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
    class OctoCurrentState extends IPSModule
    {
        use VariableProfileHelper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{FDCD30E9-73C5-AC19-0470-20B71111BD91}');

            $this->RegisterPropertyString('UUID', '');

            $this->RegisterVariableString('CurrentState', $this->Translate('Current State'), '', 0);

            if (!IPS_VariableProfileExists('OCH.YesNo')) {
                $this->RegisterProfileBooleanEx('OCTO.YesNo', 'Information', '', '', [
                    [false, $this->Translate('No'),  '', 0xFF8000],
                    [true, $this->Translate('Yes'),  '', 0x00FF00]
                ]);
            }

            $this->RegisterVariableBoolean('Operational', $this->Translate('Operational'), 'OCTO.YesNo', 1);
            $this->RegisterVariableBoolean('Printing', $this->Translate('Printing'), 'OCTO.YesNo', 2);
            $this->RegisterVariableBoolean('Cancelling', $this->Translate('Cancelling'), 'OCTO.YesNo', 3);
            $this->RegisterVariableBoolean('Pausing', $this->Translate('Pausing'), 'OCTO.YesNo', 4);
            $this->RegisterVariableBoolean('Resuming', $this->Translate('Resuming'), 'OCTO.YesNo', 5);
            $this->RegisterVariableBoolean('Finishing', $this->Translate('Finishing'), 'OCTO.YesNo', 6);
            $this->RegisterVariableBoolean('ClosedOrError', $this->Translate('Closed or Error'), 'OCTO.YesNo', 7);
            $this->RegisterVariableBoolean('Error', $this->Translate('Error'), 'OCTO.YesNo', 8);
            $this->RegisterVariableBoolean('Paused', $this->Translate('Paused'), 'OCTO.YesNo', 9);
            $this->RegisterVariableBoolean('Ready', $this->Translate('Ready'), 'OCTO.YesNo', 10);
            $this->RegisterVariableBoolean('SDReady', $this->Translate('SD Card ready'), 'OCTO.YesNo', 11);
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

            if (array_key_exists('state', $buffer)) {
                if (array_key_exists('text', $buffer['state'])) {
                    $this->SetValue('CurrentState', $buffer['state']['text']);
                }
                if (array_key_exists('flags', $buffer['state'])) {
                    $this->SetValue('Operational', $buffer['state']['flags']['operational']);
                    $this->SetValue('Printing', $buffer['state']['flags']['printing']);
                    $this->SetValue('Cancelling', $buffer['state']['flags']['cancelling']);
                    $this->SetValue('Pausing', $buffer['state']['flags']['pausing']);
                    $this->SetValue('Resuming', $buffer['state']['flags']['resuming']);
                    $this->SetValue('Finishing', $buffer['state']['flags']['finishing']);
                    $this->SetValue('ClosedOrError', $buffer['state']['flags']['closedOrError']);
                    $this->SetValue('Error', $buffer['state']['flags']['error']);
                    $this->SetValue('Paused', $buffer['state']['flags']['paused']);
                    $this->SetValue('Ready', $buffer['state']['flags']['ready']);
                    $this->SetValue('SDReady', $buffer['state']['flags']['sdReady']);
                }
            }
        }
    }