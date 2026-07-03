<?php
namespace App\Http\Controllers\Api;
use App\Enums\ResultEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\ResultEntry;
use App\Models\School;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Services\ScoreEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TeacherResultController extends Controller
{
 public function assignments(Request $request,School $school):JsonResponse
 {
  $user=$request->user();
  $query=TeacherAssignment::with(['term.academicSession','schoolClass','subject'])->where('school_id',$school->id);
  if(!$user->isSchoolManager($school)){$query->where('user_id',$user->id);}
  $items=$query->orderByDesc('academic_term_id')->get()->map(function($a){$total=Student::where('school_id',$a->school_id)->where('school_class_id',$a->school_class_id)->where('is_active',true)->count();$entered=ResultEntry::where('school_id',$a->school_id)->where('academic_term_id',$a->academic_term_id)->where('school_class_id',$a->school_class_id)->where('subject_id',$a->subject_id)->where('status','!=',ResultEntryStatus::NotEntered->value)->count();return ['id'=>$a->id,'session'=>$a->term->academicSession->name,'term'=>$a->term->name,'term_locked'=>$a->term->is_locked,'class'=>$a->schoolClass->name,'subject'=>$a->subject->name,'student_count'=>$total,'entered_count'=>$entered];});
  return response()->json(['data'=>$items]);
 }
 public function roster(Request $request,School $school,TeacherAssignment $assignment):JsonResponse
 {
  $this->authorizeAssignment($request,$school,$assignment);
  $students=Student::where('school_id',$school->id)->where('school_class_id',$assignment->school_class_id)->where('is_active',true)->orderBy('last_name')->orderBy('first_name')->get();
  $entries=ResultEntry::where('school_id',$school->id)->where('academic_term_id',$assignment->academic_term_id)->where('school_class_id',$assignment->school_class_id)->where('subject_id',$assignment->subject_id)->get()->keyBy('student_id');
  $data=$students->map(function($student)use($entries){$entry=$entries->get($student->id);return ['student_id'=>$student->id,'admission_number'=>$student->admission_number,'name'=>$student->full_name,'ca_score'=>$entry?->ca_score!==null?(float)$entry->ca_score:null,'exam_score'=>$entry?->exam_score!==null?(float)$entry->exam_score:null,'total_score'=>$entry?->total_score!==null?(float)$entry->total_score:null,'grade'=>$entry?->grade,'status'=>$entry?->status?->value??ResultEntryStatus::NotEntered->value,'lock_version'=>$entry?->lock_version??0,'updated_at'=>$entry?->updated_at?->toIso8601String()];});
  return response()->json(['assignment'=>['id'=>$assignment->id,'class'=>$assignment->schoolClass->name,'subject'=>$assignment->subject->name,'term'=>$assignment->term->name,'term_locked'=>(bool)$assignment->term->is_locked,'ca_max'=>(float)$school->setting('assessment.ca_max',30),'exam_max'=>(float)$school->setting('assessment.exam_max',70)],'data'=>$data]);
 }
 public function save(Request $request,School $school,TeacherAssignment $assignment,ScoreEntryService $service):JsonResponse
 {
  $data=$request->validate(['student_id'=>['required','integer'],'ca_score'=>['nullable','numeric'],'exam_score'=>['nullable','numeric'],'status'=>['nullable','in:present,absent'],'lock_version'=>['nullable','integer','min:0']]);
  $entry=$service->save($request->user(),$school,$assignment,$data);
  return response()->json(['message'=>'Score saved.','data'=>$this->entryPayload($entry)]);
 }
 public function sync(Request $request,School $school,TeacherAssignment $assignment,ScoreEntryService $service):JsonResponse
 {
  $this->authorizeAssignment($request,$school,$assignment);
  $data=$request->validate(['changes'=>['required','array','max:100'],'changes.*.client_id'=>['required','string','max:80'],'changes.*.student_id'=>['required','integer'],'changes.*.ca_score'=>['nullable','numeric'],'changes.*.exam_score'=>['nullable','numeric'],'changes.*.status'=>['nullable','in:present,absent'],'changes.*.lock_version'=>['nullable','integer','min:0']]);
  $saved=[];$errors=[];
  foreach($data['changes'] as $change){try{$entry=$service->save($request->user(),$school,$assignment,$change);$saved[]=['client_id'=>$change['client_id'],'entry'=>$this->entryPayload($entry)];}catch(\Throwable $e){$errors[]=['client_id'=>$change['client_id'],'message'=>$e->getMessage()];}}
  return response()->json(['saved'=>$saved,'errors'=>$errors],$errors===[]?200:207);
 }
 private function authorizeAssignment(Request $request,School $school,TeacherAssignment $assignment):void
 {
  abort_unless($assignment->school_id===$school->id,404);abort_unless($request->user()->isSchoolManager($school)||$assignment->user_id===$request->user()->id,403,'You are not assigned to this score sheet.');$assignment->loadMissing(['schoolClass','subject','term']);
 }
 private function entryPayload(ResultEntry $entry):array{return ['student_id'=>$entry->student_id,'ca_score'=>$entry->ca_score!==null?(float)$entry->ca_score:null,'exam_score'=>$entry->exam_score!==null?(float)$entry->exam_score:null,'total_score'=>$entry->total_score!==null?(float)$entry->total_score:null,'grade'=>$entry->grade,'remark'=>$entry->remark,'status'=>$entry->status->value,'lock_version'=>$entry->lock_version,'updated_at'=>$entry->updated_at->toIso8601String()];}
}
