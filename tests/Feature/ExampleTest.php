<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('retorna resposta de sucesso na rota inicial', function () {
    $this->get('/')->assertOk();
});

it('bloqueia /api/user sem autenticação', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

it('retorna o usuário autenticado via Sanctum', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id);
});
