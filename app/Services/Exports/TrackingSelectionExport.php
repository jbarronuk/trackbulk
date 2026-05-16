<?php

namespace App\Services\Exports;

use App\Models\Tracking;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrackingSelectionExport implements FromCollection, WithHeadings
{
    private $user;

    private $selection;

    public function __construct(User $user, array $selection)
    {
        $this->user = $user;
        $this->selection = $selection;
    }

    public function collection()
    {
        return Tracking::where('account_id', $this->user->account_id)
            ->whereIn('id', $this->selection)
            ->get(['number', 'summary_response']);
    }

    public function headings(): array
    {
        return [
            'Tracking Number',
            'Summary',
        ];
    }
}
