<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_homepage_redirects_to_the_wali_login()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login.wali'));
    }

    public function test_wali_siswa_only_exposes_relationship_actions()
    {
        $this->get('/operator/walisiswa')->assertStatus(405);
    }

    public function test_unimplemented_resource_actions_are_not_registered()
    {
        $this->assertFalse(Route::has('pembayaran.create'));
        $this->assertFalse(Route::has('banksekolah.show'));
        $this->assertFalse(Route::has('wali.tagihan.create'));
        $this->assertFalse(Route::has('wali.pembayaran.index'));
    }
}
