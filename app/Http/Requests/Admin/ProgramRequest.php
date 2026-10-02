<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\DecodesJsonFields;
use App\Models\Program;
use App\Rules\ArticleBlock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating featured programs, the form always sends every field.
 */
class ProgramRequest extends FormRequest
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

        $program = $this->route('program');
        $slug = $this->filled('slug')
            ? Str::slug($this->string('slug')->toString())
            : Program::uniqueSlug($this->string('title')->toString(), $program?->id);

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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('programs', 'slug')->ignore($this->route('program'))],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', 'max:50', 'alpha_dash'],
            'audience' => ['required', 'string', 'max:255'],
            'schedule' => ['required', 'string', 'max:255'],
            'body' => ['required', 'array', 'min:1', 'max:100'],
            'body.*' => [new ArticleBlock],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
