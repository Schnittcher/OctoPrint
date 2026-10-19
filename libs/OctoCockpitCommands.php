<?php

declare(strict_types=1);

/**
 * Übersetzt die Aktionen der Kachel in Befehle für OctoConnect.
 * Gibt null zurück, wenn die Aktion oder der Wert ungültig ist.
 */
final class OctoCockpitCommands
{
    public const MAX_TEMPERATURE = 400.0;

    /**
     * @return array{0: string, 1: array}|null Befehl für OctoConnect und seine Parameter
     */
    public static function build(string $ident, mixed $value): ?array
    {
        switch ($ident) {
            case 'start':
                return ['OCT.ActionStart', ['command' => 'start']];
            case 'cancel':
                return ['OCT.ActionStart', ['command' => 'cancel']];
            case 'pause':
                return ['OCT.ActionPausing', ['command' => 'pause', 'action' => 'pause']];
            case 'resume':
                return ['OCT.ActionPausing', ['command' => 'pause', 'action' => 'resume']];
            case 'target':
                return self::target($value);
        }
        return null;
    }

    private static function target(mixed $value): ?array
    {
        $data = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($data) || !isset($data['sensor'], $data['value']) || !is_numeric($data['value'])) {
            return null;
        }
        $target = min(max((float) $data['value'], 0.0), self::MAX_TEMPERATURE);

        switch ($data['sensor']) {
            case 'tool0':
            case 'tool1':
                $command = $data['sensor'] === 'tool0' ? 'Temp.Tool0TargetTemperature' : 'Temp.Tool1TargetTemperature';
                return [$command, ['command' => 'target', 'targets' => [$data['sensor'] => $target]]];
            case 'bed':
                return ['Temp.BedTargetTemperature', ['command' => 'target', 'target' => $target]];
            case 'chamber':
                return ['Temp.ChamberTargetTemperature', ['command' => 'target', 'target' => $target]];
        }
        return null;
    }
}
