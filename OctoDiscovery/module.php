<?php

declare(strict_types=1);
//require_once __DIR__ . '/../libs/DebugHelper.php';

class OctoDiscovery extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('APIKEY', '');
        $this->RegisterPropertyString('User', '');
        $this->RegisterPropertyString('Password', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $OctoPrintServers = $this->mDNSDiscoverOctoPrintServer();

        $Values = [];

        foreach ($OctoPrintServers as $Server) {
            $instanceID = $this->getOctoPrintServerInstances($Server['UUID']);

            $AddValue = [
                'IPAddress'             => $Server['IPv4'],
                'name'                  => $Server['Name'],
                'Version'               => $Server['Version'],
                'UUID'                  => $Server['UUID'],
                'instanceID'            => $instanceID
            ];

            $AddValue['create'] = [
                [
                    'moduleID'      => '{CCABE3F4-E155-4272-ABCE-D08949751DD0}',
                    'configuration' => [
                        'UUID' => $Server['UUID']
                    ]
                ],
                [
                    'moduleID'      => '{FDCD30E9-73C5-AC19-0470-20B71111BD91}',
                    'configuration' => [
                        'APIURL'        => 'http://' . $Server['IPv4'] . ':' . $Server['Port'],
                        'APIKey'        => $this->ReadPropertyString('APIKEY'),
                        'User'          => $this->ReadPropertyString('User'),
                        'Password'      => $this->ReadPropertyString('Password')
                    ]
                ],
                [
                    'moduleID'      => '{D68FD31F-0E90-7019-F16C-1949BD3079EF}',
                    'configuration' => [
                        'URL'    => 'ws://' . $Server['IPv4'] . ':' . $Server['Port'] . '/sockjs/websocket',
                        'Active' => true
                    ]
                ]
            ];

            $Values[] = $AddValue;
        }
        $Form['actions'][0]['values'] = $Values;
        return json_encode($Form);
    }

    public function mDNSDiscoverOctoPrintServer()
    {
        $mDNSInstanceIDs = IPS_GetInstanceListByModuleID('{780B2D48-916C-4D59-AD35-5A429B2355A5}');
        $resultServiceTypes = ZC_QueryServiceType($mDNSInstanceIDs[0], '_octoprint._tcp.', '');
        $this->SendDebug('mDNS resultServiceTypes', print_r($resultServiceTypes, true), 0);
        $OctoPrintServers = [];
        foreach ($resultServiceTypes as $key => $device) {
            $OctoPrint = [];
            $deviceInfo = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_octoprint._tcp', 'local.');
            $this->SendDebug('mDNS QueryService', $device['Name'] . ' ' . $device['Type'] . ' ' . $device['Domain'] . '.', 0);
            $this->SendDebug('mDNS QueryService Result', print_r($deviceInfo, true), 0);
            if (!empty($deviceInfo)) {
                $hue['Hostname'] = $deviceInfo[0]['Host'];
                if (empty($deviceInfo[0]['IPv4'])) { //IPv4 und IPv6 sind vertauscht
                    $OctoPrint['IPv4'] = $deviceInfo[0]['IPv6'][0];
                } else {
                    $OctoPrint['IPv4'] = $deviceInfo[0]['IPv4'][0];
                }

                $OctoPrint['Name'] = (string) $device['Name'];
                $OctoPrint['Port'] = (integer) $deviceInfo[0]['Port'];
                $OctoPrint['Version'] = (string) str_replace('version=', '', $deviceInfo[0]['TXTRecords'][2]);
                $OctoPrint['UUID'] = (string) str_replace('uuid=', '', $deviceInfo[0]['TXTRecords'][0]);
                array_push($OctoPrintServers, $OctoPrint);
            }
        }
        return $OctoPrintServers;
    }

    private function getOctoPrintServerInstances($UUID)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID('{CCABE3F4-E155-4272-ABCE-D08949751DD0}');
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'UUID') == $UUID) {
                return $id;
            }
        }
        return 0;
    }
}
