<?php
namespace App\Livewire\Dashboard;
use App\Support\WeatherService;
use Livewire\Component;
class Weather extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <section class="panel p-6" aria-live="polite"><p class="text-sm text-muted">Memuat cuaca sekitar lab…</p></section>
        HTML;
    }
    public function render()
    {
        abort_unless(auth()->user()?->is_active, 403);
        return view('livewire.dashboard.weather', ['weather' => app(WeatherService::class)->current()]);
    }
}
