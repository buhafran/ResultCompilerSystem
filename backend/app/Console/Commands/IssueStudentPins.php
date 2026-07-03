<?php
namespace App\Console\Commands;
use App\Models\School;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class IssueStudentPins extends Command
{
 protected $signature='students:issue-pins {school : School slug} {--class= : Optional school class ID} {--force : Replace PINs already issued}';protected $description='Generate one-time student portal PINs and write a protected CSV for secure distribution.';
 public function handle():int{$school=School::where('slug',$this->argument('school'))->firstOrFail();$query=Student::where('school_id',$school->id)->where('is_active',true);if($this->option('class'))$query->where('school_class_id',$this->option('class'));if(!$this->option('force'))$query->whereNull('portal_pin_hash');$path=storage_path('app/private/student-pins-'.$school->slug.'-'.now()->format('Ymd-His').'.csv');if(!is_dir(dirname($path)))mkdir(dirname($path),0700,true);$handle=fopen($path,'wb');fputcsv($handle,['admission_number','student_name','portal_pin']);$count=0;$query->orderBy('id')->chunkById(500,function($students)use($handle,&$count){foreach($students as $student){$pin=(string)random_int(100000,999999);$student->update(['portal_pin_hash'=>Hash::make($pin)]);fputcsv($handle,[$student->admission_number,$student->full_name,$pin]);$count++;}});fclose($handle);chmod($path,0600);$this->info("Issued {$count} PINs. Private CSV: {$path}");$this->warn('Transmit the CSV securely, then delete it after distribution. PINs are not recoverable from the database.');return self::SUCCESS;}
}
