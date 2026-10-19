<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class LocalizationTest extends TestCase
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

    // Texte, die Symcon selbst in Formulare einfügt (Spalte der Konfiguratoren)
    private const SYSTEM_TEXTS = ['Instance ID'];

    public function testEveryTextHasAGermanTranslation(): void
    {
        foreach (self::MODULES as $module) {
            $german = $this->germanTranslations($module);
            $missing = array_values(array_filter($this->usedTexts($module), fn (string $text) => !isset($german[$text])));
            $this->assertSame([], $missing, $module . ': Texte ohne deutsche Übersetzung in locale.json');
        }
    }

    public function testTranslationsAreNotEmptyAndAreUsed(): void
    {
        foreach (self::MODULES as $module) {
            $used = array_flip(array_merge($this->usedTexts($module), self::SYSTEM_TEXTS));
            foreach ($this->germanTranslations($module) as $key => $translation) {
                $this->assertNotSame('', trim($translation), $module . ': Leere Übersetzung für "' . $key . '"');
                $this->assertArrayHasKey($key, $used, $module . ': Übersetzung für "' . $key . '" wird nirgends verwendet');
            }
        }
    }

    public function testSourceTextsAreEnglish(): void
    {
        // Die Originaltexte sind englisch: kein Umlaut und kein "ß"
        foreach (self::MODULES as $module) {
            foreach ($this->usedTexts($module) as $text) {
                $this->assertDoesNotMatchRegularExpression('/[äöüÄÖÜß]/u', $text, $module . ': Deutsches Original: ' . $text);
            }
        }
    }

    private function germanTranslations(string $module): array
    {
        $locale = json_decode((string) file_get_contents(__DIR__ . '/../' . $module . '/locale.json'), true);
        return $locale['translations']['de'];
    }

    // Alle Texte aus Translate(), den Namen der Konfigurator-Zeilen und den Beschriftungen der form.json
    private function usedTexts(string $module): array
    {
        $code = (string) file_get_contents(__DIR__ . '/../' . $module . '/module.php');
        preg_match_all("/Translate\\('([^']*)'\\)/", $code, $translate);
        preg_match_all("/\\\$OctoPrintInstanceNames\\[\\d+\\] = '([^']*)';/", $code, $names);

        $form = json_decode((string) file_get_contents(__DIR__ . '/../' . $module . '/form.json'), true);
        $texts = array_merge($translate[1], $names[1]);
        array_walk_recursive($form, function ($value, $key) use (&$texts)
        {
            if (in_array($key, ['caption', 'label'], true) && is_string($value) && $value !== '') {
                $texts[] = $value;
            }
        });
        return array_values(array_unique($texts));
    }
}
