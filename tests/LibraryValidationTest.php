<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryValidationTest extends TestCaseSymconValidation
{
    private const MODULES = [
        'OctoConfigurator',
        'OctoConnect',
        'OctoConnectionHandling',
        'OctoCurrentState',
        'OctoDiscovery',
        'OctoPrintjob',
        'OctoSystem',
        'OctoTemperatures',
        'OctoTile'
    ];

    // Prüft library.json, module.json, form.json und locale.json der Bibliothek mit dem Validator der Symcon-Stubs
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateModules(): void
    {
        foreach (self::MODULES as $module) {
            $this->validateModule(__DIR__ . '/../' . $module);
        }
    }
}
