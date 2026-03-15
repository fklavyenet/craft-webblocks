<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * FormControllerPureTest
 *
 * Tests the pure (Craft-free) helper logic extracted from FormController:
 *   - _fieldKey()  — label → HTML input name slugification
 *   - _replacePlaceholders()  — {FieldLabel} substitution
 *   - Required-field validation logic (pure PHP replication)
 *
 * Because FormController extends craft\web\Controller (which requires a full
 * Craft bootstrap), we test the logic by replicating it via trait-style
 * anonymous classes rather than instantiating the controller directly.
 */

/**
 * Minimal helper class that exposes the pure helper methods via Reflection
 * without instantiating FormController (which needs Craft::$app).
 */
class FormControllerHelpers
{
    /** Replicates FormController::_fieldKey() */
    public function fieldKey(string $label): string
    {
        $key = mb_strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key);
        return trim($key, '-');
    }

    /** Replicates FormController::_replacePlaceholders() */
    public function replacePlaceholders(string $text, array $values): string
    {
        foreach ($values as $label => $value) {
            $placeholder = '{' . $label . '}';
            $text = str_replace($placeholder, (string) $value, $text);
        }
        return $text;
    }

    /**
     * Replicates the required-field validation loop from FormController::actionSubmit().
     *
     * @param  array $fields  Array of ['label'=>string, 'required'=>bool, 'value'=>string]
     * @return array          Map of inputName => error message (empty if no errors)
     */
    public function validateRequiredFields(array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            $label    = $field['label'];
            $required = $field['required'];
            $value    = $field['value'];
            $inputName = $this->fieldKey($label);

            if ($required && trim((string) $value) === '') {
                $errors[$inputName] = $label . ' is required.';
            }
        }
        return $errors;
    }

    /**
     * Replicates the visitor-email detection logic from FormController::actionSubmit().
     *
     * @param  array $fields  Array of ['type'=>string, 'value'=>string]
     * @return string|null    First non-empty email field value, or null
     */
    public function detectVisitorEmail(array $fields): ?string
    {
        $visitorEmail = null;
        foreach ($fields as $field) {
            $type  = $field['type'];
            $value = $field['value'];
            if ($type === 'email' && $visitorEmail === null && trim((string) $value) !== '') {
                $visitorEmail = trim((string) $value);
                break;
            }
        }
        return $visitorEmail;
    }
}

// ──────────────────────────────────────────────────────────────────────────────

class FormControllerPureTest extends TestCase
{
    private FormControllerHelpers $h;

    protected function setUp(): void
    {
        $this->h = new FormControllerHelpers();
    }

    // -------------------------------------------------------------------------
    // _fieldKey() — label slugification
    // -------------------------------------------------------------------------

    public function testFieldKeyLowercasesLabel(): void
    {
        $this->assertSame('name', $this->h->fieldKey('Name'));
        $this->assertSame('full-name', $this->h->fieldKey('Full Name'));
    }

    public function testFieldKeyReplacesSpecialCharsWithHyphens(): void
    {
        $this->assertSame('e-mail-address', $this->h->fieldKey('E-Mail Address'));
        $this->assertSame('phone-number', $this->h->fieldKey('Phone  Number'));
    }

    public function testFieldKeyTrimsLeadingAndTrailingHyphens(): void
    {
        $this->assertSame('message', $this->h->fieldKey('  Message  '));
    }

    public function testFieldKeyHandlesAllNumericLabel(): void
    {
        $this->assertSame('123', $this->h->fieldKey('123'));
    }

    public function testFieldKeyCollapsesManySpecialCharsIntoOneHyphen(): void
    {
        $this->assertSame('hello-world', $this->h->fieldKey('Hello___World'));
    }

    public function testFieldKeyProducesConsistentSlug(): void
    {
        // Same label must always produce the same key
        $label = 'Your Message';
        $this->assertSame($this->h->fieldKey($label), $this->h->fieldKey($label));
    }

    // -------------------------------------------------------------------------
    // _replacePlaceholders() — {Label} substitution
    // -------------------------------------------------------------------------

    public function testReplacePlaceholdersSubstitutesValues(): void
    {
        $text   = 'Hello {Name}, your email is {Email}.';
        $values = ['Name' => 'Alice', 'Email' => 'alice@example.com'];
        $result = $this->h->replacePlaceholders($text, $values);
        $this->assertSame('Hello Alice, your email is alice@example.com.', $result);
    }

    public function testReplacePlaceholdersMissingKeyLeftAsIs(): void
    {
        $text   = 'Hello {Name}, your phone is {Phone}.';
        $values = ['Name' => 'Bob'];
        $result = $this->h->replacePlaceholders($text, $values);
        // {Phone} has no value — stays unreplaced
        $this->assertSame('Hello Bob, your phone is {Phone}.', $result);
    }

    public function testReplacePlaceholdersEmptyValues(): void
    {
        $text   = 'No placeholders here.';
        $values = [];
        $this->assertSame($text, $this->h->replacePlaceholders($text, $values));
    }

    public function testReplacePlaceholdersCaseSensitive(): void
    {
        $text   = 'Hi {name} and {Name}.';
        $values = ['Name' => 'Carol'];
        $result = $this->h->replacePlaceholders($text, $values);
        // Only {Name} matches; {name} stays
        $this->assertSame('Hi {name} and Carol.', $result);
    }

    public function testReplacePlaceholdersMultipleOccurrences(): void
    {
        $text   = '{Greeting} world. {Greeting} everyone.';
        $values = ['Greeting' => 'Hello'];
        $result = $this->h->replacePlaceholders($text, $values);
        $this->assertSame('Hello world. Hello everyone.', $result);
    }

    // -------------------------------------------------------------------------
    // Required-field validation
    // -------------------------------------------------------------------------

    public function testNoErrorsWhenAllRequiredFieldsFilled(): void
    {
        $fields = [
            ['label' => 'Name',    'required' => true,  'value' => 'Alice'],
            ['label' => 'Email',   'required' => true,  'value' => 'alice@example.com'],
            ['label' => 'Message', 'required' => false, 'value' => ''],
        ];
        $errors = $this->h->validateRequiredFields($fields);
        $this->assertEmpty($errors);
    }

    public function testErrorWhenRequiredFieldIsEmpty(): void
    {
        $fields = [
            ['label' => 'Name', 'required' => true, 'value' => ''],
        ];
        $errors = $this->h->validateRequiredFields($fields);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testErrorWhenRequiredFieldIsWhitespaceOnly(): void
    {
        $fields = [
            ['label' => 'Name', 'required' => true, 'value' => '   '],
        ];
        $errors = $this->h->validateRequiredFields($fields);
        $this->assertArrayHasKey('name', $errors);
    }

    public function testNoErrorWhenOptionalFieldIsEmpty(): void
    {
        $fields = [
            ['label' => 'Phone', 'required' => false, 'value' => ''],
        ];
        $errors = $this->h->validateRequiredFields($fields);
        $this->assertEmpty($errors);
    }

    public function testMultipleRequiredFieldErrors(): void
    {
        $fields = [
            ['label' => 'Name',    'required' => true, 'value' => ''],
            ['label' => 'Email',   'required' => true, 'value' => ''],
            ['label' => 'Message', 'required' => true, 'value' => 'Hello'],
        ];
        $errors = $this->h->validateRequiredFields($fields);
        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('name',  $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    // -------------------------------------------------------------------------
    // Visitor email detection
    // -------------------------------------------------------------------------

    public function testDetectsFirstEmailFieldValue(): void
    {
        $fields = [
            ['type' => 'text',  'value' => 'Alice'],
            ['type' => 'email', 'value' => 'alice@example.com'],
        ];
        $this->assertSame('alice@example.com', $this->h->detectVisitorEmail($fields));
    }

    public function testIgnoresEmptyEmailFieldAndFallsThrough(): void
    {
        $fields = [
            ['type' => 'email', 'value' => ''],
            ['type' => 'email', 'value' => 'bob@example.com'],
        ];
        $this->assertSame('bob@example.com', $this->h->detectVisitorEmail($fields));
    }

    public function testReturnsNullWhenNoEmailField(): void
    {
        $fields = [
            ['type' => 'text',    'value' => 'Alice'],
            ['type' => 'textarea','value' => 'Hello'],
        ];
        $this->assertNull($this->h->detectVisitorEmail($fields));
    }

    public function testTrimmsVisitorEmailWhitespace(): void
    {
        $fields = [
            ['type' => 'email', 'value' => '  carol@example.com  '],
        ];
        $this->assertSame('carol@example.com', $this->h->detectVisitorEmail($fields));
    }

    public function testOnlyFirstEmailFieldIsCaptured(): void
    {
        // Even if second email field has a value, only the first non-empty one wins
        $fields = [
            ['type' => 'email', 'value' => 'first@example.com'],
            ['type' => 'email', 'value' => 'second@example.com'],
        ];
        $this->assertSame('first@example.com', $this->h->detectVisitorEmail($fields));
    }
}
