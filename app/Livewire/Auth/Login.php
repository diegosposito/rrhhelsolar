<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Standalone login screen for the kiosk circuit.
 *
 * Fichaje users cannot authenticate through Filament's /admin/login
 * (canAccessPanel denies them), so this page authenticates and then
 * redirects by role: admin -> /admin, fichaje -> /registro.
 */
#[Layout('components.layouts.kiosk')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public function authenticate()
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'activo' => true,
        ];

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son válidas o la cuenta está inactiva.',
            ]);
        }

        Session::regenerate();

        $role = Auth::user()->role?->nombre;

        return redirect()->to($role === 'admin' ? '/admin' : '/registro');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
