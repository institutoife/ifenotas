<div class="ife-sim ecuador-sim" data-ecuador-simulator data-country-name="{{ $country['name'] }}" data-whatsapp-base="{{ \App\Support\IfeWhatsApp::messageUrl($country['whatsapp'], '') }}" data-send-label="Enviar mi resultado por WhatsApp">
    @include('partials.simulator-mode-picker')

    <form class="sim-workspace" id="{{ $countryCode }}SimWorkspace" data-ec-form data-ec-fields hidden novalidate>
        <div class="sim-toolbar"><span class="mode-current"></span></div>
        <div class="known-fields">
            <div class="grade-field">
                <label for="{{ $countryCode }}-first">1.º · Real · sobre 10</label>
                <input id="{{ $countryCode }}-first" type="text" inputmode="decimal" autocomplete="off" placeholder="0–10" data-ec-first aria-describedby="{{ $countryCode }}-first-error">
                <span class="grade-error" id="{{ $countryCode }}-first-error" data-ec-error="first"></span>
            </div>
            <div class="grade-field second-known" data-ec-second-real>
                <label for="{{ $countryCode }}-second">2.º · Real · sobre 10</label>
                <input id="{{ $countryCode }}-second" type="text" inputmode="decimal" autocomplete="off" placeholder="0–10" data-ec-second aria-describedby="{{ $countryCode }}-second-error">
                <span class="grade-error" id="{{ $countryCode }}-second-error" data-ec-error="second"></span>
            </div>
        </div>

        <div class="consult-row" data-ec-context>
            <div class="ec-context-field" data-ec-province-field>
                <label class="consult-field" for="{{ $countryCode }}-province"><span class="consult-label">Provincia</span>
                    <select id="{{ $countryCode }}-province" data-ec-province aria-describedby="{{ $countryCode }}-province-error">
                        <option value="">Elige una provincia</option>
                        @foreach($country['provinces'] as $province)
                            <option value="{{ $province }}">{{ $province }}</option>
                        @endforeach
                    </select>
                </label>
                <p class="grade-error ec-context-error" id="{{ $countryCode }}-province-error" data-ec-error="province"></p>
            </div>
            <div class="ec-context-field" data-ec-subject-field>
                <label class="consult-field" for="{{ $countryCode }}-subject"><span class="consult-label">Materia</span>
                    <select id="{{ $countryCode }}-subject" data-ec-subject aria-describedby="{{ $countryCode }}-subject-error">
                        <option value="">Elige una materia</option>
                        @foreach($country['subjects'] as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                        <option value="__other__">Otra</option>
                    </select>
                </label>
                <p class="grade-error ec-context-error" id="{{ $countryCode }}-subject-error" data-ec-error="subject"></p>
                <label class="consult-field ec-other-field" data-ec-other-field hidden for="{{ $countryCode }}-other-subject">
                    <span>Nombre de la materia</span>
                    <input id="{{ $countryCode }}-other-subject" type="text" maxlength="80" autocomplete="off" data-ec-other-subject aria-describedby="{{ $countryCode }}-other-error">
                    <span class="grade-error" id="{{ $countryCode }}-other-error" data-ec-error="other"></span>
                </label>
            </div>
            <button type="submit" class="consult-button" data-ec-calculate>Calcular</button>
        </div>
        <div class="support-subject-slot" data-ec-subject-slot></div>

        <div class="result-stage" data-ec-stage hidden>
            <div class="main-result" data-ec-result hidden role="status" aria-live="polite" aria-atomic="true">
                <span class="main-kicker" data-ec-result-label>NECESITAS</span>
                <strong class="main-number" data-ec-result-value>—</strong>
                <span class="main-status"><i class="state-dot" aria-hidden="true"></i><b data-ec-status></b></span>
                <p class="result-message" data-ec-status-message></p>
                <p class="scenario-message" data-ec-result-subject></p>
                <p class="scenario-message" data-ec-result-explanation></p>
                <p class="scenario-message" data-ec-maximum hidden>Promedio máximo: <strong data-ec-maximum-average></strong> sobre 10, aun con 10,00 en el 3.º trimestre.</p>
            </div>
            <div class="notes-row" data-ec-simulation>
                <div class="note-result"><span>1.º</span><strong data-ec-note-first>—</strong><small>REAL · SOBRE 10</small></div>
                <div class="note-result">
                    <label for="{{ $countryCode }}-simulated">2.º</label>
                    <input id="{{ $countryCode }}-simulated" class="simulated-note" type="text" inputmode="decimal" autocomplete="off" value="7,00" data-ec-simulated aria-label="Nota simulada del segundo trimestre, sobre 10" aria-describedby="{{ $countryCode }}-sim-error">
                    <small>SIMULADO · SOBRE 10</small>
                    <span class="grade-error" id="{{ $countryCode }}-sim-error" data-ec-error="simulated"></span>
                </div>
                <div class="note-result active"><span>3.º</span><strong data-ec-note-third>—</strong><small>NECESITAS · SOBRE 10</small></div>
            </div>
            @include('partials.simulator-slider', ['sliderMaximum' => 10, 'sliderStep' => '0.01', 'sliderDefault' => 7])
            <x-academic-support-cta />
        </div>
    </form>
</div>
