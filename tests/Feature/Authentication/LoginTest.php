<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_log_in_succesfully()
    {
        $user = User::factory()->create([
            'password' => Hash::make('1234567'),
        ]);

        $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => '1234567']);

        $response->assertSuccessful();
        $response->assertJson(['status' => 200, 'message' => 'User logged in succesfully']);
    }

    /**
     * @dataProvider wrongCredentialsDataProvider
     */
    public function test_users_log_in_with_wrong_credentials($user)
    {
        $createdUser = User::factory()->create(['email' => 'felipe@lightit.io', 'password' => '1234567']);
        $response = $this->postJson('/api/login', $user);

        $response->assertJson(['status' => 401, 'message' => 'Invalid credentials']);
    }

    /**
     * @dataProvider invalidUserDataProvider
     */
    public function test_login_with_invalid_user_data($user)
    {
        $response = $this->postJson('/api/login', $user);

        $response->assertStatus(422);
    }

    public function test_cant_log_in_if_already_logged_in()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/login');
        $response->assertStatus(302);
    }

    public function wrongCredentialsDataProvider(): array
    {
        return [
            ['wrong Password' => [
                'email' => 'felipe@lightit.io',
                'password' => '12344643',
            ]],
            ['wrong Email' => [
                'email' => 'juan@lightit.io',
                'password' => '1234626526',
            ]],
        ];
    }

    public function invalidUserDataProvider(): array
    {
        return [
            ['wrong email' => [
                'email' => 'felipelightitio',
                'password' => '12344643',
            ]],
            ['wrong password' => [
                'email' => 'juan@lightit.io',
                'password' => '1234',
            ]],
        ];
    }
}
