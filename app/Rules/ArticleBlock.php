<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * One block of an article body, the same shape the frontend renders in ArticleBlock.tsx:
 * a paragraph string, {heading}, {quote, by?}, or {list: string[]}.
 */
class ArticleBlock implements ValidationRule
{
    public const MAX_PARAGRAPH_LENGTH = 5000;

    public const MAX_HEADING_LENGTH = 255;

    public const MAX_QUOTE_LENGTH = 2000;

    public const MAX_LIST_ITEMS = 50;

    public const MAX_LIST_ITEM_LENGTH = 1000;

    /**
     * Run even for blank values; Laravel skips non-implicit rules for whitespace-only strings,
     * which would let an empty paragraph through.
     */
    public bool $implicit = true;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $label = $this->blockLabel($attribute);

        if (is_string($value)) {
            $this->validateText($value, self::MAX_PARAGRAPH_LENGTH, "{$label}: paragraf", $fail);

            return;
        }

        if (! is_array($value) || array_is_list($value)) {
            $fail("{$label} tidak valid.");

            return;
        }

        $keys = array_keys($value);
        sort($keys);

        match ($keys) {
            ['heading'] => $this->validateText($value['heading'], self::MAX_HEADING_LENGTH, "{$label}: subjudul", $fail),
            ['quote'], ['by', 'quote'] => $this->validateQuote($value, $label, $fail),
            ['list'] => $this->validateList($value['list'], $label, $fail),
            default => $fail("{$label} tidak valid."),
        };
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function validateQuote(array $block, string $label, Closure $fail): void
    {
        $this->validateText($block['quote'], self::MAX_QUOTE_LENGTH, "{$label}: kutipan", $fail);

        $by = $block['by'] ?? null;

        if ($by === null) {
            return;
        }

        if (! is_string($by)) {
            $fail("{$label}: nama yang dikutip harus berupa teks.");
        } elseif (mb_strlen($by) > self::MAX_HEADING_LENGTH) {
            $fail("{$label}: nama yang dikutip maksimal ".self::MAX_HEADING_LENGTH.' karakter.');
        }
    }

    private function validateList(mixed $items, string $label, Closure $fail): void
    {
        if (! is_array($items) || ! array_is_list($items) || $items === []) {
            $fail("{$label}: daftar poin minimal berisi satu poin.");

            return;
        }

        if (count($items) > self::MAX_LIST_ITEMS) {
            $fail("{$label}: daftar poin maksimal ".self::MAX_LIST_ITEMS.' poin.');

            return;
        }

        foreach ($items as $index => $item) {
            $this->validateText($item, self::MAX_LIST_ITEM_LENGTH, "{$label}: poin ke-".($index + 1), $fail);
        }
    }

    private function validateText(mixed $text, int $maxLength, string $label, Closure $fail): void
    {
        if (! is_string($text)) {
            $fail("{$label} harus berupa teks.");
        } elseif (trim($text) === '') {
            $fail("{$label} tidak boleh kosong.");
        } elseif (mb_strlen($text) > $maxLength) {
            $fail("{$label} maksimal {$maxLength} karakter.");
        }
    }

    /**
     * "body.2" becomes "Blok isi ke-3" so admins can find the faulty block.
     */
    private function blockLabel(string $attribute): string
    {
        $position = (int) substr(strrchr($attribute, '.') ?: '.0', 1) + 1;

        return "Blok isi ke-{$position}";
    }
}
