<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\EnableRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicAppController extends Controller
{
    private const PASS_SCORE = 153;

    public function showAuth(): View
    {
        return view('welcome');
    }

    public function showLogin(): View
    {
        return view('auth-login');
    }

    public function showRegister(): View
    {
        return view('auth-register');
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
            'is_follower_unlocked' => false,
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

        $histories = $user->calculations()->latest()->limit(20)->get();

        return view('dashboard', [
            'user' => $user,
            'histories' => $histories,
            'passScore' => self::PASS_SCORE,
        ]);
    }

    public function saveCalculation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:80'],
            'first_term' => ['required', 'integer', 'min:0', 'max:100'],
            'second_term' => ['required', 'integer', 'min:0', 'max:100'],
            'third_term_needed' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['ok', 'warning', 'risk', 'passed'])],
            'summary' => ['required', 'string', 'max:180'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        Calculation::create([
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'first_term' => $validated['first_term'],
            'second_term' => $validated['second_term'],
            'third_term_needed' => $validated['third_term_needed'],
            'status' => $validated['status'],
            'summary' => $validated['summary'],
            'meta' => [
                'pass_score' => self::PASS_SCORE,
            ],
        ]);

        return back()->with('saved', 'Simulación guardada.');
    }

    public function requestEnable(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $enableRequest = EnableRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $link = URL::temporarySignedRoute(
            'admin.requests.show',
            now()->addDays(7),
            ['enableRequest' => $enableRequest->id]
        );

        $message = "Hola, solicito habilitación del simulador. Usuario: {$user->phone}. Revisar solicitud: {$link}";
        $waLink = 'https://wa.me/59171324941?text=' . urlencode($message);

        return redirect($waLink);
    }

    public function admin(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_admin) {
            return view('admin-denied');
        }

        $users = User::query()->latest()->get();
        $pendingRequests = EnableRequest::with('user')->where('status', 'pending')->latest()->get();

        return view('admin', [
            'users' => $users,
            'pendingRequests' => $pendingRequests,
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
}
