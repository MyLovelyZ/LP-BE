<?php

namespace App\Http\Requests\Admin;

use App\Enums\NewsCategory;
use App\Http\Requests\Concerns\DecodesJsonFields;
use App\Models\News;
use App\Rules\ArticleBlock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating news, the form always sends every field.
 */
class NewsRequest extends FormRequest
{
    use DecodesJsonFields;

    /**
     * Every account is an administrator, so being authenticated (enforced by the route) is enough.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->decodeJsonFields(['body']);

        // An empty slug is generated from the title; a typed slug is normalised and must then be unique
        $news = $this->route('news');
        $slug = $this->filled('slug')
            ? Str::slug($this->string('slug')->toString())
            : News::uniqueSlug($this->string('title')->toString(), $news?->id);

        $this->merge(['slug' => $slug]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('news', 'slug')->ignore($this->route('news'))],
            'category' => ['required', Rule::enum(NewsCategory::class)],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'array', 'min:1', 'max:100'],
            'body.*' => [new ArticleBlock],
            'author' => ['nullable', 'string', 'max:255'],
            'is_published' => ['required', 'boolean'],
            'published_at' => ['required', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
