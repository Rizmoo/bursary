<?php

namespace App\Filament\App\Resources\Wards\Pages;

use App\Filament\App\Resources\Wards\WardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWard extends EditRecord
{
    protected static string $resource = WardResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $wardUser = $this->record->users()->orderBy('id')->first();

        $data['ward_user_name'] = $wardUser?->name;
        $data['ward_user_email'] = $wardUser?->email;
        $data['ward_user_password'] = null;

        return $data;
    }

    protected function afterSave(): void
    {
        $wardUser = $this->record->users()->orderBy('id')->first();

        if (! $wardUser) {
            return;
        }

        $wardUser->name = (string) $this->data['ward_user_name'];
        $wardUser->email = (string) $this->data['ward_user_email'];
        $wardUser->county_id = $this->record->county_id;

        if (filled($this->data['ward_user_password'] ?? null)) {
            $wardUser->password = (string) $this->data['ward_user_password'];
        }

        $wardUser->save();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
