<?php
namespace Database\Seeders;
use App\Enums\MembershipRole;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\ClassSubject;
use App\Models\ResultTemplate;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
 public function run():void
 {
  if(app()->environment(['local','testing'])){$platform=User::firstOrCreate(['email'=>env('SEED_ADMIN_EMAIL','platform@example.test')],['name'=>'Platform Administrator','password'=>Hash::make(env('SEED_ADMIN_PASSWORD','ChangeMe123!')),'is_super_admin'=>true]);}elseif(env('SEED_ADMIN_EMAIL')&&env('SEED_ADMIN_PASSWORD')){User::firstOrCreate(['email'=>env('SEED_ADMIN_EMAIL')],['name'=>env('SEED_ADMIN_NAME','Platform Administrator'),'password'=>Hash::make(env('SEED_ADMIN_PASSWORD')),'is_super_admin'=>true]);}
  if(!app()->environment(['local','testing']))return;
  $school=School::firstOrCreate(['slug'=>'demo-school'],['name'=>'Demo International School','motto'=>'Knowledge, Character, Service','about'=>'A demonstration tenant for testing the result system.','address'=>'Kano, Nigeria','is_active'=>true,'settings'=>['assessment'=>['ca_max'=>30,'exam_max'=>70,'absent_counts_as_zero'=>true]]]);
  $admin=User::firstOrCreate(['email'=>'admin@demo.test'],['name'=>'Demo School Admin','password'=>Hash::make('Password123!')]);$teacher=User::firstOrCreate(['email'=>'teacher@demo.test'],['name'=>'Demo Teacher','password'=>Hash::make('Password123!')]);
  $school->users()->syncWithoutDetaching([$admin->id=>['role'=>MembershipRole::SchoolAdmin->value,'is_active'=>true],$teacher->id=>['role'=>MembershipRole::Teacher->value,'is_active'=>true]]);
  $session=AcademicSession::firstOrCreate(['school_id'=>$school->id,'name'=>'2026/2027'],['is_active'=>true]);$term=AcademicTerm::firstOrCreate(['school_id'=>$school->id,'academic_session_id'=>$session->id,'name'=>'First Term'],['is_active'=>true]);$class=SchoolClass::firstOrCreate(['school_id'=>$school->id,'name'=>'JSS 1 A'],['level'=>'JSS 1','arm'=>'A','class_teacher_id'=>$teacher->id]);$subject=Subject::firstOrCreate(['school_id'=>$school->id,'name'=>'Mathematics'],['code'=>'MTH']);ClassSubject::firstOrCreate(['school_id'=>$school->id,'school_class_id'=>$class->id,'subject_id'=>$subject->id]);TeacherAssignment::firstOrCreate(['school_id'=>$school->id,'user_id'=>$teacher->id,'academic_term_id'=>$term->id,'school_class_id'=>$class->id,'subject_id'=>$subject->id]);
  ResultTemplate::firstOrCreate(['school_id'=>$school->id,'name'=>'Modern Teal'],['layout'=>'modern','is_default'=>true,'settings'=>['primary_color'=>'#0f766e','accent_color'=>'#f59e0b','show_subject_position'=>true,'show_grading_scale'=>true,'show_verification_code'=>true]]);
  foreach(range(1,10) as $number){Student::firstOrCreate(['school_id'=>$school->id,'admission_number'=>'DEMO-'.str_pad((string)$number,3,'0',STR_PAD_LEFT)],['school_class_id'=>$class->id,'first_name'=>'Student','last_name'=>(string)$number,'gender'=>$number%2?'male':'female','portal_pin_hash'=>Hash::make('123456'),'is_active'=>true]);}
 }
}
