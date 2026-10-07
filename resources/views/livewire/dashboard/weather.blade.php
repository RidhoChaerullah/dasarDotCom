<section class="panel weather-card" wire:poll.600s.visible aria-label="Cuaca sekitar lab">
    <div><p class="eyebrow">DI SEKITAR LAB</p><h2 class="mt-2 text-xl font-semibold">{{ $weather['data']['location'] ?? config('weather.label') }}</h2>
        <p class="mt-1 text-sm text-muted">Sedikit kabar dari luar ruang praktikum.</p></div>
    @if($weather['data'])
        <div class="flex items-center gap-4"><span class="weather-symbol" aria-hidden="true">{{ $weather['data']['symbol'] }}</span>
            <div><p class="text-4xl font-bold">{{ $weather['data']['temperature'] }}°C</p><p class="mt-1 text-muted capitalize">{{ $weather['data']['description'] }}</p></div></div>
        <div class="text-sm text-muted">
            <p>Kelembapan {{ $weather['data']['humidity'] ?? '—' }}% · Angin {{ $weather['data']['wind'] ?? '—' }} m/s</p>
            <p class="mt-1">Data {{ \Carbon\CarbonImmutable::createFromTimestamp($weather['data']['observed_at'])->timezone('Asia/Jakarta')->format('d/m H:i') }} WIB</p>
            @if($weather['state'] === 'stale')<p class="mt-1 text-warning">Menampilkan data terakhir; pembaruan belum tersedia.</p>@endif
            <a href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs underline">Cuaca dari OpenWeather</a>
        </div>
    @else
        <p class="text-sm text-muted">Cuaca belum tersedia. Silakan coba lagi nanti.</p>
    @endif
</section>
