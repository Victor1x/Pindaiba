<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('responde o health check', function () {
    $this->get('/up')->assertOk();
});

it('bloqueia /user sem autenticação', function () {
    $this->getJson('/user')->assertUnauthorized();
});

it('retorna o usuário autenticado via Sanctum', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id);
});
