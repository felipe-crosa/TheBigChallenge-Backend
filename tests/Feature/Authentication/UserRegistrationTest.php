<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider validUsersDataProvider
     */
    public function test_user_is_registered_on_database($user)
    {
        Notification::fake();

        (new RolesSeeder)->run();

        $response = $this->postJson('/api/register', $user);

        $response->assertSuccessful();

        $this->assertDatabaseHas('users', ['name' => 'Felipe', 'email' => 'felicrosa@gmail.com']);

        $response->assertJson(['status' => 200, 'message' => 'User has been added succesfully']);

        Notification::assertSentTo(User::first(), VerifyEmail::class);

        $this->assertDatabaseMissing('users', ['password' => '12345678']);
    }

    /**
     * @dataProvider  invalidUserDataProvider
     */
    public function test_user_cant_register_with_invalid_data($user)
    {
        Notification::fake();
        $response = $this->postJson('/api/register', $user);
        $response->assertStatus(422);
        Notification::assertNothingSent();
    }

    public function test_user_cant_register_if_already_logged_in()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/register')->assertStatus(302);
    }

    public function test_user_cant_register_with_existing_email()
    {
        (new RolesSeeder)->run();
        $user = User::factory()->create();
        $data = [
            'name' => 'Felipe',
            'email' => $user->email,
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'role' => 'patient',
        ];
        $this->postJson('api/register', $data)->assertStatus(422);
    }

    public function invalidUserDataProvider(): array
    {
        return [
            ['no Name' => [
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'doctor',
            ]],
            ['no Password' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'role' => 'doctor',
            ]],
            ['no Password Confirmation' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'role' => 'doctor',
            ]],
            ['wrong Password Confirmation' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '1234545678',
                'role' => 'doctor',
            ]],
            ['no email' => [
                'name' => 'Felipe',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'doctor',
            ]],
            ['invalid email' => [
                'name' => 'Felipe',
                'email' => 'felicrosamail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'doctor',
            ]],
            ['invalid name' => [
                'name' => 'Fel4ipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'doctor',
            ]],
            ['shortPassword' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '1454',
                'password_confirmation' => '1454',
                'role' => 'doctor',
            ]],
            ['no Role' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '145434535',
                'password_confirmation' => '145434535',
            ]],
            ['wrong Role' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '145434535',
                'password_confirmation' => '145434535',
                'role' => 'other',
            ]],
        ];
    }

    public function validUsersDataProvider()
    {
        return [
            ['doctor' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'doctor',
            ]],
            ['patient' => [
                'name' => 'Felipe',
                'email' => 'felicrosa@gmail.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'role' => 'patient',
            ]],
        ];
    }
}
