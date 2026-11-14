<div class="slider-block">
                <div class="slider-title"><span data-slider-label>SIMULA EL 2.º TRIMESTRE</span><span class="slider-value-wrap"><strong data-slider-value>{{ $sliderDefault ?? 50 }}</strong><b class="slider-outcome"></b></span></div>
                <div class="sim-range-wrap"><div class="sim-zones"></div><div class="pass-marker"><span data-marker-label>MÍNIMO</span></div><input class="sim-range" type="range" min="0" max="{{ $sliderMaximum ?? 100 }}" step="{{ $sliderStep ?? 1 }}" value="{{ $sliderDefault ?? 50 }}" @if($countryCode ?? false) data-ec-range @endif aria-label="Nota simulada"><div class="range-legend"><span data-legend-left>REPRUEBA</span><span data-legend-right>APRUEBA</span></div></div>
            </div>
