<?php
namespace App\Support;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;
class WeatherService
{
    public function current(): array
    {
        $unavailable = ['state' => 'unavailable', 'data' => null];
        $apiKey = (string) config('weather.key');
        if ($apiKey === '') { return $unavailable; }
        $lat = config('weather.latitude');
        $lon = config('weather.longitude');
        $hasCoords = is_numeric($lat) && is_numeric($lon)
            && abs((float) $lat) <= 90 && abs((float) $lon) <= 180;
        $location = $hasCoords ? ['lat' => $lat, 'lon' => $lon]
            : ['q' => config('weather.city', 'Jakarta,ID')];
        $key = 'weather:'.hash('sha256', json_encode([$location, config('weather.label'), $apiKey]));
        try {
            $cached = Cache::get($key);
            if (is_array($cached)) { return ['state' => 'fresh', 'data' => $cached]; }
            // Cache and lock are shared by all client PCs.
            $lock = Cache::lock($key.':lock', 15);
            if (! $lock->get()) { return $this->previous($key); }
            try {
                if ($cached = Cache::get($key)) { return ['state' => 'fresh', 'data' => $cached]; }
                if (Cache::has($key.':retry')) { return $this->previous($key); }
                $response = Http::connectTimeout(2)->timeout(4)->acceptJson()
                    ->get('https://api.openweathermap.org/data/2.5/weather', $location + [
                        'appid' => $apiKey, 'units' => 'metric', 'lang' => 'id',
                    ]);
                $body = $response->json();
                if (! $response->successful() || ! is_numeric(data_get($body, 'main.temp'))
                    || ! is_numeric(data_get($body, 'dt'))) {
                    Cache::put($key.':retry', true, 600);
                    return $this->previous($key);
                }
                $icon = (string) data_get($body, 'weather.0.icon', '');
                $data = [
                    'location' => $hasCoords ? config('weather.label', 'Jakarta Barat') : 'Jakarta',
                    'temperature' => (int) round((float) data_get($body, 'main.temp')),
                    'description' => (string) data_get($body, 'weather.0.description', 'Cuaca saat ini'),
                    'humidity' => data_get($body, 'main.humidity'),
                    'wind' => data_get($body, 'wind.speed'),
                    'symbol' => $this->symbol($icon),
                    'observed_at' => (int) $body['dt'],
                ];
                Cache::put($key, $data, 600);
                Cache::put($key.':previous', $data, 86400);
                return ['state' => 'fresh', 'data' => $data];
            } catch (Throwable $e) {
                // Do not log HTTP exception URLs; they may contain the API key.
                Cache::put($key.':retry', true, 600);
                return $this->previous($key);
            } finally { $lock->release(); }
        } catch (Throwable $e) { return $unavailable; }
    }
    private function previous(string $key): array
    {
        $data = Cache::get($key.':previous');
        return ['state' => $data ? 'stale' : 'unavailable', 'data' => $data];
    }
    private function symbol(string $icon): string
    {
        return match (substr($icon, 0, 2)) {
            '01' => str_ends_with($icon, 'n') ? '☾' : '☀',
            '02', '03', '04' => '☁', '09', '10' => '☂',
            '11' => 'ϟ', '13' => '❄', default => '◌',
        };
    }
}
