<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Support\CreatesLinkedSchoolRecords;
use Tests\TestCase;

class AdminAccessAndCostFlowTest extends TestCase
{
    use CreatesLinkedSchoolRecords;
    use DatabaseTransactions;

    public function test_admin_login_access_and_cost_creation_flow(): void
    {
        ['admin' => $admin] = $this->createLinkedSchoolRecords();

        $this->get('/operator/biaya')->assertRedirect(route('login'));
        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'qa-password',
        ])->assertRedirect(route('operator.beranda'));
        $this->get(route('operator.beranda'))->assertOk();
        $this->get('/admin/dashboard')->assertOk();

        $name = 'QA Biaya ' . Str::uuid();
        $this->post(route('biaya.store'), [
            'nama' => $name,
            'jumlah' => '125.000',
        ])->assertRedirect(route('biaya.index'));

        $this->assertDatabaseHas('biayas', [
            'nama' => $name,
            'jumlah' => 125000,
            'user_id' => $admin->id,
        ]);
        $this->get(route('biaya.index'))->assertOk()->assertSee($name);

        $this->get(route('logout'))->assertRedirect(route('login'));
        $this->get(route('biaya.index'))->assertRedirect(route('login'));
    }

    public function test_public_registration_creates_a_wali_account_that_admin_can_link(): void
    {
        $email = 'qa-registration-' . Str::uuid() . '@example.test';

        $this->post('/register', [
            'name' => 'QA Registered Wali',
            'email' => $email,
            'password' => 'qa-password',
            'password_confirmation' => 'qa-password',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'akses' => 'wali',
        ]);
        $this->get(route('wali.beranda'))->assertOk();
        $this->get('/operator/beranda')->assertRedirect(route('wali.beranda'));
    }
}
