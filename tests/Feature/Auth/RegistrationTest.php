<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** No hay registro público: los usuarios los crea un Administrador (o la intranet). */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_existe_la_pantalla_de_registro(): void
    {
        $this->assertFalse(Route::has('register'));
        $this->get('/register')->assertNotFound();
    }

    public function test_no_se_puede_registrar_por_post(): void
    {
        $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
        $this->assertGuest();
        $this->assertSame(0, User::where('email', 'intruso@example.com')->count());
    }
}
