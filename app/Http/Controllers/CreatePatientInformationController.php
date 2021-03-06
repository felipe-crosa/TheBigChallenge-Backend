<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePatientInformationRequest;
use App\Http\Resources\PatientInformationResource;
use Illuminate\Support\Facades\Auth;

class CreatePatientInformationController
{
    public function __invoke(CreatePatientInformationRequest $request): PatientInformationResource
    {
        $patient = Auth::user()->patientInformation()->create($request->validated());

        return (new PatientInformationResource($patient))
            ->additional([
                'test' => Auth::user(),
                'status' => 200,
                'message' => 'Information has been successfully created',
            ]);
    }
}
