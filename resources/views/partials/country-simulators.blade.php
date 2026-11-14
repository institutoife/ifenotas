<section class="ife-countries" aria-labelledby="countryTitle">
    <h2 id="countryTitle">Elige tu país</h2>
    <nav class="country-options" aria-label="País del simulador">
        @foreach($countries as $countryCode => $country)
            <a class="country-option" href="{{ route($country['route']) }}" data-country-choice="{{ $countryCode }}">
                @if(!empty($country['flag_image']))
                    <img src="{{ asset($country['flag_image']) }}" alt="" width="48" height="48">
                @else
                    <span class="country-flag" aria-hidden="true">{{ $country['flag'] }}</span>
                @endif
                <span>{{ $country['name'] }}</span>
            </a>
        @endforeach
    </nav>
</section>
