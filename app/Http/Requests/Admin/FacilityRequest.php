<?php

namespace App\Http\Requests\Admin;

use App\Enums\FacilityCategory;
use App\Enums\MajorCode;
use App\Http\Requests\Concerns\DecodesJsonFields;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Used for both creating and updating facilities.
 *
 * Photos: `images[]` are new uploads appended at the end. On update, `image_ids` (JSON list) is the
 * complete list of existing photos to keep, in display order; photos left out are deleted.
 * When `image_ids` is omitted, existing photos are left untouched.
 */
class FacilityRequest extends FormRequest
{
    use DecodesJsonFields;

    public const MAX_IMAGES = 12;

    /**
     * Every account is an administrator, so being authenticated (enforced by the route) is enough.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->decodeJsonFields(['features', 'majors', 'image_ids']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $facility = $this->route('facility');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'icon' => ['required', 'string', 'max:50', 'alpha_dash'],
            'category' => ['required', Rule::enum(FacilityCategory::class)],
            'features' => ['required', 'array', 'min:1', 'max:20'],
            'features.*' => ['required', 'string', 'max:255', 'distinct'],
            'majors' => ['present', 'array'],
            'majors.*' => [Rule::enum(MajorCode::class), 'distinct'],
            'images' => ['sometimes', 'array', 'max:'.self::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_ids' => [$facility ? 'sometimes' : 'prohibited', 'array'],
            'image_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('facility_images', 'id')->where('facility_id', $facility?->id),
            ],
        ];
    }
}
