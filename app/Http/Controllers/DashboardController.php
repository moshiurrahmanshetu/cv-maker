<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application user dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Real calculated metrics for the authenticated user
        $totalCvs = $user->cvs()->count();
        $publishedCvs = $user->cvs()->where('status', 'published')->count();
        $draftCvs = $user->cvs()->where('status', 'draft')->count();
        
        $recentCvs = $user->cvs()
            ->with(['documentType', 'personalInfo', 'letterDetail', 'template'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'totalCvs',
            'publishedCvs',
            'draftCvs',
            'recentCvs'
        ));
    }
}
