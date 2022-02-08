<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/SymconModulHelper/VariableProfileHelper.php';
    class OctoSystem extends IPSModule
    {
        use VariableProfileHelper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{FDCD30E9-73C5-AC19-0470-20B71111BD91}');

            $this->RegisterPropertyString('UUID', '');

            if (!IPS_VariableProfileExists('OctoSystem.SystemControl')) {
                $this->RegisterProfileStringEx('OctoSystem.SystemControl', 'Menu', '', '', [
                    ['shutdown', $this->Translate('Shutdown'), '', 0xFF0000],
                    ['reboot', $this->Translate('Reboot'), '', 0xFFA500],
                    ['restart', $this->Translate('Restart'), '', 0x00ff00],
                ]);
            }

            $this->RegisterVariableString('SystemControl', $this->Translate('System Control'), 'OctoSystem.SystemControl', 0);
            $this->EnableAction('SystemControl');
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
                case 'SystemControl':
                    $this->SystemControl($Value);
                    break;
                default:
                    break;
            }
        }

        public function ReceiveData($JSONString)
        {
            $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
            $data = json_decode($JSONString, true);
            $buffer = json_decode($data['Buffer'], true);
        }

        private function SystemControl(string $Value)
        {
            $Data['DataID'] = '{B8E958B1-9C0B-8EB0-B863-7740708326EB}';

            $Buffer['Command'] = 'Sys.Control';
            $Buffer['Params'] = $Value;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $Data = json_decode($this->SendDataToParent($Data), true);
        }
    }