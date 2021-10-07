<?php

declare(strict_types=1);

class OctoConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('UUID', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Values = [];

        $InstancesCount = count($Form['actions'][0]['values']);

        $OctoPrintInstanceNames = [];
        $OctoPrintInstanceNames[0] = 'OctoPrint Current State';
        $OctoPrintInstanceNames[1] = 'OctoPrint Printjob';
        $OctoPrintInstanceNames[2] = 'OctoPrint Temperatures';
        $OctoPrintInstanceNames[3] = 'OctoPrint Connection Handling';

        for ($i = 0; $i <= $InstancesCount - 1; $i++) {
            $instanceID = $this->getOctoPrintInstances($Form['actions'][0]['values'][$i]['create']['moduleID'], $this->ReadPropertyString('UUID'));
            $Form['actions'][0]['values'][$i]['DisplayName'] = $this->Translate($OctoPrintInstanceNames[$i]);
            $Form['actions'][0]['values'][$i]['instanceID'] = $instanceID;

            $Form['actions'][0]['values'][$i]['create']['configuration']['UUID'] = $this->ReadPropertyString('UUID');
        }
        return json_encode($Form);
    }

    private function getOctoPrintInstances($moduleID, $UUID)
    {
        $InstanceIDs = IPS_GetInstanceListByModuleID($moduleID);
        foreach ($InstanceIDs as $id) {
            if (IPS_GetProperty($id, 'UUID') == $UUID) {
                return $id;
            }
        }
        return 0;
    }
}
