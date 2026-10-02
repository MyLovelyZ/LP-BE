<?php

use App\Rules\ArticleBlock;

/**
 * @return list<string> the messages the rule reported
 */
function articleBlockErrors(mixed $block, string $attribute = 'body.0'): array
{
    $messages = [];

    (new ArticleBlock)->validate($attribute, $block, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    return $messages;
}

it('accepts every block shape the frontend renders', function (mixed $block) {
    expect(articleBlockErrors($block))->toBe([]);
})->with([
    'paragraph' => ['Paragraf biasa.'],
    'heading' => [['heading' => 'Subjudul']],
    'quote without author' => [['quote' => 'Kutipan.']],
    'quote with author' => [['quote' => 'Kutipan.', 'by' => 'Kepala Sekolah']],
    'quote with empty author' => [['quote' => 'Kutipan.', 'by' => null]],
    'list' => [['list' => ['Poin satu', 'Poin dua']]],
]);

it('rejects malformed blocks with a message naming the block', function (mixed $block, string $message) {
    expect(articleBlockErrors($block, 'body.2'))->toBe([$message]);
})->with([
    'blank paragraph' => ['   ', 'Blok isi ke-3: paragraf tidak boleh kosong.'],
    'too long paragraph' => [str_repeat('a', ArticleBlock::MAX_PARAGRAPH_LENGTH + 1), 'Blok isi ke-3: paragraf maksimal 5000 karakter.'],
    'number' => [42, 'Blok isi ke-3 tidak valid.'],
    'plain list instead of block' => [['a', 'b'], 'Blok isi ke-3 tidak valid.'],
    'unknown key' => [['image' => 'foto.jpg'], 'Blok isi ke-3 tidak valid.'],
    'two block types at once' => [['heading' => 'A', 'quote' => 'B'], 'Blok isi ke-3 tidak valid.'],
    'empty heading' => [['heading' => ''], 'Blok isi ke-3: subjudul tidak boleh kosong.'],
    'heading that is not text' => [['heading' => ['A']], 'Blok isi ke-3: subjudul harus berupa teks.'],
    'empty quote' => [['quote' => ' '], 'Blok isi ke-3: kutipan tidak boleh kosong.'],
    'author that is not text' => [['quote' => 'Kutipan.', 'by' => ['Kepala Sekolah']], 'Blok isi ke-3: nama yang dikutip harus berupa teks.'],
    'empty list' => [['list' => []], 'Blok isi ke-3: daftar poin minimal berisi satu poin.'],
    'list that is an object' => [['list' => ['a' => 'Poin']], 'Blok isi ke-3: daftar poin minimal berisi satu poin.'],
    'blank list item' => [['list' => ['Poin', '']], 'Blok isi ke-3: poin ke-2 tidak boleh kosong.'],
    'too many list items' => [['list' => array_fill(0, ArticleBlock::MAX_LIST_ITEMS + 1, 'Poin')], 'Blok isi ke-3: daftar poin maksimal 50 poin.'],
]);
