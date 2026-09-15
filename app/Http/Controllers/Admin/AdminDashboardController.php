<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin panel dashboard.
     */
    public function index()
    {
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();
        $regularUsers = User::where('role', 'user')->count();

        $totalCvs = Cv::count();
        $publishedCvs = Cv::where('status', 'published')->count();
        $draftCvs = Cv::where('status', 'draft')->count();

        $totalTemplates = CvTemplate::count();
        $totalCategories = TemplateCategory::count();

        $recentUsers = User::latest()->take(5)->get();
        $recentCvs = Cv::with(['user', 'personalInfo', 'template'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'adminUsers',
            'regularUsers',
            'totalCvs',
            'publishedCvs',
            'draftCvs',
            'totalTemplates',
            'totalCategories',
            'recentUsers',
            'recentCvs'
        ));
    }
}
