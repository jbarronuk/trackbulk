<?php

namespace App\Http\Controllers;

use App\Enums\TrackingStatus;
use App\Enums\TrackingType;
use App\Jobs\Query;
use App\Jobs\QueryRoyalMail;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OAuthController extends Controller
{
    public function royalmailCallBack(Request $request)
    {
        Log::info($request);
    }
}
