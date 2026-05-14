<?php

namespace App\Http\Requests;

use App\Models\Beneficiary;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBeneficiaryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        [$firstName, $middleName, $lastName] = $this->normalizedNameParts();

        $this->merge([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'full_name' => collect([$firstName, $middleName, $lastName])->filter()->implode(' '),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'full_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'barangay' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(Beneficiary::genders())],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'civil_status' => ['required', Rule::in(Beneficiary::civilStatuses())],
            'valid_id' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'category' => ['required', Rule::in(Beneficiary::categories())],
            'status' => ['required', Rule::in(Beneficiary::statuses())],
            'date_issued' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function normalizedNameParts(): array
    {
        $firstName = trim((string) $this->input('first_name', ''));
        $middleName = trim((string) $this->input('middle_name', ''));
        $lastName = trim((string) $this->input('last_name', ''));

        if ($firstName !== '' || $middleName !== '' || $lastName !== '') {
            return [$firstName, $middleName, $lastName];
        }

        return $this->splitFullName((string) $this->input('full_name', ''));
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if ($parts === []) {
            return ['', '', ''];
        }

        if (count($parts) === 1) {
            return [$parts[0], '', ''];
        }

        $firstName = array_shift($parts) ?? '';
        $lastName = array_pop($parts) ?? '';

        return [$firstName, implode(' ', $parts), $lastName];
    }
}
