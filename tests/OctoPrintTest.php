<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class OctoPrintTest extends TestCase
{
    private const CONNECT = '{FDCD30E9-73C5-AC19-0470-20B71111BD91}';
    private const CURRENT_STATE = '{445EF730-BA0B-4BDD-92FC-4134A6B97A6E}';
    private const PRINTJOB = '{10408A5C-72CE-4A2D-A2F5-80DB5EB7C79B}';
    private const TEMPERATURES = '{27B4DB7A-166E-40FC-9C61-3570CF3CCC29}';
    private const CONNECTION_HANDLING = '{66A02C84-6839-E682-0C27-9EE36EE88D3E}';
    private const SYSTEM = '{C02C3949-0FFA-4641-ABC0-629046854215}';
    private const TILE = '{83E7FBB0-869E-457C-B034-5E15FA31652A}';

    private const STATUS_ACTIVE = 102;
    private const STATUS_URL_MISSING = 201;
    private const STATUS_NOT_REACHABLE = 202;
    private const STATUS_KEY_MISSING = 204;

    protected function setUp(): void
    {
        // Jeder Test startet mit einem leeren Kernel und lädt die Bibliothek neu
        IPS\Kernel::reset();
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        parent::setUp();
    }

    // --- OctoConnect: Status ---

    public function testConnectWithoutUrlReportsMissingUrl(): void
    {
        $connect = $this->createInstance(self::CONNECT);

        $this->assertSame(self::STATUS_URL_MISSING, $this->instanceStatus($connect));
    }

    public function testConnectWithoutKeyReportsMissingKey(): void
    {
        $connect = $this->createInstance(self::CONNECT, ['APIURL' => 'http://127.0.0.1:9']);

        $this->assertSame(self::STATUS_KEY_MISSING, $this->instanceStatus($connect));
    }

    public function testConnectReportsUnreachableServer(): void
    {
        // Auf Port 9 lauscht niemand, die Anfrage scheitert sofort
        $connect = $this->createInstance(self::CONNECT, ['APIURL' => 'http://127.0.0.1:9', 'APIKey' => 'test']);

        $this->assertSame(self::STATUS_NOT_REACHABLE, $this->instanceStatus($connect));
    }

    // --- OctoConnect: Datenverteilung ---

    public function testConnectIgnoresInvalidData(): void
    {
        $connect = $this->createInstance(self::CONNECT);
        $state = $this->createChild(self::CURRENT_STATE, $connect);

        $this->receiveHex($connect, 'kein json');
        $this->receiveHex($connect, '[1,2');

        $this->assertSame('', GetValueString($this->ident($state, 'CurrentState')));
    }

    public function testConnectDistributesCurrentToChildren(): void
    {
        $connect = $this->createInstance(self::CONNECT);
        $state = $this->createChild(self::CURRENT_STATE, $connect);
        $job = $this->createChild(self::PRINTJOB, $connect);
        $temperatures = $this->createChild(self::TEMPERATURES, $connect);
        $tile = $this->createChild(self::TILE, $connect);

        $this->receiveHex($connect, json_encode(['current' => $this->currentMessage()]));

        $this->assertSame('Printing', GetValueString($this->ident($state, 'CurrentState')));
        $this->assertTrue(GetValueBoolean($this->ident($state, 'Printing')));
        $this->assertSame('benchy.gcode', GetValueString($this->ident($job, 'Filename')));
        $this->assertSame(42, GetValueInteger($this->ident($job, 'Completion')));
        $this->assertEqualsWithDelta(209.6, GetValueFloat($this->ident($temperatures, 'Tool0ActualTemperature')), 0.001);
        $this->assertStringContainsString('benchy.gcode', IPS\InstanceManager::getInstanceInterface($tile)->GetVisualizationTile());
    }

    public function testConnectTreatsHistoryLikeCurrent(): void
    {
        $connect = $this->createInstance(self::CONNECT);
        $state = $this->createChild(self::CURRENT_STATE, $connect);

        $this->receiveHex($connect, json_encode(['history' => $this->currentMessage()]));

        $this->assertSame('Printing', GetValueString($this->ident($state, 'CurrentState')));
    }

    public function testConnectForwardsEventsToCurrentState(): void
    {
        $connect = $this->createInstance(self::CONNECT);
        $state = $this->createChild(self::CURRENT_STATE, $connect);

        $this->receiveHex($connect, json_encode(['event' => ['type' => 'PrintDone', 'payload' => ['name' => 'benchy.gcode']]]));

        $this->assertSame('PrintDone', GetValueString($this->ident($state, 'LastEvent')));
        $this->assertGreaterThan(0, GetValueInteger($this->ident($state, 'LastEventTime')));
    }

    // --- OctoCurrentState ---

    public function testCurrentStateSetsFlagsAndHandlesMissingFlags(): void
    {
        $state = $this->createInstance(self::CURRENT_STATE);

        $this->receive($state, ['state' => ['text' => 'Paused', 'flags' => ['paused' => true, 'operational' => true]]]);

        $this->assertSame('Paused', GetValueString($this->ident($state, 'CurrentState')));
        $this->assertTrue(GetValueBoolean($this->ident($state, 'Paused')));
        $this->assertTrue(GetValueBoolean($this->ident($state, 'Operational')));
        $this->assertFalse(GetValueBoolean($this->ident($state, 'Printing')));
    }

    public function testCurrentStateActionsAreEnabled(): void
    {
        $state = $this->createInstance(self::CURRENT_STATE);

        $this->assertTrue(HasAction($this->ident($state, 'ActionStart')));
        $this->assertTrue(HasAction($this->ident($state, 'ActionPausing')));
        $this->assertFalse(HasAction($this->ident($state, 'Printing')));
    }

    // --- OctoPrintjob ---

    public function testPrintjobStoresTimesInSecondsAndFilament(): void
    {
        $job = $this->createInstance(self::PRINTJOB);

        $this->receive($job, [
            'file'               => ['name' => 'a.gcode', 'path' => 'sub/a.gcode', 'display' => 'A', 'origin' => 'local', 'size' => 2097152, 'date' => 1700000000],
            'estimatedPrintTime' => 7200.4,
            'filament'           => ['tool0' => ['length' => 1500.0, 'volume' => 3.5], 'tool1' => ['length' => 500.0, 'volume' => 1.0]]
        ]);
        $this->receive($job, ['completion' => 12.6, 'filepos' => 1048576, 'printTime' => 90, 'printTimeLeft' => 3600]);
        $this->receive($job, ['currentZ' => 4.2]);

        $this->assertSame('A', GetValueString($this->ident($job, 'Display')));
        $this->assertEqualsWithDelta(2.0, GetValueFloat($this->ident($job, 'Size')), 0.001);
        $this->assertSame(1700000000, GetValueInteger($this->ident($job, 'FileDate')));
        $this->assertSame(7200, GetValueInteger($this->ident($job, 'PrinttimeEstimated')));
        $this->assertEqualsWithDelta(2.0, GetValueFloat($this->ident($job, 'FilamentLength')), 0.001);
        $this->assertEqualsWithDelta(4.5, GetValueFloat($this->ident($job, 'FilamentVolume')), 0.001);
        $this->assertSame(13, GetValueInteger($this->ident($job, 'Completion')));
        $this->assertSame(90, GetValueInteger($this->ident($job, 'Printtime')));
        $this->assertSame(3600, GetValueInteger($this->ident($job, 'PrinttimeLeft')));
        $this->assertEqualsWithDelta(4.2, GetValueFloat($this->ident($job, 'CurrentZ')), 0.001);
    }

    public function testPrintjobHandlesMissingValues(): void
    {
        $job = $this->createInstance(self::PRINTJOB);

        $this->receive($job, ['file' => ['name' => null]]);
        $this->receive($job, ['completion' => null, 'printTime' => null, 'printTimeLeft' => null]);

        $this->assertSame('', GetValueString($this->ident($job, 'Filename')));
        $this->assertSame(0, GetValueInteger($this->ident($job, 'PrinttimeLeft')));
    }

    // --- OctoTemperatures ---

    public function testTemperaturesCreatesVariablesAccordingToConfiguration(): void
    {
        $temperatures = $this->createInstance(self::TEMPERATURES, ['Tool1Temperatures' => true, 'ChamberTemperatures' => true]);

        foreach (['Tool0', 'Tool1', 'Bed', 'Chamber'] as $sensor) {
            $this->assertNotFalse(@IPS_GetObjectIDByIdent($sensor . 'ActualTemperature', $temperatures), $sensor . ' actual');
            $this->assertTrue(HasAction($this->ident($temperatures, $sensor . 'TargetTemperature')), $sensor . ' target');
            $this->assertFalse(HasAction($this->ident($temperatures, $sensor . 'ActualTemperature')), $sensor . ' actual');
        }
    }

    public function testTemperaturesRemovesDisabledVariablesAndIsIdempotent(): void
    {
        $temperatures = $this->createInstance(self::TEMPERATURES, ['Tool1Temperatures' => true]);
        IPS_ApplyChanges($temperatures);
        IPS_ApplyChanges($temperatures);
        $count = count(IPS_GetChildrenIDs($temperatures));

        IPS_SetProperty($temperatures, 'Tool1Temperatures', false);
        IPS_ApplyChanges($temperatures);

        $this->assertSame(6, $count);
        $this->assertSame(4, count(IPS_GetChildrenIDs($temperatures)));
    }

    public function testTemperaturesReadsLastDataPoint(): void
    {
        $temperatures = $this->createInstance(self::TEMPERATURES, ['Tool1Temperatures' => true]);

        $this->receive($temperatures, [
            ['time' => 1, 'tool0' => ['actual' => 20.0, 'target' => 0.0], 'bed' => ['actual' => 20.0, 'target' => 0.0]],
            ['time' => 2, 'tool0' => ['actual' => 210.5, 'target' => 215.0], 'tool1' => ['actual' => 30.0, 'target' => null], 'bed' => ['actual' => 60.0, 'target' => 60.0], 'chamber' => ['actual' => null, 'target' => null]]
        ]);

        $this->assertEqualsWithDelta(210.5, GetValueFloat($this->ident($temperatures, 'Tool0ActualTemperature')), 0.001);
        $this->assertEqualsWithDelta(215.0, GetValueFloat($this->ident($temperatures, 'Tool0TargetTemperature')), 0.001);
        $this->assertEqualsWithDelta(30.0, GetValueFloat($this->ident($temperatures, 'Tool1ActualTemperature')), 0.001);
        $this->assertEqualsWithDelta(60.0, GetValueFloat($this->ident($temperatures, 'BedActualTemperature')), 0.001);
    }

    public function testTemperaturesIgnoresEmptyData(): void
    {
        $temperatures = $this->createInstance(self::TEMPERATURES);

        $this->receive($temperatures, []);
        $this->receive($temperatures, [['time' => 1]]);

        $this->assertEqualsWithDelta(0.0, GetValueFloat($this->ident($temperatures, 'Tool0ActualTemperature')), 0.001);
    }

    // --- OctoConnectionHandling ---

    public function testConnectionHandlingTracksConnectionEvents(): void
    {
        $handling = $this->createInstance(self::CONNECTION_HANDLING);

        $this->receive($handling, ['type' => 'Connected', 'payload' => ['port' => '/dev/ttyUSB0', 'baudrate' => 115200]]);

        $this->assertTrue(GetValueBoolean($this->ident($handling, 'State')));
        $this->assertSame('/dev/ttyUSB0', GetValueString($this->ident($handling, 'Ports')));
        $this->assertSame('115200', GetValueString($this->ident($handling, 'Baudrates')));

        $this->receive($handling, ['type' => 'Disconnected', 'payload' => []]);

        $this->assertFalse(GetValueBoolean($this->ident($handling, 'State')));
    }

    public function testConnectionHandlingStoresSelection(): void
    {
        $handling = $this->createInstance(self::CONNECTION_HANDLING);

        RequestAction($this->ident($handling, 'Ports'), '/dev/ttyACM0');
        RequestAction($this->ident($handling, 'Baudrates'), '250000');

        $this->assertSame('/dev/ttyACM0', GetValueString($this->ident($handling, 'Ports')));
        $this->assertSame('250000', GetValueString($this->ident($handling, 'Baudrates')));
    }

    // --- OctoSystem ---

    public function testSystemControlIsControllable(): void
    {
        $system = $this->createInstance(self::SYSTEM);

        $this->assertTrue(HasAction($this->ident($system, 'SystemControl')));
    }

    // --- OctoTile ---

    public function testTileKeepsLastValuesAndNeedsNoVisualizationConstants(): void
    {
        // Die Konstanten für den Kachel-Typ gibt es erst ab Symcon 9.1, die Instanz muss sich trotzdem anlegen lassen
        $this->assertFalse(defined('INSTANCE_VISUALIZATION_TYPE_HTML'));
        $tile = $this->createInstance(self::TILE);

        $this->receive($tile, $this->currentMessage());
        $this->receive($tile, ['type' => 'PrintPaused', 'payload' => []]);

        $html = IPS\InstanceManager::getInstanceInterface($tile)->GetVisualizationTile();
        $this->assertStringContainsString('handleMessage', $html);
        $this->assertStringContainsString('benchy.gcode', $html);
        $this->assertStringContainsString('PrintPaused', $html);
        $this->assertStringContainsString('No active print job', $html);
    }

    public function testTileShowsEverySensorOctoPrintReports(): void
    {
        // Die Kachel hängt nicht von den Einstellungen anderer Instanzen ab, sie zeigt, was OctoPrint meldet
        $tile = $this->createInstance(self::TILE);

        $this->receive($tile, ['temps' => [['time' => 1, 'tool0' => ['actual' => 20.0, 'target' => 0.0], 'tool1' => ['actual' => 21.0, 'target' => 0.0], 'bed' => ['actual' => 22.0, 'target' => 0.0], 'chamber' => ['actual' => 23.0, 'target' => 0.0]]]]);

        $message = $this->tileMessage($tile);
        $this->assertSame(['tool0', 'tool1', 'bed', 'chamber'], array_keys($message['data']['temps']));
        $this->assertEqualsWithDelta(23.0, $message['data']['temps']['chamber']['actual'], 0.001);
    }

    public function testTileKeepsTemperatureHistory(): void
    {
        $tile = $this->createInstance(self::TILE);

        for ($i = 1; $i <= 3; $i++) {
            $this->receive($tile, ['temps' => [['time' => $i, 'bed' => ['actual' => 20.0 + $i, 'target' => 60.0]]]]);
        }

        $message = $this->tileMessage($tile);
        $this->assertCount(3, $message['history']);
        $this->assertSame([1, 2, 3], array_column($message['history'], 't'));
        $this->assertEqualsWithDelta(23.0, $message['history'][2]['bed'], 0.001);
        $this->assertArrayNotHasKey('history', $message['data']);
    }

    public function testTileAcceptsActionsAndIgnoresInvalidOnes(): void
    {
        $connect = $this->createInstance(self::CONNECT);
        $tile = $this->createChild(self::TILE, $connect);

        // Ohne erreichbaren OctoPrint-Server laufen die Befehle ins Leere, die Kachel darf dabei nicht abbrechen
        IPS_RequestAction($tile, 'pause', true);
        IPS_RequestAction($tile, 'target', json_encode(['sensor' => 'bed', 'value' => 60]));
        IPS_RequestAction($tile, 'target', 'kein json');
        IPS_RequestAction($tile, 'unbekannt', true);

        $this->assertSame(self::STATUS_URL_MISSING, $this->instanceStatus($connect));
    }

    // --- Hilfsmethoden ---

    private function createInstance(string $moduleID, array $properties = []): int
    {
        $id = IPS_CreateInstance($moduleID);
        foreach ($properties as $name => $value) {
            IPS_SetProperty($id, $name, $value);
        }
        IPS_ApplyChanges($id);
        return $id;
    }

    private function createChild(string $moduleID, int $parent): int
    {
        $id = $this->createInstance($moduleID);
        IPS_ConnectInstance($id, $parent);
        return $id;
    }

    // Startdaten der Kachel: handleMessage bekommt einen JSON-Text, der selbst als Text eingebettet ist
    private function tileMessage(int $tile): array
    {
        $html = IPS\InstanceManager::getInstanceInterface($tile)->GetVisualizationTile();
        $this->assertSame(1, preg_match('/handleMessage\((".*")\);<\/script>$/s', $html, $matches));
        return json_decode(json_decode($matches[1], true), true);
    }

    private function instanceStatus(int $id): int
    {
        return IPS_GetInstance($id)['InstanceStatus'];
    }

    private function ident(int $instance, string $ident): int
    {
        return IPS_GetObjectIDByIdent($ident, $instance);
    }

    // Nachricht an ein Kindmodul, der Buffer ist Text
    private function receive(int $instance, array $buffer): void
    {
        IPS\InstanceManager::getInstanceInterface($instance)->ReceiveData(json_encode(['DataID' => '{00000000-0000-0000-0000-000000000000}', 'Buffer' => json_encode($buffer)]));
    }

    // Nachricht des WebSocket Clients an OctoConnect, der Buffer ist bei IPSModuleStrict hexkodiert
    private function receiveHex(int $instance, string $payload): void
    {
        IPS\InstanceManager::getInstanceInterface($instance)->ReceiveData(json_encode(['DataID' => '{00000000-0000-0000-0000-000000000000}', 'Buffer' => bin2hex($payload)]));
    }

    private function currentMessage(): array
    {
        return [
            'state'    => ['text' => 'Printing', 'flags' => ['operational' => true, 'printing' => true]],
            'job'      => ['file' => ['name' => 'benchy.gcode', 'path' => 'benchy.gcode', 'origin' => 'local', 'size' => 1048576]],
            'progress' => ['completion' => 42.3, 'filepos' => 1024, 'printTime' => 1805, 'printTimeLeft' => 3720],
            'temps'    => [['time' => 1, 'tool0' => ['actual' => 209.6, 'target' => 210.0], 'bed' => ['actual' => 60.0, 'target' => 60.0]]],
            'currentZ' => 4.2
        ];
    }
}
