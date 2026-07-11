<?php
namespace App\Filament\School\Resources;
use App\Filament\School\Resources\ResultSummaryResource\Pages;
use App\Models\AcademicTerm;
use App\Models\ResultSummary;
use Filament\Facades\Filament;
use App\Services\AiCommentService;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
class ResultSummaryResource extends TenantManagedResource
{
 protected static ?string $model=ResultSummary::class;protected static ?string $navigationLabel='Result Comments';protected static string|\BackedEnum|null $navigationIcon='heroicon-o-chat-bubble-left-right';
 public static function canCreate():bool{return false;}
 public static function form(Schema $schema):Schema{return $schema->components([Section::make('Reviewed comments')->description('AI text is a draft. A school officer remains responsible for approving it.')->schema([Textarea::make('teacher_comment')->rows(4)->maxLength(500),Textarea::make('principal_comment')->rows(4)->maxLength(500)])->columns(2)]);}
 public static function table(Table $table):Table{return $table->columns([TextColumn::make('student.full_name')->label('Student')->searchable(['first_name','last_name']),TextColumn::make('student.admission_number')->searchable(),TextColumn::make('term.academicSession.name')->label('Session'),TextColumn::make('term.name'),TextColumn::make('schoolClass.name')->label('Class'),TextColumn::make('average_score')->suffix('%')->sortable(),TextColumn::make('class_position')->label('Position')->sortable(),IconColumn::make('ai_comment_generated')->label('AI draft')->boolean(),TextColumn::make('released_at')->dateTime()])->filters([SelectFilter::make('academic_term_id')->options(fn():array=>AcademicTerm::query()->with('academicSession')->where('school_id',Filament::getTenant()->id)->get()->mapWithKeys(fn(AcademicTerm $term):array=>[$term->id=>$term->academicSession->name.' - '.$term->name])->all()),SelectFilter::make('school_class_id')->options(fn():array=>Filament::getTenant()->classes()->orderBy('name')->pluck('name','id')->all())])->recordActions([
  Action::make('generateAi')->label('Generate AI draft')->icon('heroicon-o-sparkles')->requiresConfirmation()->action(function(ResultSummary $record):void{$comments=app(AiCommentService::class)->generate($record);$before=$record->only(['teacher_comment','principal_comment','ai_comment_generated']);$record->update(['teacher_comment'=>$comments['teacher_comment'],'principal_comment'=>$comments['principal_comment'],'ai_comment_generated'=>true]);app(AuditService::class)->record('result.ai_comment_generated',$record,$before,$record->fresh()->only(['teacher_comment','principal_comment','ai_comment_generated']),$record->school_id);Notification::make()->success()->title('Draft comments generated')->body('Review and edit the comments before release.')->send();}),
  EditAction::make(),Action::make('preview')->url(fn(ResultSummary $record)=>route('results.publication.preview',$record->result_publication_id))->openUrlInNewTab(),
 ]);}
 public static function getPages():array{return ['index'=>Pages\ListResultSummaries::route('/'),'edit'=>Pages\EditResultSummary::route('/{record}/edit')];}
}
