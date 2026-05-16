<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function royalmailCallBack(Request $request)
    {
        Log::info($request);
    }
}
