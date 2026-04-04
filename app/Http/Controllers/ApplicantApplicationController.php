<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicantApplicationRequest;
use Illuminate\Http\RedirectResponse;

class ApplicantApplicationController extends Controller
{
    public function create()
    {
        return view('applicant.apply');
    }

    public function store(StoreApplicantApplicationRequest $request): RedirectResponse
    {
        $request->validated();

        // Persistence/workflow integration will be added in the next phase.
        return redirect()
            ->route('applicant.apply')
            ->with('status', 'Application inputs validated and sanitized successfully. Submission storage is the next step.');
    }
}
