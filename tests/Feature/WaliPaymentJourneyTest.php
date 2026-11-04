<?php

namespace Tests\Feature;

use App\Models\BankSekolah;
use App\Models\Pembayaran;
use App\Models\WaliBank;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Support\CreatesLinkedSchoolRecords;
use Tests\TestCase;

class WaliPaymentJourneyTest extends TestCase
{
    use CreatesLinkedSchoolRecords;
    use DatabaseTransactions;

    public function test_wali_login_payment_and_admin_confirmation_preserve_linked_statuses(): void
    {
        ['admin' => $admin, 'wali' => $wali, 'student' => $student, 'invoice' => $invoice] =
            $this->createLinkedSchoolRecords();
        $other = $this->createLinkedSchoolRecords();

        $bankCode = 'QA-' . substr((string) $admin->id, 0, 10);
        DB::table('banks')->insert([
            'sandi_bank' => $bankCode,
            'nama_bank' => 'QA Bank',
        ]);
        $schoolBank = BankSekolah::create([
            'kode' => $bankCode,
            'nama_bank' => 'QA Bank',
            'nama_rekening' => 'QA School Account',
            'nomor_rekening' => '000000000000000',
        ]);
        $senderBank = WaliBank::create([
            'wali_id' => $wali->id,
            'kode' => $bankCode,
            'nama_bank' => 'QA Bank',
            'nama_rekening' => 'QA Wali (DEMO)',
            'nomor_rekening' => '000000000000001',
        ]);
        Notification::fake();
        Storage::fake(config('filesystems.default'));

        $this->post('/login', [
            'email' => $wali->email,
            'password' => 'qa-password',
        ])->assertRedirect(route('wali.beranda'));
        $this->get(route('wali.beranda'))->assertOk();
        $this->put(route('wali.profile.update', $admin->id), [
            'name' => 'Compromised Account',
            'email' => $admin->email,
            'nohp' => '081200000099',
        ])->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => $admin->name,
        ]);
        $this->get(route('wali.tagihan.index'))->assertOk()->assertSee($student->nama);
        $this->get(route('wali.tagihan.show', $student->id))->assertOk()->assertSee('QA SPP');
        $this->get(route('wali.tagihan.show', $other['student']->id))->assertForbidden();

        $this->get(route('wali.pembayaran.create', ['tagihan_id' => [$invoice->id]]))
            ->assertOk()
            ->assertSee('QA School Account');

        $this->post(route('wali.pembayaran.store'), [
            'tagihan_id' => [$invoice->id],
            'bank_sekolah_id' => $schoolBank->id,
            'wali_bank_id' => $senderBank->id,
            'tanggal_bayar' => now()->toDateString(),
            'bukti_bayar' => UploadedFile::fake()->image('qa-proof.png'),
        ])->assertRedirect(route('wali.tagihan.index'));

        $payment = Pembayaran::where('tagihan_id', $invoice->id)->firstOrFail();
        $this->assertSame('belum', $payment->status_konfirmasi);
        $this->assertSame(275000.0, (float) $payment->jumlah_dibayar);
        $this->assertTrue(Storage::disk(config('filesystems.default'))->exists($payment->bukti_bayar));
        $this->assertDatabaseHas('pembayarans', [
            'id' => $payment->id,
            'wali_id' => $wali->id,
            'wali_bank_id' => $senderBank->id,
            'bank_sekolah_id' => $schoolBank->id,
            'status_konfirmasi' => 'belum',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $invoice->id,
            'status' => 'baru',
        ]);

        $this->post(route('wali.pembayaran.store'), [
            'tagihan_id' => [$invoice->id],
            'bank_sekolah_id' => $schoolBank->id,
            'wali_bank_id' => $senderBank->id,
            'tanggal_bayar' => now()->toDateString(),
            'bukti_bayar' => UploadedFile::fake()->image('duplicate-proof.png'),
        ])->assertRedirect();
        $this->assertCount(1, Storage::disk(config('filesystems.default'))->allFiles('public'));
        $this->assertSame(1, Pembayaran::where('tagihan_id', $invoice->id)->count());

        $this->get(route('logout'))->assertRedirect(route('login.wali'));
        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'qa-password',
        ])->assertRedirect(route('operator.beranda'));
        $this->put(route('pembayaran.update.multiple'), [
            'pembayaran_ids' => [$payment->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('pembayarans', [
            'id' => $payment->id,
            'status_konfirmasi' => 'sudah',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $invoice->id,
            'status' => 'lunas',
        ]);
        $this->get(route('pembayaran.show', $payment->id))
            ->assertOk()
            ->assertSee('Rp 275.000');

        $this->get(route('logout'))->assertRedirect(route('login'));
        $this->post('/login', [
            'email' => $wali->email,
            'password' => 'qa-password',
        ])->assertRedirect(route('wali.beranda'));
        $this->get(route('wali.invoice.cetak', $student->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
