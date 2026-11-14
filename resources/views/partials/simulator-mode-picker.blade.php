<section class="mode-picker" @if($countryCode ?? false) data-ec-modes @endif>
    <h2 class="mode-question" id="{{ $countryCode ?? 'ife' }}ModeQuestion">¿Cuántas notas ya tienes?</h2>
    <div class="mode-options" role="group" aria-labelledby="{{ $countryCode ?? 'ife' }}ModeQuestion">
        <button class="mode-card" type="button" data-mode="one" value="one" @if($countryCode ?? false) data-ec-mode @endif aria-pressed="false" aria-controls="{{ $countryCode ?? 'ife' }}SimWorkspace"><strong>TENGO 1 NOTA</strong></button>
        <button class="mode-card" type="button" data-mode="two" value="two" @if($countryCode ?? false) data-ec-mode @endif aria-pressed="false" aria-controls="{{ $countryCode ?? 'ife' }}SimWorkspace"><strong>TENGO 2 NOTAS</strong></button>
    </div>
</section>
