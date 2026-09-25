<?php

namespace App\Http\Controllers;

use App\Models\SimulatorRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SimulatorRecordController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'submission_id' => ['required', 'uuid'],
            'subject' => ['required', 'string', Rule::in(config('ife.subjects'))],
            'first' => ['required', 'integer', 'between:0,100'],
            'second' => ['required', 'integer', 'between:0,100'],
        ]);
        $required = max(0, AcademicAppController::PASS_SCORE - $data['first'] - $data['second']);
        $visitor = $request->session()->get('simulator_visitor_key');
        if (! $visitor) {
            $visitor = (string) Str::uuid();
            $request->session()->put('simulator_visitor_key', $visitor);
        }
        $record = SimulatorRecord::firstOrCreate([
            'visitor_key' => hash_hmac('sha256', $visitor, config('app.key')),
            'submission_id' => $data['submission_id'],
        ], [
            'user_id' => $request->user()?->id,
            'first' => $data['first'],
            'second' => $data['second'],
            'subject' => $data['subject'],
            'required_third' => $required,
            'pass_score' => AcademicAppController::PASS_SCORE,
            'status' => $required > 100 ? 'failed' : ($required === 0 ? 'passed' : 'pending'),
        ]);

        return response()->json(['saved' => true, 'status' => $record->status], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->is_admin, 403);
        $filters = $request->validate([
            'subject' => ['nullable', 'string', Rule::in(config('ife.subjects'))],
            'status' => ['nullable', Rule::in(['failed', 'passed', 'pending'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $query = SimulatorRecord::query()
            ->when($filters['subject'] ?? null, fn ($q, $subject) => $q->where('subject', $subject))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
        $counts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $records = $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin-simulator-records', compact('records', 'counts', 'filters'));
    }
}
