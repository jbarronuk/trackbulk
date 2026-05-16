<?php

namespace App\Services\Exports;

use App\Enums\TrackingStatus;
use App\Models\Tracking;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrackingDateRangeExport implements FromCollection, WithHeadings
{
    private $user;

    private $start;

    private $end;

    public function __construct(User $user, Carbon $start, Carbon $end)
    {
        $this->user = $user;
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        return Tracking::where('account_id', $this->user->account_id)
            ->where('status', '>', TrackingStatus::Querying->value)
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<=', $this->end)
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
