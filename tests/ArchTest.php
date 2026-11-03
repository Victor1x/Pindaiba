<?php

arch('controllers não usam DB diretamente')
    ->expect('App\Http\Controllers')
    ->not->toUse('Illuminate\Support\Facades\DB');

arch('actions não dependem de HTTP')
    ->expect('App\Actions')
    ->not->toUse(['Illuminate\Http\Request', 'Illuminate\Http\Response']);

arch('actions são finais e têm sufixo Action')
    ->expect('App\Actions')
    ->toBeFinal()
    ->toHaveSuffix('Action');

arch('DTOs são readonly')
    ->expect('App\DTOs')
    ->toBeReadonly();

arch('sem dd ou dump no código')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch()->preset()->laravel();
