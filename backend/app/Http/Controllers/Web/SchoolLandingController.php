<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\View\View;

class SchoolLandingController extends Controller
{
    public function __invoke(School $school): View
    {
        $sliderEnabled = (bool) $school->setting('landing.slider_enabled', false);
        $slides = $sliderEnabled
            ? $school->slides()->where('is_active', true)->get()
            : collect();

        return view('landing.school', compact('school', 'slides', 'sliderEnabled'));
    }
}
