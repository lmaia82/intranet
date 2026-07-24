<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_a_raiz_encaminha_para_a_pagina_de_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
