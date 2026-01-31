<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        if ($user && ($user->hasRole('kasir') || $user->tipe === 'kasir')) {
            return redirect()->route('pos');
        }

        return redirect()->intended(
            filament()->getUrl()
        );
    }
}
