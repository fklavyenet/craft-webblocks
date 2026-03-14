<?php

/**
 * WebBlocks Component Migration — DSL Reference
 *
 * File naming convention:
 *   src/componentMigrations/{type}/{handle}/{from}_to_{to}.php
 *
 * Example:
 *   src/componentMigrations/entrytypes/wbHero/1_to_2.php
 *   src/componentMigrations/fields/wbButtonColor/1_to_2.php
 *
 * Each file must return an array with the following keys:
 *
 *   from    (int)    Source version this migration applies FROM
 *   to      (int)    Target version this migration applies TO
 *   actions (array)  List of action definitions (see below)
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * SUPPORTED ACTION TYPES
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * renameField
 *   Renames a field handle in Craft. Safe — no content loss.
 *   {
 *     'type'      => 'renameField',
 *     'oldHandle' => 'wbButtonText',
 *     'newHandle' => 'wbCtaText',
 *   }
 *
 * addField
 *   Creates a new field using a JSON definition file path (relative to
 *   src/wbComponents/fields/). Safe — additive change.
 *   {
 *     'type'       => 'addField',
 *     'handle'     => 'wbButtonStyle',
 *     'definition' => 'fields/wbButtonStyle.json',  // path within wbComponents/
 *   }
 *
 * removeField
 *   Deletes a field. DESTRUCTIVE — content will be lost.
 *   Mark 'risk' => 'high' to require manual confirmation.
 *   {
 *     'type'   => 'removeField',
 *     'handle' => 'wbLegacyText',
 *     'risk'   => 'high',
 *   }
 *
 * deprecateField
 *   Marks a field as deprecated in the component state; removes it from
 *   field layouts but keeps the field and its data in the DB.
 *   Safe — content preserved.
 *   {
 *     'type'   => 'deprecateField',
 *     'handle' => 'wbOldCaption',
 *   }
 *
 * updateFieldSettings
 *   Updates settings on an existing field (e.g. add dropdown options).
 *   Generally safe; content-destructive only if column type changes.
 *   {
 *     'type'     => 'updateFieldSettings',
 *     'handle'   => 'wbAlignment',
 *     'settings' => ['options' => [['label' => 'Justify', 'value' => 'justify']]],
 *   }
 *
 * updateFieldLayout
 *   Reorders or reorganises fields within a tab on an entry type layout.
 *   Safe — structural only.
 *   {
 *     'type'      => 'updateFieldLayout',
 *     'entryType' => 'wbHero',
 *     'tab'       => 'Content',
 *     'fields'    => ['wbTitle', 'wbCtaText', 'wbImage'],  // new order
 *   }
 *
 * addMatrixBlockType
 *   Adds a new entry type to an existing matrix field. Safe.
 *   {
 *     'type'        => 'addMatrixBlockType',
 *     'matrixField' => 'wbBlocks',
 *     'entryType'   => 'wbNewBlock',
 *   }
 *
 * removeMatrixBlockType
 *   Removes an entry type from a matrix field. DESTRUCTIVE if entries exist.
 *   {
 *     'type'        => 'removeMatrixBlockType',
 *     'matrixField' => 'wbBlocks',
 *     'entryType'   => 'wbOldBlock',
 *     'risk'        => 'high',
 *   }
 *
 * renameMatrixBlockType
 *   Renames a matrix block entry type handle. Safe — Craft updates references.
 *   {
 *     'type'        => 'renameMatrixBlockType',
 *     'matrixField' => 'wbBlocks',
 *     'oldHandle'   => 'wbOldBlock',
 *     'newHandle'   => 'wbNewBlock',
 *   }
 *
 * copyContent
 *   Copies content from one field to another (same entry type). Use when
 *   renaming a field in place with data migration.
 *   {
 *     'type'        => 'copyContent',
 *     'fromField'   => 'wbButtonText',
 *     'toField'     => 'wbCtaText',
 *     'entryType'   => 'wbCallToAction',
 *   }
 *
 * transformContent
 *   Runs a custom PHP closure over each entry's field value. Advanced.
 *   'risk' => 'high' required — always dry-run first.
 *   {
 *     'type'      => 'transformContent',
 *     'field'     => 'wbOverlayOpacity',
 *     'entryType' => 'wbHero',
 *     'risk'      => 'high',
 *     'transform' => function (mixed $value): mixed {
 *         // Example: migrate numeric string "40" to integer 40
 *         return (int) $value;
 *     },
 *   }
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * RISK LEVELS
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   (none)      Safe to apply automatically
 *   'medium'    Proceed with caution; included in dry-run warnings
 *   'high'      Requires explicit --force flag or manual confirmation;
 *               always added to manualReviewRequired in the report
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * FULL EXAMPLE
 * ─────────────────────────────────────────────────────────────────────────────
 */

return [
    'from' => 1,
    'to'   => 2,

    // Optional: human-readable description shown in dry-run and health screen
    'description' => 'Rename wbButtonText → wbCtaText; add wbButtonStyle field; reorder layout.',

    'actions' => [

        // 1. Copy content before renaming (safe, no risk)
        [
            'type'      => 'copyContent',
            'fromField' => 'wbButtonText',
            'toField'   => 'wbCtaText',
            'entryType' => 'wbCallToAction',
        ],

        // 2. Rename the field handle
        [
            'type'      => 'renameField',
            'oldHandle' => 'wbButtonText',
            'newHandle' => 'wbCtaText',
        ],

        // 3. Add a new field
        [
            'type'       => 'addField',
            'handle'     => 'wbButtonStyle',
            'definition' => 'fields/wbButtonStyle.json',
        ],

        // 4. Update the field layout to include the new field
        [
            'type'      => 'updateFieldLayout',
            'entryType' => 'wbCallToAction',
            'tab'       => 'Content',
            'fields'    => ['wbTitle', 'wbCtaText', 'wbButtonStyle', 'wbImage'],
        ],

        // 5. Deprecate the old field (keep data, remove from layout)
        [
            'type'   => 'deprecateField',
            'handle' => 'wbButtonText',
        ],

    ],
];
