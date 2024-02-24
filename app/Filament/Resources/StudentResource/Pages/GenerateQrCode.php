<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class GenerateQrCode extends Page
{
    use InteractsWithRecord;
    
    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.resources.student-resource.pages.generate-qr-code';

    //la copie del video porque no se me creo automaticamente con el comando ( esta en la docu de filament)
    public function mount(int | string $record): void {

        $this->record = $this->resolveRecord($record);

        static::authorizeResourceAccess();

    }
}
