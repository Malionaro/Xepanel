<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $logs = ActivityLog::all();
        return view('logs.index', compact('logs'));
    }
}
