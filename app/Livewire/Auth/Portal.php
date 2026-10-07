<?php
namespace App\Livewire\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
class Portal extends Component
{
    #[Locked] public string $initialMode = 'login';
    public function mount(): void
    {
        $this->initialMode = request()->routeIs('register') ? 'register' : 'login';
    }
    public function render()
    {
        return view('livewire.auth.portal')->layout('components.layouts.app', [
            'title' => $this->initialMode === 'register' ? 'Daftar' : 'Masuk',
        ]);
    }
}
