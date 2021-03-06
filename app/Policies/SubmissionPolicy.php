<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAll(User $user): bool
    {
        return $user->hasRole('patient') || ($user->hasRole('doctor') && $user->doctorInformation);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('patient') && $user->patientInformation;
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->id == $submission->patient_id;
    }

    public function assign(User $user, Submission $submission): bool
    {
        return (! $submission->doctor_id) && ($user->hasRole('doctor')) && ($user->doctorInformation);
    }

    public function update(User $user, Submission $submission)
    {
        return ($user->id == $submission->patient_id) && (boolval($submission->doctor_id) == false);
    }

    public function view(User $user, Submission $submission): bool
    {
        $isAssigned = $submission->doctor_id != null;
        $canDoctorView = (! $isAssigned && $user->hasRole('doctor')) || ($submission->doctor_id == $user->id);
        $canPatientView = $user->id == $submission->patient_id;

        return $canDoctorView || $canPatientView;
    }

    public function diagnose(User $user, Submission $submission): bool
    {
        return (! $submission->diagnosis) && ($submission->doctor_id == $user->id) && ($user->hasRole('doctor') && ($user->doctorInformation));
    }

    public function deleteDiagnosis(User $user, Submission $submission): bool
    {
        return ($submission->diagnosis) && ($user->hasRole('doctor') && ($user->doctorInformation));
    }
}
