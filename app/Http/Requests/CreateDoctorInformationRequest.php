<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateDoctorInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->hasRole('doctor') && ! isset(Auth::user()->doctorInformation);
    }

    public function rules(): array
    {
        return [
            'speciality' => ['required', 'regex:/^[\pL\s\-]+$/u', 'max:50'],
            'institution' => ['required', 'max:50'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'institution' => ucwords($this['institution']),
            'speciality' => ucwords($this['speciality']),
        ]);
    }
}
