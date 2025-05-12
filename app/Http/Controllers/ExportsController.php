<?php
namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Services\Exports\TrackingDateRangeExport;
use App\Services\Exports\TrackingExport;
use App\Services\Exports\TrackingSelectionExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Exporter;

class ExportsController extends Controller
{
    private $exporter;

    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }
    
    public function tracking(Request $request)
    {
        //TODO finish off the batch stuff and test the date range stuff
        $current = Carbon::now();

        if ($request->type === 'daterange') {
            
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            
            return $this->exporter->download(new TrackingDateRangeExport(
                $request->user(),
                $start,
                $end
            ), 'tracking-' . $current->format('y-m-d') . '.xlsx');

        } else if ($request->type === 'selection') {
            $selection = $request->selection;
            return $this->exporter->download(new TrackingSelectionExport(
                $request->user(),
                $selection,
            ), 'tracking-' . $current->format('y-m-d') . '.xlsx');
        }
    }
}