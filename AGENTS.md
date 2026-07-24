# Code Review Rules — RRHH El Solar Uruguay

Laravel 12 + Filament 3 + Pest. Dockerized (PHP 8.3, MySQL 8). UI en español, código en inglés.

## PHP / Laravel
- PHP 8.3, `declare(strict_types=1)` en clases de dominio.
- Tipar argumentos y retornos siempre. Enums backed para valores cerrados (ej. `TipoFichaje`).
- Lógica de negocio en `app/Domain/**`, no en controladores ni en componentes Livewire.
- Eloquent: usar relaciones y scopes; nada de queries crudas salvo necesidad justificada.
- Nunca exponer ni loguear hashes de password ni secretos.

## Filament
- Autorización en backend (policies / `canAccessPanel`), nunca solo ocultando menús.
- Labels y navegación en español; identificadores y nombres de clase en inglés.

## Testing (TDD estricto)
- Pest. Tests primero para lógica de negocio y fronteras de seguridad.
- `RefreshDatabase` en tests de feature. Controlar el tiempo con `Carbon::setTestNow`.
- No mergear con tests en rojo.

## Convenciones
- Commits convencionales (`feat:`, `fix:`, `chore:`…). Sin atribución de IA ni `Co-Authored-By`.
- Textos de UI en español neutro/profesional. Sin regionalismos en artefactos.
- Nada de credenciales/datos reales en el repo; `referencia/` y `.env` van gitignored.
