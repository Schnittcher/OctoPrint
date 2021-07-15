<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
    class OctoConnectionHandling extends IPSModule
    {
        use VariableProfileHelper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{FDCD30E9-73C5-AC19-0470-20B71111BD91}');

            $this->RegisterPropertyString('UUID', '');

            if (!IPS_VariableProfileExists('OCH.Ports')) {
                IPS_CreateVariableProfile('OCH.Ports', 3);
            }
            $this->RegisterVariableString('Ports', $this->Translate('Ports'), 'OCH.Ports', 0);
            $this->EnableAction('Ports');

            if (!IPS_VariableProfileExists('OCH.Baudrates')) {
                IPS_CreateVariableProfile('OCH.Baudrates', 3);
            }
            $this->RegisterVariableString('Baudrates', $this->Translate('Baudrates'), 'OCH.Baudrates', 1);
            $this->EnableAction('Baudrates');

            $this->RegisterVariableBoolean('State', $this->Translate('Connection'), '~Switch', 2);
            $this->EnableAction('State');
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

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'State':
                    if ($Value) {
                        $this->Connect();
                    } else {
                        $this->Disonnect();
                    }
                    break;
                default:
                    $this->SetValue($Ident, $Value);
                    break;
            }
        }

        public function ReceiveData($JSONString)
        {
            $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
            $data = json_decode($JSONString, true);
            $buffer = json_decode($data['Buffer'], true);

            if (array_key_exists('type', $buffer)) {
                switch ($buffer['type']) {
                    case 'Connected':
                        $this->SetValue('Ports', $buffer['payload']['port']);
                        $this->SetValue('Baudrates', $buffer['payload']['baudrate']);
                        $this->SetValue('State', true);
                        break;
                    case 'Disconnected':
                        $this->SetValue('State', false);
                        break;
                }
            }
        }

        public function Connect()
        {
            $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

            $Buffer['Command'] = 'CH.Connect';
            $Buffer['Params'] = '';
            //$Buffer['Params']['port'] = $this->GetValue('Ports');
            //$Buffer['Params']['baudrate'] = $this->GetValue('Baudrates');
            //$Buffer['Params']['printerProfile'] = 'Mega-S';
            //$Buffer['Params']['save'] = $this->GetValue('Baudrates');
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $Data = json_decode($this->SendDataToParent($Data), true);
        }

        public function Disonnect()
        {
            $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

            $Buffer['Command'] = 'CH.Disconnect';
            $Buffer['Params'] = '';
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $Data = json_decode($this->SendDataToParent($Data), true);
        }

        public function getConnectSettings()
        {
            $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

            $Buffer['Command'] = 'CH.GetConnectionSettings';
            $Buffer['Params'] = '';

            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);

            $Data = json_decode($this->SendDataToParent($Data), true);

            IPS_LogMessage('getConnectSettings :: Received JSON', print_r($Data, true));
            if (!$Data) {
                return false;
            }

            if (IPS_VariableProfileExists('OCH.Ports')) {
                IPS_DeleteVariableProfile('OCH.Ports');
            }
            $associations = [];
            foreach ($Data['options']['ports'] as $value) {
                $association[0] = $value;
                $association[1] = $value;
                $association[2] = '';
                $association[3] = -1;
                array_push($associations, $association);
            }
            $this->RegisterProfileStringEx('OCH.Ports', 'Network', '', '', $associations);

            if (IPS_VariableProfileExists('OCH.Baudrates')) {
                IPS_DeleteVariableProfile('OCH.Baudrates');
            }
            $associations = [];
            foreach ($Data['options']['baudrates'] as $value) {
                $association[0] = $value;
                $association[1] = $value;
                $association[2] = '';
                $association[3] = -1;
                array_push($associations, $association);
            }
            $this->RegisterProfileStringEx('OCH.Baudrates', 'Network', '', '', $associations);
        }
    }