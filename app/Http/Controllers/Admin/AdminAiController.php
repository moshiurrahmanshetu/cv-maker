<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;

class AdminAiController extends Controller
{
    /**
     * Display AI assistant telemetry, metrics, and usage logs.
     */
    public function index(Request $request)
    {
        $totalRequests = AiUsageLog::count();
        $successfulRequests = AiUsageLog::where('status', 'success')->count();
        $failedRequests = AiUsageLog::where('status', 'failed')->count();
        $totalTokens = AiUsageLog::sum('total_tokens');

        $query = AiUsageLog::with(['user', 'cv'])->latest('created_at');

        if ($request->filled('feature')) {
            $query->where('feature', $request->feature);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        $activeProvider = config('ai.default_provider', 'mock');
        $aiEnabled = config('ai.enabled', true);
        $features = config('ai.features', []);
        $providers = config('ai.providers', []);

        return view('admin.ai.index', compact(
            'totalRequests',
            'successfulRequests',
            'failedRequests',
            'totalTokens',
            'logs',
            'activeProvider',
            'aiEnabled',
            'features',
            'providers'
        ));
    }
}
