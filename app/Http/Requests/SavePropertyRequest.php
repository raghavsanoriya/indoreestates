<?php

namespace App\Http\Requests;

use App\Models\Property;
use App\Rules\Recaptcha;
use App\Support\PropertyPresentation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SavePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property ? $this->user()->can('update', $property) : $this->user()->can('create', Property::class);
    }

    public function rules(): array
    {
        $measurementPresence = $this->route('property')?->source_key ? 'nullable' : 'required';

        return ($this->route('property') ? [] : ['g-recaptcha-response' => [new Recaptcha]]) + [
            'title' => ['required', 'string', 'max:200'], 'description' => ['required', 'string', 'max:20000'],
            'address' => ['required', 'string', 'max:255'], 'location' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'], 'country' => ['required', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'], 'neighborhood' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:Apartment,Villa,House,Studio,Office,Land'], 'purpose' => ['required', 'in:rent,sale'],
            'price' => [$measurementPresence, 'numeric', 'between:0,999999999999.99'], 'rental_period' => ['nullable', 'in:month,week,year'],
            'area' => [$measurementPresence, 'numeric', 'between:1,9999999999'], 'land_area' => ['nullable', 'numeric', 'between:0,9999999999'],
            'rooms' => [$measurementPresence, 'integer', 'between:0,100'], 'bedrooms' => [$measurementPresence, 'integer', 'between:0,100'],
            'bathrooms' => [$measurementPresence, 'integer', 'between:0,100'], 'garages' => [$measurementPresence, 'integer', 'between:0,100'],
            'garage_area' => ['nullable', 'numeric', 'between:0,99999999'], 'year_built' => ['nullable', 'integer', 'between:1800,'.(now()->year + 10)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'], 'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'availability' => ['required', 'in:available,sold,rented'], 'intent' => ['required', 'in:draft,submit,preview'],
            'amenities' => ['nullable', 'array', 'max:30'], 'amenities.*' => ['integer', 'exists:amenities,id'],
            'photos' => ['nullable', 'array', 'max:20'], 'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photos' => ['nullable', 'array'], 'remove_photos.*' => ['integer'],
            'photo_order' => ['nullable', 'array'], 'photo_order.*' => ['integer', 'distinct'],
            'cover_photo' => ['nullable', 'integer'],
            'presentation' => ['nullable', 'array:unit_price,before_price_label,after_price_label,label,tour_mode,tour_url,video_url,floors_enabled,floors,contact_mode,contact_name,contact_email,contact_phone'],
            'presentation.unit_price' => ['nullable', 'in:total,per_sqft'],
            'presentation.before_price_label' => ['nullable', 'string', 'max:50'],
            'presentation.after_price_label' => ['nullable', 'string', 'max:50'],
            'presentation.label' => ['nullable', 'in:None,Featured,New listing,Hot offer'],
            'presentation.tour_mode' => ['nullable', 'in:none,embed,image'],
            'presentation.tour_url' => ['nullable', 'required_if:presentation.tour_mode,embed', 'string', 'max:2000', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value && ! PropertyPresentation::tourUrl($value)) {
                    $fail('Use an HTTPS Matterport, Kuula, Panoee, Giraffe360 or Google Maps embed URL. An iframe embed is also accepted; scripts are never saved.');
                }
            }],
            'presentation.video_url' => ['nullable', 'url:https', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value && ! PropertyPresentation::videoUrl($value)) {
                    $fail('Enter a valid YouTube or Vimeo video URL.');
                }
            }],
            'presentation.floors_enabled' => ['nullable', 'boolean'],
            'presentation.floors' => ['nullable', 'array', 'max:10'],
            'presentation.floors.*' => ['array:id,name,price,price_postfix,size,size_postfix,bedrooms,bathrooms,description'],
            'presentation.floors.*.id' => ['nullable', 'uuid', 'distinct'],
            'presentation.floors.*.name' => ['required', 'string', 'max:100'],
            'presentation.floors.*.price' => ['nullable', 'numeric', 'between:0,999999999999.99'],
            'presentation.floors.*.price_postfix' => ['nullable', 'string', 'max:30'],
            'presentation.floors.*.size' => ['nullable', 'numeric', 'between:0,9999999999'],
            'presentation.floors.*.size_postfix' => ['nullable', 'string', 'max:30'],
            'presentation.floors.*.bedrooms' => ['nullable', 'integer', 'between:0,100'],
            'presentation.floors.*.bathrooms' => ['nullable', 'integer', 'between:0,100'],
            'presentation.floors.*.description' => ['nullable', 'string', 'max:3000'],
            'presentation.contact_mode' => ['nullable', 'in:account,other'],
            'presentation.contact_name' => ['nullable', 'required_if:presentation.contact_mode,other', 'string', 'max:120'],
            'presentation.contact_email' => ['nullable', 'required_if:presentation.contact_mode,other', 'email', 'max:255'],
            'presentation.contact_phone' => ['nullable', 'string', 'max:30'],
            'presentation_files' => ['nullable', 'array:tour,floors'],
            'presentation_files.tour' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'presentation_files.floors' => ['nullable', 'array', 'max:10'],
            'presentation_files.floors.*' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $property = $this->route('property');
            if ($this->input('intent') === 'submit' && mb_strtolower(trim((string) $this->input('location'))) !== 'indore') {
                $validator->errors()->add('location', 'Only properties in Indore can be submitted for publication.');
            }
            $ids = $property ? $property->photos()->pluck('id')->all() : [];
            $removed = $this->input('remove_photos', []);
            $order = $this->input('photo_order', []);
            $cover = $this->input('cover_photo');
            if (array_diff(array_merge($removed, $order, $cover ? [$cover] : []), $ids)) {
                $validator->errors()->add('photos', 'Choose only photos belonging to this property.');
            }
            $count = count($ids) - count(array_unique($removed)) + count($this->file('photos', []));
            if ($count > 20) {
                $validator->errors()->add('photos', 'A property may contain up to 20 photos.');
            }
            if ($this->input('intent') === 'submit' && $count < 1) {
                $validator->errors()->add('photos', 'Add at least one photo before submitting.');
            }
            if ($cover && in_array($cover, $removed)) {
                $validator->errors()->add('cover_photo', 'The cover photo cannot also be removed.');
            }
            if ($this->input('presentation.tour_mode') === 'image' && ! $this->hasFile('presentation_files.tour') && ! data_get($property?->presentation, 'tour_image')) {
                $validator->errors()->add('presentation_files.tour', 'Upload a panoramic image for the virtual tour.');
            }
        }];
    }
}
