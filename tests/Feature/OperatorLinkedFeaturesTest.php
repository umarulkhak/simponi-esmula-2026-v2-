<?php

namespace Tests\Feature;

use App\Models\BankSekolah;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Support\CreatesLinkedSchoolRecords;
use Tests\TestCase;

class OperatorLinkedFeaturesTest extends TestCase
{
    use CreatesLinkedSchoolRecords;
    use DatabaseTransactions;

    public function test_operator_workspaces_exports_and_invoice_follow_linked_records(): void
    {
        ['admin' => $admin, 'wali' => $wali, 'student' => $student, 'invoice' => $invoice] =
            $this->createLinkedSchoolRecords();

        $bankId = DB::table('banks')->insertGetId([
            'sandi_bank' => 'QA-' . substr((string) $admin->id, 0, 10),
            'nama_bank' => 'QA Bank',
        ]);
        $schoolBank = BankSekolah::create([
            'kode' => 'QA-' . substr((string) $admin->id, 0, 10),
            'nama_bank' => 'QA Bank',
            'nama_rekening' => 'QA School Account',
            'nomor_rekening' => '000000000000000',
        ]);
        $payment = Pembayaran::create([
            'tagihan_id' => $invoice->id,
            'wali_id' => $wali->id,
            'bank_sekolah_id' => $schoolBank->id,
            'tanggal_bayar' => now(),
            'status_konfirmasi' => 'sudah',
            'jumlah_dibayar' => 275000,
            'metode_pembayaran' => 'transfer',
            'nama_bank_pengirim' => 'QA Bank',
            'nama_rekening_pengirim' => 'QA Wali',
            'nomor_rekening_pengirim' => '000000000000001',
            'user_id' => $admin->id,
        ]);
        $invoice->update(['status' => 'lunas']);

        $this->actingAs($admin);

        $this->put('/operator/siswa/' . $student->id, [
            'wali_id' => $wali->id,
            'nama' => $student->nama,
            'nisn' => $student->nisn,
            'kelas' => 'VIII',
            'angkatan' => $student->angkatan,
            'status' => 'aktif',
        ])->assertRedirect(route('siswa.index'));
        $this->assertDatabaseHas('siswas', [
            'id' => $student->id,
            'kelas' => 'VIII',
        ]);

        foreach ([
            '/operator/beranda',
            '/operator/user',
            '/operator/wali',
            '/operator/wali/' . $wali->id,
            '/operator/siswa',
            '/operator/siswa/' . $student->id,
            '/operator/biaya',
            '/operator/biaya/create',
            '/operator/tagihan',
            '/operator/tagihan/create',
            '/operator/tagihan/' . $student->id,
            '/operator/pembayaran',
            '/operator/pembayaran/' . $payment->id,
            '/operator/banksekolah',
            '/operator/banksekolah/create',
        ] as $uri) {
            $response = $this->get($uri);
            $this->assertSame(200, $response->status(), $uri);
        }

        $this->get('/operator/invoice/cetak/' . $student->id)->assertOk();
        $this->get('/operator/dashboard/tagihan/export')->assertOk();
        $this->get('/operator/operator/invoice/cetak/' . $student->id)->assertNotFound();
        $this->get('/operator/operator/tagihan/export')->assertNotFound();

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $invoice->id,
            'wali_id' => $wali->id,
            'bank_sekolah_id' => $schoolBank->id,
        ]);
        $this->assertDatabaseHas('tagihan_details', [
            'tagihan_id' => $invoice->id,
            'jumlah_biaya' => 275000,
        ]);
        $this->assertDatabaseHas('banks', ['id' => $bankId, 'nama_bank' => 'QA Bank']);
    }
}
