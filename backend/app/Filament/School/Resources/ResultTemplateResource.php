<?php
namespace App\Filament\School\Resources;
use App\Enums\TemplateLayout;
use App\Filament\School\Resources\ResultTemplateResource\Pages;
use App\Models\ResultTemplate;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class ResultTemplateResource extends TenantManagedResource
{
 protected static ?string $model=ResultTemplate::class; protected static string|\BackedEnum|null $navigationIcon='heroicon-o-document-text';
 public static function form(Schema $schema):Schema{return $schema->components([
  Section::make('Template')->schema([TextInput::make('name')->required()->unique(ignoreRecord:true,modifyRuleUsing:fn($rule)=>$rule->where('school_id',Filament::getTenant()->id)),Select::make('layout')->options(collect(TemplateLayout::cases())->mapWithKeys(fn($l)=>[$l->value=>ucfirst($l->value)]))->required()->default(TemplateLayout::Modern->value),Toggle::make('is_default')])->columns(3),
  Section::make('Branding')->statePath('settings')->schema([ColorPicker::make('primary_color')->default('#0f766e'),ColorPicker::make('accent_color')->default('#f59e0b'),TextInput::make('header_note'),Toggle::make('show_subject_position')->default(true),Toggle::make('show_grading_scale')->default(true),Toggle::make('show_verification_code')->default(true)])->columns(2)
 ]);}
 public static function table(Table $table):Table{return $table->columns([TextColumn::make('name')->searchable(),TextColumn::make('layout')->badge(),IconColumn::make('is_default')->boolean(),TextColumn::make('updated_at')->since()])->recordActions([EditAction::make()]);}
 public static function getPages():array{return ['index'=>Pages\ListResultTemplates::route('/'),'create'=>Pages\CreateResultTemplate::route('/create'),'edit'=>Pages\EditResultTemplate::route('/{record}/edit')];}
}
