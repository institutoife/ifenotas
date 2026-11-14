@include('partials.grade-simulator-styles')

<div class="ife-sim" data-initial-mode="{{ $initialMode ?? '' }}" data-autofocus="{{ ($autoFocus ?? true) ? 'true' : 'false' }}" data-support-whatsapp="{{ $supportWhatsapp ?? config('ife.support_whatsapp') }}" data-school-year-end-date="{{ config('ife.school_year_end_date') }}" data-school-timezone="{{ config('ife.school_timezone') }}" data-server-time="{{ now()->toIso8601String() }}" data-records-url="{{ route('simulator-records.store') }}" data-csrf="{{ csrf_token() }}" data-pass-score="{{ $passScore }}" data-high-grade-threshold="{{ $highGradeThreshold }}">
    @unless($creatorInHeader ?? false)
    <div style="display:flex;justify-content:center">
        @include('partials.creator-live-link')
    </div>
    @endunless
    @include('partials.simulator-mode-picker')

    <section class="sim-workspace" id="ifeSimWorkspace" hidden>
        <div class="sim-toolbar"><span class="mode-current"></span></div>
        <div class="known-fields">
            <div class="grade-field"><label for="ifeFirstTerm">1.º · Real</label><input id="ifeFirstTerm" type="number" min="0" max="100" step="1" inputmode="numeric" placeholder="0–100"><span class="grade-error" data-error="first"></span></div>
            <div class="grade-field second-known"><label for="ifeSecondTerm">2.º · Real</label><input id="ifeSecondTerm" type="number" min="0" max="100" step="1" inputmode="numeric" placeholder="0–100"><span class="grade-error" data-error="second"></span></div>
        </div>

        <div class="consult-row" data-consult-controls hidden>
            <label class="consult-field"><span class="consult-label">Departamento</span><select data-consult-department required><option value="">Elige un departamento</option>@foreach(config('ife.departments') as $department)<option value="{{ $department }}">{{ $department }}</option>@endforeach</select></label>
            <label class="consult-field"><span class="consult-label">Materia</span><select data-consult-subject required><option value="">Elige una materia</option>@foreach(config('ife.subjects') as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach</select></label>
            <button type="button" class="consult-button" data-consult-button>Consultar</button>
        </div>
        <p style="font-size:.75rem;margin:4px 0" role="status" data-save-status></p>
        <div class="support-subject-slot" data-support-subject-slot></div>
        <div class="result-stage sim-pending" hidden>
            <div class="main-result" role="status" aria-live="polite" aria-atomic="true"><span class="main-kicker">NECESITAS</span><strong class="main-number">—</strong><span class="main-status"><i class="state-dot" aria-hidden="true"></i><b>INGRESA TUS NOTAS</b></span><p class="result-message" data-result-message></p><p class="scenario-message" data-scenario-message hidden></p></div>
            <div class="notes-row">
                <div class="note-result"><span>1.ºx</span><strong data-note="first">c—</strong><small>REAL</small></div>
                <div class="note-result"><span>2.ºx</span><strong data-note="second">—</strong><small data-role="second">SIMULADO</small></div>
                <div class="note-result active"><span>3.º</span><strong data-note="third">—</strong><small data-role="third">NECESITAS</small></div>
            </div>
            @include('partials.simulator-slider')
            <x-academic-support-cta />
        </div>
    </section>
</div>

<script src="{{ asset('js/ife-grade-engine.js').'?v=4' }}"></script>
<script src="{{ asset('js/ife-simulator-records.js').'?v=4' }}"></script>
<script src="{{ asset('js/ife-grade-simulator.js').'?v=5' }}"></script>
