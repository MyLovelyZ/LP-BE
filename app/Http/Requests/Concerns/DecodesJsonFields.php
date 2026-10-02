<?php

namespace App\Http\Requests\Concerns;

/**
 * Admin forms are sent as multipart/form-data so they can carry photos. Nested values such as the
 * article body or a facility's feature list are sent as JSON strings, and decoded here before validation.
 */
trait DecodesJsonFields
{
    /**
     * @param  list<string>  $fields
     */
    protected function decodeJsonFields(array $fields): void
    {
        $decoded = [];

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $json = json_decode($value, true);

            // Invalid JSON is left as a string so the "array" rule reports it
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded[$field] = $json;
            }
        }

        $this->merge($decoded);
    }
}
