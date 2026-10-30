<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_application_boots_and_registers_the_homepage_route(): void
    {
        $this->assertTrue(Route::has('front.index'));
        $this->assertSame('/', route('front.index', [], false));
    }
}
