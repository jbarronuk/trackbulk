<?php

namespace App\Services\Exports;

use App\Enums\TrackingStatus;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrackingExport implements FromCollection, WithHeadings
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        return Tracking::where('account_id', $this->user->account_id)->where('status', '>', TrackingStatus::Querying->value)->get(['number', 'summary_response']);
    }
    public function headings(): array
    {
        return [
            'Tracking Number',
            'Summary',
        ];
    }
}