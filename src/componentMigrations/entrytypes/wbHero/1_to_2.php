<?php

/**
 * wbHero — migration v1 → v2
 *
 * Test migration: updates the instructions on wbTitle to confirm that the
 * ComponentMigrator pipeline (file resolution → action dispatch → state update)
 * works end-to-end.
 *
 * This is a safe, data-preserving operation.
 */
return [
    'from'    => 1,
    'to'      => 2,
    'actions' => [
        [
            'type'     => 'updateFieldSettings',
            'handle'   => 'wbTitle',
            'settings' => [
                'instructions' => 'The main heading displayed on the hero block.',
            ],
        ],
    ],
];
