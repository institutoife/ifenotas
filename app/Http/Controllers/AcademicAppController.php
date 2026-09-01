<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\EnableRequest;
use App\Models\LivePrizeWinner;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicAppController extends Controller
{
    private const PASS_SCORE = 153;
    private const HIGH_THIRD_TERM_SCORE = 70;

    public function showAuth(): View
    {
        return view('welcome', [
            'ife' => config('ife'),
        ]);
    }

    public function showLogin(): View
    {
        return view('auth-login');
    }

    public function showRegister(): View
    {
        return view('auth-register');
    }

    public function liveNotes(): View
    {
        return view('live-notas', [
            'passScore' => self::PASS_SCORE,
        ]);
    }

    public function notesSimulator(): View
    {
        return view('third-term-simulator', [
            'passScore' => self::PASS_SCORE,
            'highGradeThreshold' => self::HIGH_THIRD_TERM_SCORE,
        ]);
    }

    public function liveWinners(): JsonResponse
    {
        $winners = LivePrizeWinner::query()
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (LivePrizeWinner $winner): array => $this->formatLiveWinner($winner));

        return response()->json(['winners' => $winners]);
    }

    public function storeLiveWinner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'winner_name' => ['required', 'string', 'max:120'],
            'prize' => ['required', 'string', 'max:160'],
            'drawn_number' => ['nullable', 'integer', 'min:1', 'max:29'],
        ]);

        $winner = LivePrizeWinner::create($validated);

        return response()->json([
            'saved' => true,
            'winner' => $this->formatLiveWinner($winner),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'regex:/^\d{8}$/'],
            'password' => ['required', 'string', 'min:6', 'max:40', 'confirmed'],
        ], [
            'phone.regex' => 'Ingresa un numero valido de 8 digitos.',
        ]);

        $phone = '+591' . $validated['phone'];

        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors([
                'register' => 'Este número ya está registrado.',
            ])->onlyInput('phone');
        }

        $user = User::create([
            'name' => 'Estudiante ' . substr($phone, -4),
            'email' => str_replace('+', '', $phone) . '@notes.local',
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'is_follower_unlocked' => true,
            'is_admin' => User::count() === 0,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'phone' => ['required', 'regex:/^\d{8}$/'],
            'password' => ['required', 'string'],
        ]);

        $authCredentials = [
            'phone' => '+591' . $credentials['phone'],
            'password' => $credentials['password'],
        ];

        if (! Auth::attempt($authCredentials, true)) {
            return back()->withErrors([
                'auth' => 'Número o contraseña incorrectos.',
            ])->onlyInput('phone');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth');
    }

    public function dashboard(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('school');

        $histories = $user->calculations()->with('school')->latest()->limit(20)->get();

        return view('dashboard', [
            'user' => $user,
            'histories' => $histories,
            'passScore' => self::PASS_SCORE,
            'highGradeThreshold' => self::HIGH_THIRD_TERM_SCORE,
        ]);
    }

    public function searchSchools(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $schools = School::query()
            ->select(['id', 'nombre', 'codigo_rue', 'departamento', 'provincia', 'municipio', 'area', 'dependencia'])
            ->when($term !== '', function ($query) use ($term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_rue', 'like', "%{$term}%");
                });
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get()
            ->map(fn (School $school): array => $this->formatSchoolForSelect($school));

        return response()->json(['results' => $schools]);
    }

    public function saveCalculation(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:80'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'first_term' => ['required', 'integer', 'min:0', 'max:100'],
            'second_term' => ['nullable', 'integer', 'min:0', 'max:100'],
            'third_term_needed' => ['required', 'integer', 'min:0', 'max:300'],
            'status' => ['required', Rule::in(['ok', 'warning', 'risk', 'passed'])],
            'summary' => ['required', 'string', 'max:500'],
            'kind' => ['nullable', Rule::in(['calculation', 'simulation'])],
            'meta' => ['nullable', 'array'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $calculation = Calculation::create([
            'user_id' => $user->id,
            'school_id' => $validated['school_id'] ?? $user->school_id,
            'kind' => $validated['kind'] ?? 'calculation',
            'subject' => $validated['subject'],
            'first_term' => $validated['first_term'],
            'second_term' => $validated['second_term'] ?? 0,
            'third_term_needed' => $validated['third_term_needed'],
            'status' => $validated['status'],
            'summary' => $validated['summary'],
            'meta' => array_merge([
                'pass_score' => self::PASS_SCORE,
            ], $validated['meta'] ?? []),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'saved' => true,
                'id' => $calculation->id,
            ]);
        }

        return back()->with('saved', 'Simulación guardada.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'grade_level' => ['nullable', 'string', 'max:80'],
            'guardian_name' => ['nullable', 'string', 'max:120'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $validated['name'];
        $user->email = $validated['email'] ?: $user->email;
        $user->grade_level = $validated['grade_level'] ?? null;
        $user->guardian_name = $validated['guardian_name'] ?? null;
        $user->guardian_phone = $validated['guardian_phone'] ?? null;
        $user->school_id = $validated['school_id'] ?? null;
        $user->save();

        return response()->json([
            'saved' => true,
            'school_id' => $user->school_id,
        ]);
    }

    public function requestEnable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        EnableRequest::firstOrCreate([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Solicitud recibida. Revisaremos tu perfil para habilitar el simulador.');
    }

    public function admin(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_admin) {
            return view('admin-denied');
        }

        $calculationFilters = [
            'subject' => (string) $request->query('subject', ''),
            'quality' => (string) $request->query('quality', ''),
            'kind' => (string) $request->query('kind', ''),
            'status' => (string) $request->query('status', ''),
            'search' => trim((string) $request->query('search', '')),
        ];

        $users = User::query()->latest()->get();
        $pendingRequests = EnableRequest::with('user')->where('status', 'pending')->latest()->get();
        $subjects = Calculation::query()
            ->select('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');

        $calculations = Calculation::with(['user', 'school'])
            ->when($calculationFilters['subject'] !== '', function ($query) use ($calculationFilters): void {
                $query->where('subject', $calculationFilters['subject']);
            })
            ->when($calculationFilters['kind'] !== '', function ($query) use ($calculationFilters): void {
                $query->where('kind', $calculationFilters['kind']);
            })
            ->when($calculationFilters['status'] !== '', function ($query) use ($calculationFilters): void {
                $query->where('status', $calculationFilters['status']);
            })
            ->when($calculationFilters['quality'] === 'bad', function ($query): void {
                $query->where('first_term', '<', 51);
            })
            ->when($calculationFilters['quality'] === 'regular', function ($query): void {
                $query->whereBetween('first_term', [51, 69]);
            })
            ->when($calculationFilters['quality'] === 'good', function ($query): void {
                $query->where('first_term', '>=', 70);
            })
            ->when($calculationFilters['search'] !== '', function ($query) use ($calculationFilters): void {
                $search = $calculationFilters['search'];

                $query->where(function ($query) use ($search): void {
                    $query->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('phone', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('school', function ($query) use ($search): void {
                            $query->where('nombre', 'like', "%{$search}%")
                                ->orWhere('codigo_rue', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->limit(200)
            ->get();

        return view('admin', [
            'users' => $users,
            'pendingRequests' => $pendingRequests,
            'subjects' => $subjects,
            'calculations' => $calculations,
            'calculationFilters' => $calculationFilters,
        ]);
    }

    public function showEnableRequest(EnableRequest $enableRequest): View
    {
        /** @var User $actor */
        $actor = Auth::user();

        if (! $actor->is_admin) {
            return view('admin-denied');
        }

        $enableRequest->load('user');

        return view('admin-request', ['enableRequest' => $enableRequest]);
    }

    public function approveEnableRequest(EnableRequest $enableRequest): RedirectResponse
    {
        /** @var User $actor */
        $actor = Auth::user();

        if (! $actor->is_admin) {
            return redirect()->route('admin')->with('status', 'No tienes permisos de administrador para acceder a esta sección.');
        }

        $targetUser = $enableRequest->user;
        if ($targetUser !== null) {
            $targetUser->is_follower_unlocked = true;
            $targetUser->save();
        }

        $enableRequest->status = 'approved';
        $enableRequest->save();

        return redirect()->route('admin')->with('status', 'Usuario habilitado correctamente.');
    }

    public function toggleFollower(User $user): RedirectResponse
    {
        /** @var User $actor */
        $actor = Auth::user();

        if (! $actor->is_admin) {
            return redirect()->route('admin')->with('status', 'No tienes permisos de administrador para acceder a esta sección.');
        }

        $user->is_follower_unlocked = ! $user->is_follower_unlocked;
        $user->save();

        return back();
    }

    private function formatSchoolForSelect(School $school): array
    {
        $location = collect([$school->departamento, $school->provincia, $school->municipio])
            ->filter()
            ->implode(' · ');

        $details = collect([
            $school->area ? "Área: {$school->area}" : null,
            $school->dependencia ? "Dependencia: {$school->dependencia}" : null,
        ])->filter()->implode(' · ');

        return [
            'id' => $school->id,
            'text' => $school->nombre,
            'name' => $school->nombre,
            'rue' => $school->codigo_rue,
            'location' => $location,
            'details' => $details,
        ];
    }

    private function formatLiveWinner(LivePrizeWinner $winner): array
    {
        return [
            'id' => $winner->id,
            'name' => $winner->winner_name,
            'prize' => $winner->prize,
            'number' => $winner->drawn_number,
            'date' => $winner->created_at?->format('d/m H:i') ?? '',
        ];
    }

}

