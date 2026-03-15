<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * TranslationSyncTest
 *
 * Verifies that all four translation files (en, tr, de, es) contain exactly
 * the same set of keys. This is a pure-PHP test — no Craft bootstrap needed.
 */
class TranslationSyncTest extends TestCase
{
    /** @var string[] */
    private array $locales = ['en', 'tr', 'de', 'es'];

    /** @var string */
    private string $translationsDir;

    protected function setUp(): void
    {
        $this->translationsDir = dirname(__DIR__, 2) . '/src/translations';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Load and return the PHP translation array for a given locale. */
    private function loadTranslations(string $locale): array
    {
        $path = $this->translationsDir . '/' . $locale . '/webblocks.php';
        $this->assertFileExists($path, "Translation file for locale '$locale' not found at $path");
        $data = require $path;
        $this->assertIsArray($data, "Translation file for '$locale' must return an array");
        return $data;
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /** Every locale file must be loadable and return a non-empty array. */
    public function testAllLocaleFilesExistAndAreNonEmpty(): void
    {
        foreach ($this->locales as $locale) {
            $data = $this->loadTranslations($locale);
            $this->assertNotEmpty($data, "Translation file for '$locale' must not be empty");
        }
    }

    /** All keys must be strings (no numeric/array keys). */
    public function testAllKeysAreStrings(): void
    {
        foreach ($this->locales as $locale) {
            $data = $this->loadTranslations($locale);
            foreach (array_keys($data) as $key) {
                $this->assertIsString($key, "Locale '$locale' has a non-string key");
            }
        }
    }

    /** All values must be strings (no nested arrays). */
    public function testAllValuesAreStrings(): void
    {
        foreach ($this->locales as $locale) {
            $data = $this->loadTranslations($locale);
            foreach ($data as $key => $value) {
                $this->assertIsString($value, "Locale '$locale', key '$key' must be a string value");
            }
        }
    }

    /** Every locale must have the exact same key count as English. */
    public function testAllLocalesHaveSameKeyCountAsEnglish(): void
    {
        $en    = $this->loadTranslations('en');
        $enCount = count($en);

        foreach ($this->locales as $locale) {
            if ($locale === 'en') {
                continue;
            }
            $data = $this->loadTranslations($locale);
            $this->assertCount(
                $enCount,
                $data,
                "Locale '$locale' has " . count($data) . " keys but EN has $enCount"
            );
        }
    }

    /** Every locale must contain all keys present in the English file. */
    public function testAllLocalesContainAllEnglishKeys(): void
    {
        $en = $this->loadTranslations('en');

        foreach ($this->locales as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $data    = $this->loadTranslations($locale);
            $missing = array_diff_key($en, $data);

            $this->assertEmpty(
                $missing,
                "Locale '$locale' is missing " . count($missing) . " key(s) from EN: "
                    . implode(', ', array_keys($missing))
            );
        }
    }

    /** No locale may contain keys that do not exist in English (extra/stale keys). */
    public function testNoLocaleHasExtraKeysBeyondEnglish(): void
    {
        $en = $this->loadTranslations('en');

        foreach ($this->locales as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $data  = $this->loadTranslations($locale);
            $extra = array_diff_key($data, $en);

            $this->assertEmpty(
                $extra,
                "Locale '$locale' has " . count($extra) . " extra key(s) not in EN: "
                    . implode(', ', array_keys($extra))
            );
        }
    }

    /** Every locale file must contain exactly 147 keys (the agreed count). */
    public function testEveryLocaleHas147Keys(): void
    {
        foreach ($this->locales as $locale) {
            $data = $this->loadTranslations($locale);
            $this->assertCount(147, $data, "Locale '$locale' must have exactly 147 keys, found " . count($data));
        }
    }

    /** No translation value in any locale may be an empty string. */
    public function testNoEmptyTranslationValues(): void
    {
        foreach ($this->locales as $locale) {
            $data = $this->loadTranslations($locale);
            foreach ($data as $key => $value) {
                $this->assertNotSame(
                    '',
                    $value,
                    "Locale '$locale', key '$key' has an empty translation value"
                );
            }
        }
    }
}
