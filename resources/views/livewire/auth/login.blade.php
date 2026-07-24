<div class="login-wrap">
    <div class="login-card">
        <div class="login-brand">
            <span class="login-sun">@include('partials.sol')</span>
            <div class="name">El Solar Uruguay</div>
            <div class="tag">Abordaje Terapéutico Integral para una Mejor Calidad de Vida</div>
        </div>

        <div class="login-body">
            <form wire:submit="authenticate">
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" wire:model="email" autocomplete="username" autofocus>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" wire:model="password" autocomplete="current-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-login">Ingresar</button>
            </form>
        </div>
    </div>
</div>
