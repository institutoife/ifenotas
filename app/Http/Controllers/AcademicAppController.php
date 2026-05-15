<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\EnableRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
        $user->load('school');

        $histories = $user->calculations()->with('school')->latest()->limit(20)->get();

        return view('dashboard', [
            'user' => $user,
            'histories' => $histories,
            'passScore' => self::PASS_SCORE,
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

    public function saveSimulation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:80'],
            'first_term' => ['required', 'integer', 'min:0', 'max:100'],
            'second_term' => ['required', 'integer', 'min:0', 'max:100'],
            'third_term_needed' => ['required', 'integer', 'min:0', 'max:300'],
            'status' => ['required', Rule::in(['ok', 'warning', 'risk', 'passed'])],
            'summary' => ['required', 'string', 'max:500'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $calculation = Calculation::create([
            'user_id' => $user->id,
            'kind' => 'simulation',
            'subject' => $validated['subject'],
            'first_term' => $validated['first_term'],
            'second_term' => $validated['second_term'],
            'third_term_needed' => $validated['third_term_needed'],
            'status' => $validated['status'],
            'summary' => $validated['summary'],
            'meta' => [
                'pass_score' => self::PASS_SCORE,
                'autosaved' => true,
            ],
        ]);

        return response()->json([
            'saved' => true,
            'id' => $calculation->id,
        ]);
    }

    public function aiChat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:800'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required_with:history', 'string', 'max:1200'],
            'context' => ['nullable', 'array'],
            'context.subject' => ['nullable', 'string', 'max:80'],
            'context.first_term' => ['nullable', 'integer', 'min:0', 'max:100'],
            'context.second_term' => ['nullable', 'integer', 'min:0', 'max:100'],
            'context.total_needed' => ['nullable', 'integer', 'min:0', 'max:300'],
            'context.average_needed' => ['nullable', 'integer', 'min:0', 'max:150'],
            'context.third_term_needed' => ['nullable', 'integer', 'min:0', 'max:300'],
            'context.pass_score' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'El chat con IA aún no tiene configurada la clave OPENAI_API_KEY. Agrega tu API key en el archivo .env para activarlo.',
            ], 503);
        }

        $context = $validated['context'] ?? [];
        $history = collect($validated['history'] ?? [])
            ->take(-10)
            ->map(fn (array $item): array => [
                'role' => $item['role'],
                'content' => $item['content'],
            ])
            ->values()
            ->all();

        $input = array_merge($history, [[
            'role' => 'user',
            'content' => $validated['message'],
        ]]);

        $instructions = $this->academicChatInstructions($context);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(25)
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model', 'gpt-5.2'),
                'instructions' => $instructions,
                'input' => $input,
                'max_output_tokens' => 260,
            ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'No pude conectar con la IA en este momento. Intenta nuevamente en unos segundos.',
            ], 502);
        }

        return response()->json([
            'message' => $this->sanitizeAiText($this->extractOpenAiText($response->json())),
        ]);
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

    /**
     * @param array<string, mixed> $context
     */
    private function academicChatInstructions(array $context): string
    {
        $subject = $context['subject'] ?? 'sin materia seleccionada';
        $firstTerm = $context['first_term'] ?? 'sin nota';
        $totalNeeded = $context['total_needed'] ?? 'sin cálculo';
        $averageNeeded = $context['average_needed'] ?? 'sin cálculo';
        $secondTerm = $context['second_term'] ?? null;
        $thirdTerm = $context['third_term_needed'] ?? null;
        $passScore = $context['pass_score'] ?? self::PASS_SCORE;
        $simulationContext = $secondTerm === null
            ? 'No hay simulación del segundo trimestre. NO hables de tercer trimestre necesario; habla de puntos restantes entre segundo y tercer trimestre.'
            : "Hay simulación del segundo trimestre: {$secondTerm}. En ese caso el tercer trimestre necesario es {$thirdTerm}.";

        return <<<PROMPT
Eres el asistente académico de ife notas, una app educativa para estudiantes de Bolivia.
Tu objetivo es ayudar a calcular notas, explicar escenarios para pasar de curso y convertir estudiantes interesados a clases de apoyo escolar.

Reglas de negocio:
- Usa internamente {$passScore} puntos sobre 300 como referencia, pero no menciones ese número ni lo nombres como umbral.
- En lugar de eso, di siempre "para pasar de curso".
- Las notas trimestrales van de 0 a 100.
- Si solo hay nota del primer trimestre, calcula puntos restantes como {$passScore} - primer_trimestre y explica que esos puntos se deben obtener entre el segundo y tercer trimestre.
- Si solo hay nota del primer trimestre, también puedes decir el promedio aproximado que necesita en cada uno de los dos trimestres restantes.
- NO digas que necesita sacar todos los puntos restantes en el tercer trimestre cuando todavía no existe nota o simulación del segundo trimestre.
- Solo si hay segundo trimestre simulado, calcula tercer trimestre necesario como {$passScore} - primer_trimestre - segundo_trimestre.
- Si el usuario da una nota inválida, pide una nota entera entre 0 y 100.
- Si el frontend ya eligió una materia, acéptala sin expresar duda.
- No muestres listas de materias similares.
- Si falta una materia válida, pide una materia válida y vuelve al cálculo.
- Si el usuario pregunta cualquier cosa fuera de notas, materias, puntos necesarios, simulación académica o apoyo escolar, responde exactamente: "Estoy especializado en decirte cuántos puntos necesitas para pasar de curso. Tengo mis homólogos que son de uso general, consulta al WhatsApp 71039910."
- No inventes colegios, precios, horarios ni promesas de pasar de curso.
- Sé breve, cálido, juvenil, profesional y claro.
- Usa español natural.
- No digas que eres una IA entrenada.
- Puedes usar negritas con **texto** para materia, notas, puntos restantes y recomendaciones clave.
- No uses markdown pesado; respuestas de 2 a 5 líneas.
- No incluyas aclaraciones como "no se trata solo del tercer trimestre".
- Explica directamente los puntos restantes y el promedio por trimestre restante.
- Si invitas a WhatsApp, incluye el enlace https://wa.me/59171324941 como máximo una vez. No dupliques enlaces ni repitas el enlace al final.
- Mensajes de conversión permitidos: "Podemos ayudarte a prepararte mejor", "Te recomendamos apoyo escolar", "Podemos ayudarte a subir tus notas".

Contexto actual de la app:
- Materia seleccionada: {$subject}
- Primer trimestre: {$firstTerm}
- Puntos restantes entre segundo y tercer trimestre: {$totalNeeded}
- Promedio aproximado necesario por trimestre restante: {$averageNeeded}
- {$simulationContext}
PROMPT;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractOpenAiText(array $payload): string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text']) && trim($payload['output_text']) !== '') {
            return trim($payload['output_text']);
        }

        foreach ($payload['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return trim((string) $content['text']);
                }
            }
        }

        return 'Ya tengo el cálculo listo. ¿Qué otra materia quieres saber cuántos puntos te hacen falta para pasar de curso?';
    }

    private function sanitizeAiText(string $message): string
    {
        $message = preg_replace('/para alcanzar la nota m[ií]nima(?:\s+de\s+\d+)?/iu', 'para pasar de curso', $message) ?? $message;
        $message = preg_replace('/nota m[ií]nima(?:\s+anual)?(?:\s+de\s+\d+)?/iu', 'puntos necesarios para pasar de curso', $message) ?? $message;
        $message = preg_replace('/\[([^\]]+)\]\(https:\/\/wa\.me\/59171324941[^\s)]*\)?/u', '$1: https://wa.me/59171324941', $message) ?? $message;

        $seenLinks = [];
        $message = preg_replace_callback('/https:\/\/wa\.me\/59171324941[^\s]*/', function (array $match) use (&$seenLinks): string {
            if (isset($seenLinks['support'])) {
                return '';
            }

            $seenLinks['support'] = true;

            return 'https://wa.me/59171324941';
        }, $message) ?? $message;

        $message = preg_replace("/[ \t]{2,}/", ' ', $message) ?? $message;
        $message = preg_replace("/\n{3,}/", "\n\n", $message) ?? $message;

        return trim($message);
    }
}

