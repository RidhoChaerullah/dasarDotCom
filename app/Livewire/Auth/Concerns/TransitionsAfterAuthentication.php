<?php

namespace App\Livewire\Auth\Concerns;

use Illuminate\Support\Js;

trait TransitionsAfterAuthentication
{
    // Call only after successful authentication and session regeneration.
    protected function transitionAfterAuthentication(string $url): void
    {
        $target = Js::from($url)->toHtml();

        // If the optional animation asset fails to load, navigate normally.
        $this->js("(() => { const url = {$target}; if (window.DasarAuthTransition) { window.DasarAuthTransition.go(url); } else { window.location.assign(url); } })()");
        $this->skipRender();
    }
}
