<?php
namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Services\Exports\TrackingExport;
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
        return $this->exporter->download(new TrackingExport($request->user()), 'tracking.xlsx');
    }
}