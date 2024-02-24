<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Exports\StudentsExport;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\StudentResource\Pages;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = "Academic Management";

    public static function getNavigationBadge(): ?string{
        return static::$model::count();    //de linea 28, trae el modelo y me trae
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('class_id')->live()
                    ->relationship(name: 'class', titleAttribute: 'name'),

                Select::make('section_id') 
                    ->label('Section')
                    ->options(function(Get $get){
                        $classId = $get('class_id');
                        if($classId){
                            return Section::where('class_id', $classId)
                            ->get()->pluck('name','id')->toArray();
                        }
                    }),
                    
                TextInput::make('name')->autofocus()->required(),
                TextInput::make('email')
                ->required()
                ->unique(ignoreRecord:true),
                //en class si funciona el ignore, aqui no
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ///MUESTRA DATOS DE LA TABLA
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('class.name')->badge()->searchable()->sortable(),
                TextColumn::make('section.name')->badge()->searchable()->sortable(),
            ])

            ->filters([
                //APLICAR FILTROS
                Filter::make('class-section-filter')
                ->form([
                    Select::make('class_id')
                    ->label('Filter By Class')
                    ->placeholder('Select a Class')
                    ->options(
                        Classes::pluck('name','id')->toArray(),
                    ),
                    Select::make('section_id')
                    ->label('Filter By Section')
                    ->placeholder('Select a Section')
                    ->options(
                        // obtenemos el id de class
                        function(Get $get){
                            $classId = $get ('class_id');

                            //comprobamos si existe,si es asi devolvemos la section donde esta el id de la clase
                            if($classId){
                                return Section::where('class_id', $classId)
                                ->pluck('name','id')->toArray();
                            }})
                ])

                //LA QUERY NOS DEVUELVE LA CONSULTA DESADA AL APLICAR EL FILTRO Y SELECCIONAR
                ->query(function (Builder $query, array $data): Builder {
                    
                        //consulta cuando-when se selecciona el ID 
                    return $query->when($data['class_id'], function ($query) use ($data) {
                        return $query->where('class_id', $data ['class_id']);  
                            //cuando tengamos el id de la section,
                    })->when($data['section_id'], function ($query) use ($data) {
                        return $query->where('section_id', $data ['section_id']);
                    });

            }),
            ])
            
            ->actions([
                //ACCIONES A REGISTROS INDIVUALES
                Tables\Actions\EditAction::make(),
                
                //ACCIONES PERSONALIZADAS ! 
                Action::make('downloadPdf')->url(function(Student $student){
                    return route('student.invoice.generate',$student);
                }),

                Action::make('qrCode')->url(function(Student $record){

                    return static::getUrl('qrCode',['record' => $record]);
                    
                }),
            ])
            ->bulkActions([
                //ACCIONES MACIVAS, PODES SELECCIONAR VARIOS
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export')
                    ->label('Export Records')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function(Collection $records){
                        return (new StudentsExport($records))->download('students.xlsx');
                    }) 
                    
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),

            'qrCode' => Pages\GenerateQrCode::route('/{record}/qrcode'),
        ];
    }
}
