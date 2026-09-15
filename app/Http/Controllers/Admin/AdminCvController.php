<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use Illuminate\Http\Request;

class AdminCvController extends Controller
{
    /**
     * Display a listing of all CVs across the system.
     */
    public function index(Request $request)
    {
        $query = Cv::with(['user', 'personalInfo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $cvs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.cvs.index', compact('cvs'));
    }

    /**
     * Preview any CV from the admin panel.
     */
    public function show(Cv $cv)
    {
        $cv->load([
            'user',
            'personalInfo',
            'experiences',
            'educations',
            'skills',
            'languages',
            'certifications',
            'projects',
            'awards',
            'references',
            'customSections',
        ]);

        return view('admin.cvs.show', compact('cv'));
    }
}
