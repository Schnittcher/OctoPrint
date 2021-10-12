<?php

declare(strict_types=1);
    class OctoConnect extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyString('APIURL', '');
            $this->RegisterPropertyString('APIKey', '');
            $this->RegisterPropertyString('User', '');
            $this->RegisterPropertyString('Password', '');
            $this->RegisterAttributeString('Session', '');
            $this->ConnectParent('{D68FD31F-0E90-7019-F16C-1949BD3079EF}');
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
            $this->SendDebug('Received Data :: JSON', $JSONString, 0);

            $data = json_decode($JSONString, true);
            $this->SendDebug('Received Data :: Buffer', $data['Buffer'], 0);
            $buffer = json_decode($data['Buffer'], true);

            if (array_key_exists('connected', $buffer)) {
                if (!empty($this->ReadPropertyString('User')) && (!empty($this->ReadPropertyString('Password')))) {
                    $this->OtcoPrintLogin();
                }
            }

            if (array_key_exists('event', $buffer)) {
                $Data['DataID'] = '{98A7CF97-569C-48C8-8894-D57E1763ACFE}'; //OctoConnectionhandling
                $Data['Buffer'] = json_encode($buffer['event']);
                $this->SendDebug('Send (Child) Event :: JSON', json_encode($Data), 0);
                $this->SendDataToChildren(json_encode($Data));
            }
            if (array_key_exists('current', $buffer)) {
                $Data['DataID'] = '{061BF800-B5B6-5098-BD69-5D0FFD61FCED}'; //OctoCurrentState
                $Data['Buffer'] = json_encode($buffer['current']);
                $this->SendDebug('Send (Child) Current :: JSON', json_encode($Data), 0);
                $this->SendDataToChildren(json_encode($Data));

                if (array_key_exists('temps', $buffer['current'])) {
                    if (!empty($buffer['current']['temps'])) {
                        $Data['DataID'] = '{617B62B6-F91F-40D2-8D91-CA8D46686476}'; //OctoTemperatures
                        $Data['Buffer'] = json_encode($buffer['current']['temps']);
                        $this->SendDebug('Send (Child) Temperatures :: JSON', json_encode($Data), 0);
                        $this->SendDataToChildren(json_encode($Data));
                    }
                }

                if (array_key_exists('job', $buffer['current'])) {
                    if (!empty($buffer['current']['job'])) {
                        $Data['DataID'] = '{B0D9814C-473D-4953-B79D-4B0CF946CBEE}'; //OctoPrintjob
                        $Data['Buffer'] = json_encode($buffer['current']['job']);
                        $this->SendDebug('Send (Child) Printjob Job :: JSON', json_encode($Data), 0);
                        $this->SendDataToChildren(json_encode($Data));
                    }
                }
                if (array_key_exists('progress', $buffer['current'])) {
                    if (!empty($buffer['current']['progress'])) {
                        $Data['DataID'] = '{B0D9814C-473D-4953-B79D-4B0CF946CBEE}'; //OctoPrintjob
                        $Data['Buffer'] = json_encode($buffer['current']['progress']);
                        $this->SendDebug('Send (Child) Printjob Progress :: JSON', json_encode($Data), 0);
                        $this->SendDataToChildren(json_encode($Data));
                    }
                }
            }
        }

        public function ForwardData($JSONString)
        {
            $this->SendDebug('ForwardData :: Received JSON', $JSONString, 0);
            $data = json_decode($JSONString);
            switch ($data->Buffer->Command) {
                case 'CH.GetConnectionSettings':
                    $params = (array) $data->Buffer->Params;
                    return $this->sendHTTPRequest('connection', $params);
                case 'CH.Connect':
                    $params = (array) $data->Buffer->Params;
                    $params['command'] = 'connect';
                    return $this->sendHTTPRequest('connection', $params, 'POST');
                case 'CH.Disconnect':
                    $params = [];
                    $params['command'] = 'disconnect';
                    return $this->sendHTTPRequest('connection', $params, 'POST');
                case 'Temp.BedTargetTemperature':
                    $params = (array) $data->Buffer->Params;
                    return $this->sendHTTPRequest('printer/bed', $params, 'POST');
                case 'Temp.Tool0TargetTemperature':
                    $params = (array) $data->Buffer->Params;
                    return $this->sendHTTPRequest('printer/tool', $params, 'POST');
                case 'OCT.ActionStart':
                    $params = (array) $data->Buffer->Params;
                    return $this->sendHTTPRequest('job', $params, 'POST');
            }
        }

        public function sendHTTPRequest(string $endpoint, array $params = [], string $method = 'GET')
        {
            $URL = $this->ReadPropertyString('APIURL') . '/api/' . $endpoint;
            $apiKey = $this->ReadPropertyString('APIKey');
            $this->SendDebug(__FUNCTION__ . ' :: URL', $URL, 0);
            $this->SendDebug(__FUNCTION__ . ' :: Params', json_encode($params), 0);

            $headers = [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            if ($method == 'POST' || $method == 'PUT' || $method == 'DELETE') {
                if ($method == 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                }
                if (in_array($method, ['PUT', 'DELETE'])) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $JSONResult = curl_exec($ch);
            $headerInfo = curl_getinfo($ch);
            curl_close($ch);

            $result = json_decode($JSONResult, true);

            return $JSONResult;
        }

        public function OtcoPrintLogin()
        {
            $params['user'] = $this->ReadPropertyString('User');
            $params['pass'] = $this->ReadPropertyString('Password');

            $result = $this->sendHTTPRequest('login', $params, 'POST');
            $result = json_decode($result, true);
            $this->WriteAttributeString('Session', $result['session']);

            //Login via Websockets

            $websockets['auth'] = $this->ReadPropertyString('User') . ':' . $this->ReadAttributeString('Session');

            $Data['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
            $Data['Buffer'] = json_encode($websockets);

            $this->SendDebug('Login Websockets :: JSON', json_encode($Data), 0);

            $this->SendDataToParent(json_encode($Data));
        }
    }
