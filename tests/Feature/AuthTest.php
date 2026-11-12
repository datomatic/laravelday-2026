<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('schedule'))->assertRedirect(route('login'));
});

test('a user can log in and log out', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('schedule'));

    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});

test('login fails with wrong credentials and keeps the email', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), ['email' => $user->email, 'password' => 'sbagliata'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', $user->email);

    $this->assertGuest();
});
