<?php

namespace fklavyenet\webblocks\services;

use Craft;
use craft\base\Component;
use craft\base\FieldInterface;
use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Checkboxes;
use craft\fields\Color;
use craft\fields\Dropdown;
use craft\fields\Entries;
use craft\fields\Lightswitch;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\RadioButtons;
use craft\fields\Table;
use craft\fields\Tags;
use craft\fields\Url;
use craft\ckeditor\Field as CKEditorField;

/**
 * Creates Craft 5 fields from JSON template definitions.
 *
 * Reads JSON files from src/wbComponents/fields/ and creates the corresponding Craft fields.
 * Supports all standard Craft 5 field types. Matrix fields are handled
 * separately by InstallService (they require entry types to exist first).
 */
class FieldInstallService extends Component
{
    /** Map of JSON type names to Craft 5 field classes. */
    private const TYPE_MAP = [
        'PlainText'    => PlainText::class,
        'Assets'       => Assets::class,
        'Entries'      => Entries::class,
        'Lightswitch'  => Lightswitch::class,
        'Dropdown'     => Dropdown::class,
        'Color'        => Color::class,
        'Url'          => Url::class,
        'Number'       => Number::class,
        'Tags'         => Tags::class,
        'Categories'   => Categories::class,
        'Checkboxes'   => Checkboxes::class,
        'RadioButtons' => RadioButtons::class,
        'Table'        => Table::class,
        'CKEditor'     => CKEditorField::class,
    ];

    /**
     * Create and save a field from a JSON template definition.
     */
    public function installFieldFromTemplate(array $template): ?FieldInterface
    {
        $handle = $template['handle'] ?? null;
        if (!$handle) {
            Craft::warning('Field template missing handle, skipping', __METHOD__);
            return null;
        }

        // Skip if field already exists
        $existing = Craft::$app->getFields()->getFieldByHandle($handle);
        if ($existing) {
            return $existing;
        }

        $typeName = $template['type'] ?? 'PlainText';

        // Skip Matrix fields — handled by InstallService
        if ($typeName === 'Matrix') {
            return null;
        }

        $fieldClass = self::TYPE_MAP[$typeName] ?? PlainText::class;
        $field = new $fieldClass();

        $field->name = $template['name'] ?? $handle;
        $field->handle = $handle;
        $field->instructions = $template['instructions'] ?? '';
        $field->searchable = !empty($template['searchable']);

        if (!empty($template['translationMethod'])) {
            $field->translationMethod = $template['translationMethod'];
        }

        $settings = $template['typesettings'] ?? [];
        $this->_applySettings($field, $typeName, $settings);

        if (!Craft::$app->getFields()->saveField($field)) {
            Craft::error("Failed to save field '$handle': " . implode(', ', $field->getFirstErrors()), __METHOD__);
            return null;
        }

        Craft::info("Created field '$handle' ($fieldClass)", __METHOD__);
        return $field;
    }

    /**
     * Apply type-specific settings to a field instance.
     */
    private function _applySettings(FieldInterface $field, string $typeName, array $settings): void
    {
        switch ($typeName) {
            case 'PlainText':
                if ($field instanceof PlainText) {
                    $field->placeholder = $settings['placeholder'] ?? '';
                    $field->charLimit = !empty($settings['maxLength']) ? (int) $settings['maxLength'] : null;
                    $field->multiline = !empty($settings['multiline']);
                    $field->initialRows = !empty($settings['initialRows']) ? (int) $settings['initialRows'] : 4;
                }
                break;

            case 'Assets':
                if ($field instanceof Assets) {
                    $field->allowedKinds = $settings['allowedKinds'] ?? null;
                    $field->maxRelations = !empty($settings['limit']) ? (int) $settings['limit'] : null;
                    $field->viewMode = $settings['viewMode'] ?? 'list';
                    if (!empty($settings['restrictFiles'])) {
                        $field->restrictFiles = true;
                    }

                    // Resolve default upload location from volume handle to volume:UID format
                    if (!empty($settings['defaultUploadLocationVolume'])) {
                        $volumeHandle = $settings['defaultUploadLocationVolume'];
                        $volume = Craft::$app->getVolumes()->getVolumeByHandle($volumeHandle);
                        if ($volume) {
                            $field->defaultUploadLocationSource = "volume:{$volume->uid}";
                            $field->defaultUploadLocationSubpath = $settings['defaultUploadLocationSubpath'] ?? '';
                        } else {
                            Craft::warning("Volume '$volumeHandle' not found for field '{$field->handle}' default upload location", __METHOD__);
                        }
                    }

                    // Apply sources — resolve volume handles to volume:UID format
                    if (isset($settings['sources'])) {
                        if ($settings['sources'] === '*') {
                            $field->sources = '*';
                        } elseif (is_array($settings['sources'])) {
                            $resolved = [];
                            foreach ($settings['sources'] as $source) {
                                if (str_starts_with($source, 'volume:')) {
                                    $volHandle = substr($source, 7);
                                    $vol = Craft::$app->getVolumes()->getVolumeByHandle($volHandle);
                                    if ($vol) {
                                        $resolved[] = "volume:{$vol->uid}";
                                    }
                                } else {
                                    $resolved[] = $source;
                                }
                            }
                            $field->sources = $resolved;
                        }
                    }
                }
                break;

            case 'Entries':
                if ($field instanceof Entries) {
                    $field->sources = $settings['sources'] ?? null;
                    $field->maxRelations = !empty($settings['limit']) ? (int) $settings['limit'] : null;
                }
                break;

            case 'Lightswitch':
                if ($field instanceof Lightswitch) {
                    $field->default = !empty($settings['default']);
                }
                break;

            case 'Dropdown':
                if ($field instanceof Dropdown) {
                    $field->options = $settings['options'] ?? [];
                }
                break;

            case 'Number':
                if ($field instanceof Number) {
                    $field->min = (isset($settings['min']) && $settings['min'] !== '') ? (int) $settings['min'] : null;
                    $field->max = (isset($settings['max']) && $settings['max'] !== '') ? (int) $settings['max'] : null;
                    $field->decimals = (isset($settings['decimals']) && $settings['decimals'] !== '') ? (int) $settings['decimals'] : 0;
                }
                break;

            case 'Categories':
                if ($field instanceof Categories) {
                    $field->source = $settings['source'] ?? null;
                    $field->maxRelations = !empty($settings['limit']) ? (int) $settings['limit'] : null;
                }
                break;

            case 'Checkboxes':
                if ($field instanceof Checkboxes) {
                    $field->options = $settings['options'] ?? [];
                }
                break;

            case 'RadioButtons':
                if ($field instanceof RadioButtons) {
                    $field->options = $settings['options'] ?? [];
                }
                break;

            case 'Table':
                if ($field instanceof Table) {
                    $field->columns = $settings['columns'] ?? [];
                    $field->defaults = $settings['defaults'] ?? [];
                }
                break;

            case 'CKEditor':
                // Uses CP-level CKEditor config — no field-level settings needed
                break;

            case 'Color':
                if ($field instanceof Color) {
                    $field->allowCustomColors = true;
                }
                break;

            case 'Tags':
                if ($field instanceof Tags) {
                    $field->source = $settings['source'] ?? null;
                    $field->maxRelations = !empty($settings['limit']) ? (int) $settings['limit'] : null;
                }
                break;

            case 'Url':
                // No special settings
                break;
        }
    }
}
