<section class="ife-countries" aria-labelledby="countryTitle">
    <h2 id="countryTitle">Elige tu país</h2>
    <nav class="country-options" aria-label="País del simulador">
        @foreach($countries as $countryCode => $country)
            @if(!empty($country['coming_soon']))
                <div class="country-option country-option-coming-soon" data-country-choice="{{ $countryCode }}" data-coming-soon>
                    @if(!empty($country['flag_image']))
                        <img src="{{ asset($country['flag_image']) }}" alt="" width="48" height="48">
                    @else
                        <span class="country-flag" aria-hidden="true">{{ $country['flag'] }}</span>
                    @endif
                    <span class="country-option-copy">
                        <span class="country-name">{{ $country['name'] }}</span>
                        <span class="country-status">En desarrollo</span>
                    </span>
                </div>
            @else
                <a class="country-option" href="{{ route($country['route']) }}" data-country-choice="{{ $countryCode }}">
                    @if(!empty($country['flag_image']))
                        <img src="{{ asset($country['flag_image']) }}" alt="" width="48" height="48">
                    @else
                        <span class="country-flag" aria-hidden="true">{{ $country['flag'] }}</span>
                    @endif
                    <span>{{ $country['name'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</section>
