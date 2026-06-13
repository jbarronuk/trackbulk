<?php

namespace App\Http\Controllers;

use App\Services\Exports\TrackingDateRangeExport;
use App\Services\Exports\TrackingSelectionExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Exporter;

class ExportsController extends Controller
{
    public function __construct(
        private readonly Exporter $exporter,
    ) {}

    public function tracking(Request $request)
    {
        $current = Carbon::now();
        $filename = 'tracking-'.$current->format('y-m-d').'.xlsx';

        if ($request->type === 'daterange') {
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);

            return $this->exporter->download(new TrackingDateRangeExport(
                $request->user(),
                $start,
                $end,
            ), $filename);
        }

        if ($request->type === 'selection') {
            return $this->exporter->download(new TrackingSelectionExport(
                $request->user(),
                $request->selection,
            ), $filename);
        }
    }
}