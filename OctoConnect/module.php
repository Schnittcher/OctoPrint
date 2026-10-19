<?php

declare(strict_types=1);

class OctoConnect extends IPSModuleStrict
{
    private const STATUS_ACTIVE = 102;
    private const STATUS_URL_MISSING = 201;
    private const STATUS_NOT_REACHABLE = 202;
    private const STATUS_KEY_INVALID = 203;
    private const STATUS_KEY_MISSING = 204;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('APIURL', '');
        $this->RegisterPropertyString('APIKey', '');
        $this->RegisterPropertyInteger('Throttle', 2);
        $this->RegisterAttributeString('Session', '');
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

        if ($this->ReadPropertyString('APIURL') === '') {
            $this->SetStatus(self::STATUS_URL_MISSING);
            return;
        }
        if ($this->ReadPropertyString('APIKey') === '') {
            $this->SetStatus(self::STATUS_KEY_MISSING);
            return;
        }
        $this->SetStatus(self::STATUS_ACTIVE);

        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }

        // Erreichbarkeit und API Key prüfen, das Ergebnis setzt den Instanzstatus
        $this->performRequest('version', [], 'GET', 5);

        if ($this->HasActiveParent()) {
            $this->sendSubscription();
        }
    }

    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => ['{D68FD31F-0E90-7019-F16C-1949BD3079EF}']
        ]);
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('Received Data :: JSON', $JSONString, 0);

        $data = json_decode($JSONString, true);
        // IPSModuleStrict: Der Buffer des WebSocket Clients ist hexkodiert
        $payload = $this->decodeBuffer((string) $data['Buffer']);
        $this->SendDebug('Received Data :: Buffer', $payload, 0);
        $buffer = json_decode($payload, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_array($buffer)) {
            return '';
        }

        if (array_key_exists('connected', $buffer)) {
            if (is_array($buffer['connected']) && isset($buffer['connected']['display_version'])) {
                $this->SetSummary('OctoPrint ' . $buffer['connected']['display_version']);
            }
            // subscribe und throttle vor dem Login senden, damit der Filter von Anfang an greift
            $this->sendSubscription();
            $this->loginIfConfigured();
        }

        if (array_key_exists('reauthRequired', $buffer)) {
            $this->SendDebug('reauthRequired', json_encode($buffer['reauthRequired']), 0);
            $this->loginIfConfigured();
        }

        if (array_key_exists('event', $buffer) && is_array($buffer['event'])) {
            // Verbindungsereignisse für OctoConnectionHandling, alle Ereignisse für OctoCurrentState
            foreach (['{98A7CF97-569C-48C8-8894-D57E1763ACFE}', '{061BF800-B5B6-5098-BD69-5D0FFD61FCED}'] as $dataID) {
                $Data['DataID'] = $dataID;
                $Data['Buffer'] = json_encode($buffer['event']);
                $this->SendDebug('Send (Child) Event :: JSON', json_encode($Data), 0);
                $this->SendDataToChildren(json_encode($Data));
            }
        }
        // history enthält dieselben Daten wie current und wird direkt nach dem Verbinden gesendet
        foreach (['history', 'current'] as $key) {
            if (array_key_exists($key, $buffer) && is_array($buffer[$key])) {
                $this->forwardState($buffer[$key]);
            }
        }
        return '';
    }

    public function ForwardData(string $JSONString): string
    {
        $this->SendDebug('ForwardData :: Received JSON', $JSONString, 0);
        $data = json_decode($JSONString);
        switch ($data->Buffer->Command) {
            case 'CH.GetConnectionSettings':
                $params = is_object($data->Buffer->Params) ? (array) $data->Buffer->Params : [];
                return $this->apiCall('connection', $params);
            case 'CH.Connect':
                $params = is_object($data->Buffer->Params) ? (array) $data->Buffer->Params : [];
                $params['command'] = 'connect';
                return $this->apiCall('connection', $params, 'POST');
            case 'CH.Disconnect':
                $params = [];
                $params['command'] = 'disconnect';
                return $this->apiCall('connection', $params, 'POST');
            case 'Temp.BedTargetTemperature':
                $params = (array) $data->Buffer->Params;
                return $this->apiCall('printer/bed', $params, 'POST');
            case 'Temp.ChamberTargetTemperature':
                $params = (array) $data->Buffer->Params;
                return $this->apiCall('printer/chamber', $params, 'POST');
            case 'Temp.Tool0TargetTemperature':
            case 'Temp.Tool1TargetTemperature':
                $params = (array) $data->Buffer->Params;
                return $this->apiCall('printer/tool', $params, 'POST');
            case 'OCT.ActionStart':
                $params = (array) $data->Buffer->Params;
                return $this->apiCall('job', $params, 'POST');
            case 'OCT.ActionPausing':
                $params = (array) $data->Buffer->Params;
                return $this->apiCall('job', $params, 'POST');
            case 'Sys.Control':
                $params = (string) $data->Buffer->Params;
                return $this->apiCall('system/commands/core/' . $params, [], 'POST');
        }
        return '';
    }

    // Öffentliche Methoden haben keine Standardwerte, Symcon verlangt in Skripten alle Parameter
    public function sendHTTPRequest(string $endpoint, array $params, string $method): string
    {
        return $this->apiCall($endpoint, $params, $method);
    }

    public function RequestAPIKey(): void
    {
        $base = rtrim($this->ReadPropertyString('APIURL'), '/');
        if ($base === '') {
            $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('Please enter the API URL and apply the changes first.'));
            return;
        }

        // Application Keys Plugin vorhanden?
        $probe = $this->httpRequest($base . '/plugin/appkeys/probe', 'GET');
        if ($probe['code'] !== 204) {
            $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('Application Keys are not available. Please enter an API key manually.'));
            return;
        }

        $request = $this->httpRequest($base . '/plugin/appkeys/request', 'POST', ['app' => 'IP-Symcon OctoPrint']);
        $answer = json_decode($request['body'], true);
        if ($request['code'] !== 201 || !is_array($answer) || empty($answer['app_token'])) {
            $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('The request was rejected by OctoPrint.'));
            return;
        }

        $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('Please confirm the request in the OctoPrint web interface.'));

        // OctoPrint verwirft die Anfrage, wenn sie länger als 5 Sekunden nicht abgefragt wird
        for ($i = 0; $i < 90; $i++) {
            sleep(1);
            $poll = $this->httpRequest($base . '/plugin/appkeys/request/' . $answer['app_token'], 'GET');
            if ($poll['code'] === 200) {
                $result = json_decode($poll['body'], true);
                if (is_array($result) && !empty($result['api_key'])) {
                    $this->UpdateFormField('APIKey', 'value', (string) $result['api_key']);
                    $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('API key received. Please apply the changes.'));
                    return;
                }
            }
            if ($poll['code'] !== 202) {
                break;
            }
        }
        $this->UpdateFormField('RequestStatus', 'caption', $this->Translate('The request was denied or timed out.'));
    }

    public function OctoPrintLogin(): void
    {
        if ($this->ReadPropertyString('APIKey') === '') {
            $this->SendDebug(__FUNCTION__, 'No API key configured', 0);
            return;
        }

        // Passiver Login: OctoPrint liefert Benutzer und Session zum API Key
        $params = ['passive' => true];

        $result = $this->apiCall('login', $params, 'POST');
        $result = json_decode($result, true);
        $this->SendDebug(__FUNCTION__ . ' :: Result', is_array($result) ? json_encode(array_keys($result)) : 'invalid response', 0);
        if (!is_array($result) || empty($result['session'])) {
            $this->SendDebug(__FUNCTION__, 'Login failed', 0);
            $this->LogMessage($this->Translate('OctoPrint login failed. Please check the API key.'), KL_WARNING);
            return;
        }
        $this->WriteAttributeString('Session', (string) $result['session']);

        //Login via Websockets

        $name = (string) ($result['name'] ?? '');
        $websockets['auth'] = $name . ':' . $this->ReadAttributeString('Session');

        $Data['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        $Data['Buffer'] = bin2hex(json_encode($websockets));

        $this->SendDebug('Login Websockets :: JSON', json_encode($Data), 0);

        $this->SendDataToParent(json_encode($Data));
    }

    // Veralteter Name mit Tippfehler, bleibt für bestehende Skripte erhalten
    public function OtcoPrintLogin(): void
    {
        $this->OctoPrintLogin();
    }

    private function apiCall(string $endpoint, array $params = [], string $method = 'GET'): string
    {
        return $this->performRequest($endpoint, $params, $method)['body'];
    }

    private function forwardState(array $current): void
    {
        $Data['DataID'] = '{061BF800-B5B6-5098-BD69-5D0FFD61FCED}'; //OctoCurrentState
        $Data['Buffer'] = json_encode($current);
        $this->SendDebug('Send (Child) Current :: JSON', json_encode($Data), 0);
        $this->SendDataToChildren(json_encode($Data));

        if (!empty($current['temps'])) {
            $Data['DataID'] = '{617B62B6-F91F-40D2-8D91-CA8D46686476}'; //OctoTemperatures
            $Data['Buffer'] = json_encode($current['temps']);
            $this->SendDebug('Send (Child) Temperatures :: JSON', json_encode($Data), 0);
            $this->SendDataToChildren(json_encode($Data));
        }

        if (!empty($current['job'])) {
            $Data['DataID'] = '{B0D9814C-473D-4953-B79D-4B0CF946CBEE}'; //OctoPrintjob
            $Data['Buffer'] = json_encode($current['job']);
            $this->SendDebug('Send (Child) Printjob Job :: JSON', json_encode($Data), 0);
            $this->SendDataToChildren(json_encode($Data));
        }
        if (!empty($current['progress'])) {
            $Data['DataID'] = '{B0D9814C-473D-4953-B79D-4B0CF946CBEE}'; //OctoPrintjob
            $Data['Buffer'] = json_encode($current['progress']);
            $this->SendDebug('Send (Child) Printjob Progress :: JSON', json_encode($Data), 0);
            $this->SendDataToChildren(json_encode($Data));
        }
        if (isset($current['currentZ'])) {
            $Data['DataID'] = '{B0D9814C-473D-4953-B79D-4B0CF946CBEE}'; //OctoPrintjob
            $Data['Buffer'] = json_encode(['currentZ' => $current['currentZ']]);
            $this->SendDebug('Send (Child) Printjob Z :: JSON', json_encode($Data), 0);
            $this->SendDataToChildren(json_encode($Data));
        }
    }

    private function decodeBuffer(string $buffer): string
    {
        if ($buffer !== '' && strlen($buffer) % 2 === 0 && ctype_xdigit($buffer)) {
            return (string) hex2bin($buffer);
        }
        return $buffer;
    }

    private function sendSubscription(): void
    {
        // Terminal-Logs werden nicht ausgewertet und würden die Nachrichten stark vergrößern
        $subscribe = [
            'subscribe' => [
                'state'   => ['logs' => false, 'messages' => false],
                'events'  => [
                    'Startup', 'Shutdown', 'Connected', 'Disconnected', 'Error', 'FileSelected',
                    'PrintStarted', 'PrintDone', 'PrintFailed', 'PrintCancelled', 'PrintPaused', 'PrintResumed'
                ],
                'plugins' => false
            ]
        ];
        $throttle = ['throttle' => max(1, $this->ReadPropertyInteger('Throttle'))];
        foreach ([$subscribe, $throttle] as $message) {
            $Data['DataID'] = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
            $Data['Buffer'] = bin2hex(json_encode($message));
            $this->SendDebug('Send (Parent) :: JSON', json_encode($Data), 0);
            $this->SendDataToParent(json_encode($Data));
        }
    }

    private function loginIfConfigured(): void
    {
        // Schutz vor Endlosschleife, falls OctoPrint wiederholt eine neue Anmeldung verlangt
        $last = (int) $this->GetBuffer('LastLogin');
        if (time() - $last < 5) {
            $this->SendDebug(__FUNCTION__, 'Login skipped (last login less than 5 seconds ago)', 0);
            return;
        }
        $this->SetBuffer('LastLogin', (string) time());
        $this->OctoPrintLogin();
    }

    /**
     * Anfrage an die OctoPrint-API mit dem API Key. Das Ergebnis setzt den Instanzstatus.
     *
     * @return array{code: int, body: string}
     */
    private function performRequest(string $endpoint, array $params = [], string $method = 'GET', int $timeout = 10): array
    {
        $base = rtrim($this->ReadPropertyString('APIURL'), '/');
        if ($base === '') {
            $this->SendDebug(__FUNCTION__, 'No API URL configured', 0);
            return ['code' => 0, 'body' => ''];
        }

        $URL = $base . '/api/' . $endpoint;
        $apiKey = $this->ReadPropertyString('APIKey');
        $this->SendDebug(__FUNCTION__ . ' :: URL', $URL, 0);
        $this->SendDebug(__FUNCTION__ . ' :: Params', json_encode($params), 0);

        $headers = [];
        if ($apiKey !== '') {
            $headers[] = 'X-Api-Key: ' . $apiKey;
        }
        $result = $this->httpRequest($URL, $method, $method === 'GET' ? null : $params, $headers, $timeout);
        $this->evaluateResponse($endpoint, $result['code'], $result['body']);
        return $result;
    }

    private function evaluateResponse(string $endpoint, int $code, string $body): void
    {
        $status = $this->GetStatus();

        if ($code === 0) {
            if ($status !== self::STATUS_NOT_REACHABLE) {
                $this->LogMessage($this->Translate('OctoPrint is not reachable. Please check the API URL.'), KL_WARNING);
            }
            if (in_array($status, [self::STATUS_ACTIVE, self::STATUS_KEY_INVALID], true)) {
                $this->SetStatus(self::STATUS_NOT_REACHABLE);
            }
            return;
        }

        if ($code === 401 || $code === 403) {
            if ($status !== self::STATUS_KEY_INVALID) {
                $this->LogMessage($this->Translate('OctoPrint rejected the API key. Please check the API key.'), KL_WARNING);
            }
            if (in_array($status, [self::STATUS_ACTIVE, self::STATUS_NOT_REACHABLE], true)) {
                $this->SetStatus(self::STATUS_KEY_INVALID);
            }
            return;
        }

        if ($code >= 200 && $code < 300) {
            if (in_array($status, [self::STATUS_NOT_REACHABLE, self::STATUS_KEY_INVALID], true)) {
                $this->SetStatus(self::STATUS_ACTIVE);
            }
            return;
        }

        // Andere Fehler, zum Beispiel 409 bei Pause ohne laufenden Druck
        $message = json_decode($body, true);
        $detail = is_array($message) && isset($message['error']) ? (string) $message['error'] : trim(substr($body, 0, 120));
        $this->LogMessage(sprintf('OctoPrint /api/%s: HTTP %d %s', $endpoint, $code, $detail), KL_WARNING);
    }

    /**
     * @return array{code: int, body: string}
     */
    private function httpRequest(string $url, string $method, ?array $body = null, array $headers = [], int $timeout = 10): array
    {
        $this->SendDebug(__FUNCTION__ . ' :: URL', $url, 0);
        $headers[] = 'Content-Type: application/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body ?? []));
        }
        $result = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($result === false) {
            $this->SendDebug(__FUNCTION__ . ' :: Error', curl_error($ch), 0);
        }
        // Schlüssel und Sessions gehören nicht ins Debug
        $this->SendDebug(__FUNCTION__ . ' :: Result', $code . ' ' . preg_replace('/"(api_key|session)"\s*:\s*"[^"]*"/', '"$1":"***"', (string) $result), 0);
        return ['code' => $code, 'body' => (string) $result];
    }
}
