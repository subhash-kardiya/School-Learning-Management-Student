<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class DashboardController extends Controller
{
    public function index()
    {
        $latestAnnouncements = Announcement::query()
            ->activeWindow()
            ->visibleTo((string) session('role'), auth()->user())
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.parent', compact('latestAnnouncements'));
    }
}
