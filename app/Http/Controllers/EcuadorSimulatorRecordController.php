<?php

namespace App\Http\Controllers;

use App\Models\EcuadorSimulatorRecord;
use App\Support\EcuadorGrades;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcuadorSimulatorRecordController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'submission_id' => ['required', 'uuid'],
            'mode' => ['required', Rule::in(['two'])],
            'province' => ['required', 'string', Rule::in(config('ife.countries.ecuador.provinces'))],
            'subject' => ['required', 'string', Rule::in([...config('ife.countries.ecuador.subjects'), '__other__'])],
            'other_subject' => ['exclude_unless:subject,__other__', 'required', 'string', 'max:80'],
            'first_cents' => ['required', 'integer', 'between:0,1000'],
            'second_cents' => ['required', 'integer', 'between:0,1000'],
            'simulated' => ['prohibited'],
        ]);
        $subject = $data['subject'] === '__other__' ? $data['other_subject'] : $data['subject'];
        $visitor = $request->session()->get('ecuador_simulator_visitor_key');
        if (! $visitor) {
            $visitor = (string) Str::uuid();
            $request->session()->put('ecuador_simulator_visitor_key', $visitor);
        }
        $attributes = [
            'province' => $data['province'],
            'subject' => $subject,
            'is_custom_subject' => $data['subject'] === '__other__',
            'first_cents' => (int) $data['first_cents'],
            'second_cents' => (int) $data['second_cents'],
        ];
        $record = EcuadorSimulatorRecord::firstOrCreate([
            'visitor_key' => hash_hmac('sha256', $visitor, config('app.key')),
            'submission_id' => $data['submission_id'],
        ], [
            ...$attributes,
            ...EcuadorGrades::calculate($attributes['first_cents'], $attributes['second_cents']),
            'user_id' => $request->user()?->id,
        ]);
        // Retries are idempotent; a reused ID may never silently overwrite another calculation.
        foreach ($attributes as $key => $value) {
            abort_if($record->{$key} !== $value, 409, 'Este identificador ya corresponde a otra consulta.');
        }

        return response()->json(['saved' => true, 'status' => $record->status], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->is_admin, 403);

        $filters = $request->validate([
            'province' => ['nullable', 'string', Rule::in(config('ife.countries.ecuador.provinces'))],
            'subject' => ['nullable', 'string', Rule::in([...config('ife.countries.ecuador.subjects'), '__other__'])],
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', Rule::in(['possible', 'impossible'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $timezone = config('ife.school_timezone', 'America/La_Paz');
        $query = EcuadorSimulatorRecord::query()
            ->when($filters['province'] ?? null, fn ($q, $province) => $q->where('province', $province))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('subject', 'like', '%'.$search.'%'));
        if ($subject = $filters['subject'] ?? null) {
            $subject === '__other__'
                ? $query->where('is_custom_subject', true)
                : $query->where('is_custom_subject', false)->where('subject', $subject);
        }
        if ($from = $filters['from'] ?? null) {
            $query->where('created_at', '>=', Carbon::createFromFormat('!Y-m-d', $from, $timezone)->utc());
        }
        if ($to = $filters['to'] ?? null) {
            $query->where('created_at', '<=', Carbon::createFromFormat('!Y-m-d', $to, $timezone)->endOfDay()->utc());
        }
        $counts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $counts->sum();
        $records = $query->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin-ecuador-records', compact('records', 'counts', 'total', 'filters', 'timezone'));
    }
}
