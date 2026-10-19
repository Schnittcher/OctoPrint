<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/OctoCommandHelper.php';

class OctoSystem extends IPSModuleStrict
{
    use OctoCommandHelper;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('UUID', '');

        $this->RegisterVariableString('SystemControl', $this->Translate('System Control'), [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode([
                ['Value' => 'shutdown', 'Caption' => $this->Translate('Shutdown'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0xFF0000],
                ['Value' => 'reboot', 'Caption' => $this->Translate('Reboot'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0xFFA500],
                ['Value' => 'restart', 'Caption' => $this->Translate('Restart'), 'IconActive' => false, 'IconValue' => '', 'Color' => 0x00FF00]
            ])
        ], 0);
        $this->EnableAction('SystemControl');
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
    }

    public function GetCompatibleParents(): string
    {
        return json_encode([
            'type'      => 'connect',
            'moduleIDs' => ['{FDCD30E9-73C5-AC19-0470-20B71111BD91}']
        ]);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'SystemControl':
                $this->sendCommand('Sys.Control', (string) $Value);
                break;
            default:
                $this->SendDebug('RequestAction :: Ident', 'Invalid Ident', 0);
                break;
        }
    }

    public function ReceiveData(string $JSONString): string
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        return '';
    }
}
