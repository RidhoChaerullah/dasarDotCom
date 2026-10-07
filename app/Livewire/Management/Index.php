<?php

namespace App\Livewire\Management;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        Gate::authorize('manage-lab');

        return view('livewire.management.index')
            ->layout('components.layouts.app', [
                'title' => 'Pengelolaan Laboratorium',
            ]);
    }
}
