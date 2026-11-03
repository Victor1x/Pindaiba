<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('responde o health check', function () {
    $this->get('/up')->assertOk();
});

it('bloqueia /v1/user sem autenticação', function () {
    $this->getJson('/v1/user')->assertUnauthorized();
});

it('retorna JSON 401 mesmo sem header Accept', function () {
    $this->get('/v1/user')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('retorna o usuário autenticado via UserResource', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/v1/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.nome', $user->name)
        ->assertJsonMissingPath('data.password');
});
