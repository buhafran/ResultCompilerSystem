<?php
namespace App\Filament\School\Resources\ResultPublicationResource\Pages;
use App\Filament\School\Resources\ResultPublicationResource;
use App\Models\AcademicTerm;
use App\Models\ResultTemplate;
use App\Models\SchoolClass;
use App\Services\ResultCompilerService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
class ListResultPublications extends ListRecords
{
 protected static string $resource=ResultPublicationResource::class;
 protected function getHeaderActions():array{return [Action::make('compile')->label('Compile new result version')->icon('heroicon-o-calculator')->color('primary')->modalDescription('Compilation locks the selected term before creating an immutable result snapshot. Unlock the term only when corrections are required, then compile a new version.')->schema([
   Select::make('academic_term_id')->label('Term')->options(fn()=>AcademicTerm::where('school_id',Filament::getTenant()->id)->with('academicSession')->get()->mapWithKeys(fn($t)=>[$t->id=>$t->academicSession->name.' - '.$t->name]))->required()->searchable(),
   Select::make('school_class_id')->label('Class')->options(fn()=>SchoolClass::where('school_id',Filament::getTenant()->id)->where('is_active',true)->pluck('name','id'))->required()->searchable(),
   Select::make('result_template_id')->label('Template')->options(fn()=>ResultTemplate::where('school_id',Filament::getTenant()->id)->pluck('name','id'))->searchable(),
 ])->action(function(array $data):void{
    $school=Filament::getTenant(); $term=AcademicTerm::where('school_id',$school->id)->findOrFail($data['academic_term_id']); $class=SchoolClass::where('school_id',$school->id)->findOrFail($data['school_class_id']); $template=!empty($data['result_template_id'])?ResultTemplate::where('school_id',$school->id)->findOrFail($data['result_template_id']):ResultTemplate::where('school_id',$school->id)->where('is_default',true)->first();
    $publication=app(ResultCompilerService::class)->compile(auth()->user(),$school,$term,$class,$template); Notification::make()->success()->title("Compiled version {$publication->version}")->body('Review the result before releasing it to students.')->send();
  })];}
}
