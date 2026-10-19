<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../libs/OctoCockpitCommands.php';

class CockpitCommandsTest extends TestCase
{
    public function testJobCommands(): void
    {
        $this->assertSame(['OCT.ActionStart', ['command' => 'start']], OctoCockpitCommands::build('start', true));
        $this->assertSame(['OCT.ActionStart', ['command' => 'cancel']], OctoCockpitCommands::build('cancel', true));
        $this->assertSame(['OCT.ActionPausing', ['command' => 'pause', 'action' => 'pause']], OctoCockpitCommands::build('pause', true));
        $this->assertSame(['OCT.ActionPausing', ['command' => 'pause', 'action' => 'resume']], OctoCockpitCommands::build('resume', true));
    }

    public function testTargetTemperatureCommands(): void
    {
        $this->assertSame(
            ['Temp.Tool0TargetTemperature', ['command' => 'target', 'targets' => ['tool0' => 210.0]]],
            OctoCockpitCommands::build('target', json_encode(['sensor' => 'tool0', 'value' => 210]))
        );
        $this->assertSame(
            ['Temp.Tool1TargetTemperature', ['command' => 'target', 'targets' => ['tool1' => 55.5]]],
            OctoCockpitCommands::build('target', json_encode(['sensor' => 'tool1', 'value' => '55.5']))
        );
        $this->assertSame(
            ['Temp.BedTargetTemperature', ['command' => 'target', 'target' => 60.0]],
            OctoCockpitCommands::build('target', json_encode(['sensor' => 'bed', 'value' => 60]))
        );
        $this->assertSame(
            ['Temp.ChamberTargetTemperature', ['command' => 'target', 'target' => 0.0]],
            OctoCockpitCommands::build('target', ['sensor' => 'chamber', 'value' => 0])
        );
    }

    public function testTargetTemperatureIsLimited(): void
    {
        $this->assertSame(0.0, OctoCockpitCommands::build('target', json_encode(['sensor' => 'bed', 'value' => -20]))[1]['target']);
        $this->assertSame(400.0, OctoCockpitCommands::build('target', json_encode(['sensor' => 'bed', 'value' => 9999]))[1]['target']);
    }

    public function testInvalidInputIsRejected(): void
    {
        $this->assertNull(OctoCockpitCommands::build('unknown', true));
        $this->assertNull(OctoCockpitCommands::build('target', 'kein json'));
        $this->assertNull(OctoCockpitCommands::build('target', json_encode(['sensor' => 'bed'])));
        $this->assertNull(OctoCockpitCommands::build('target', json_encode(['sensor' => 'bed', 'value' => 'heiß'])));
        $this->assertNull(OctoCockpitCommands::build('target', json_encode(['sensor' => 'motor', 'value' => 50])));
    }
}
