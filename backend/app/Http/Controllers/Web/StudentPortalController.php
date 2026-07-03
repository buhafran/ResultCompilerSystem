<?php
namespace App\Http\Controllers\Web;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ResultSummary;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class StudentPortalController extends Controller
{
 public function loginForm(School $school):View{return view('portal.login',compact('school'));}
 public function authenticate(Request $request,School $school):RedirectResponse
 {
  $data=$request->validate(['admission_number'=>['required','string','max:100'],'pin'=>['required','string','min:4','max:20']]);$student=Student::where('school_id',$school->id)->where('admission_number',$data['admission_number'])->where('is_active',true)->first();
  if(!$student||!$student->portal_pin_hash||!Hash::check($data['pin'],$student->portal_pin_hash)){return back()->withErrors(['admission_number'=>'Invalid admission number or PIN.'])->onlyInput('admission_number');}
  $request->session()->regenerate();$request->session()->put("student_portal.{$school->id}",$student->id);return redirect()->route('school.portal.results',$school);
 }
 public function results(Request $request,School $school):View
 {
  $studentId=$request->session()->get("student_portal.{$school->id}");abort_unless($studentId,403,'Please sign in to the student portal.');$student=Student::where('school_id',$school->id)->findOrFail($studentId);$student->loadMissing('schoolClass');
  $results=ResultSummary::with(['term.academicSession','schoolClass','publication'])->where('school_id',$school->id)->where('student_id',$student->id)->whereNotNull('released_at')->whereHas('publication',fn($q)=>$q->where('status',PublicationStatus::Released->value))->latest('released_at')->get();return view('portal.results',compact('school','student','results'));
 }
 public function logout(Request $request,School $school):RedirectResponse{$request->session()->forget("student_portal.{$school->id}");$request->session()->regenerateToken();return redirect()->route('school.portal.login',$school);}
}
