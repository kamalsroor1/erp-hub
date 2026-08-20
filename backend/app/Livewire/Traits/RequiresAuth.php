<?php

namespace App\Livewire\Traits;

trait RequiresAuth
{
    public function bootRequiresAuth()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }
}
