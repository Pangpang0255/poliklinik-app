<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprehensiveSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_change_password_flow()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
            'role'     => 'pasien',
        ]);

        // Salah password lama
        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'password_lama'              => 'wrongpassword',
            'password_baru'              => 'newpassword123',
            'password_baru_confirmation' => 'newpassword123',
        ]);
        $response->assertSessionHasErrors('password_lama');

        // Password baru sama dengan lama
        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'password_lama'              => 'oldpassword123',
            'password_baru'              => 'oldpassword123',
            'password_baru_confirmation' => 'oldpassword123',
        ]);
        $response->assertSessionHasErrors('password_baru');

        // Kurang dari 8 karakter
        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'password_lama'              => 'oldpassword123',
            'password_baru'              => 'short',
            'password_baru_confirmation' => 'short',
        ]);
        $response->assertSessionHasErrors('password_baru');

        // Berhasil ganti password
        $response = $this->actingAs($user)->post(route('profile.password.update'), [
            'password_lama'              => 'oldpassword123',
            'password_baru'              => 'newsecretpassword123',
            'password_baru_confirmation' => 'newsecretpassword123',
        ]);
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newsecretpassword123', $user->fresh()->password));
    }

    public function test_notification_flow()
    {
        $user1 = User::factory()->create(['role' => 'pasien']);
        $user2 = User::factory()->create(['role' => 'dokter']);

        $notif1 = AppNotification::create([
            'user_id' => $user1->id,
            'title'   => 'Notif User 1',
            'message' => 'Pesan 1',
            'type'    => 'payment_verified',
            'is_read' => false,
        ]);

        $notif2 = AppNotification::create([
            'user_id' => $user2->id,
            'title'   => 'Notif User 2',
            'message' => 'Pesan 2',
            'type'    => 'payment_created',
            'is_read' => false,
        ]);

        // User 1 cannot mark User 2's notification as read
        $response = $this->actingAs($user1)->post(route('notifications.read', $notif2->id));
        $response->assertStatus(404);

        // User 1 can mark own notification
        $response = $this->actingAs($user1)->post(route('notifications.read', $notif1->id));
        $response->assertSessionHas('success');
        $this->assertTrue($notif1->fresh()->is_read);

        // Mark all read
        $notif3 = AppNotification::create([
            'user_id' => $user1->id,
            'title'   => 'Notif 3',
            'message' => 'Pesan 3',
            'is_read' => false,
        ]);
        $response = $this->actingAs($user1)->post(route('notifications.read-all'));
        $this->assertTrue($notif3->fresh()->is_read);
    }

    public function test_export_excel_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dokter = User::factory()->create(['role' => 'dokter']);

        // Admin exports
        $response = $this->actingAs($admin)->get(route('admin.dokter.export'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('admin.obat.export'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('admin.pasien.export'));
        $response->assertStatus(200);

        // Dokter exports
        $response = $this->actingAs($dokter)->get(route('dokter.jadwal.export'));
        $response->assertStatus(200);

        $response = $this->actingAs($dokter)->get(route('dokter.riwayat.export'));
        $response->assertStatus(200);
    }

    public function test_payment_upload_and_verification_flow()
    {
        Storage::fake('public');

        $poli = Poli::create([
            'nama_poli'  => 'Poli Umum',
            'keterangan' => 'Poli Umum',
        ]);

        $dokter = User::factory()->create([
            'role'    => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $pasien = User::factory()->create([
            'role' => 'pasien',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter'   => $dokter->id,
            'hari'        => 'Senin',
            'jam_mulai'   => '08:00:00',
            'jam_selesai' => '12:00:00',
        ]);

        $daftar = DaftarPoli::create([
            'id_pasien'  => $pasien->id,
            'id_jadwal'  => $jadwal->id,
            'keluhan'    => 'Sakit kepala',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli'    => $daftar->id,
            'tgl_periksa'       => now(),
            'catatan'           => 'Istirahat cukup',
            'biaya_periksa'     => 150000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        // 1. Pasien upload bukti bayar
        $file = UploadedFile::fake()->image('bukti.jpg');
        $response = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_bayar' => $file,
        ]);
        $response->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('menunggu_verifikasi', $periksa->status_pembayaran);
        $this->assertNotNull($periksa->bukti_bayar);

        // Notifikasi harus terkirim ke dokter
        $doctorNotif = AppNotification::where('user_id', $dokter->id)
            ->where('type', 'payment_created')
            ->first();
        $this->assertNotNull($doctorNotif);
        $this->assertStringContainsString('Pasien ' . $pasien->nama, $doctorNotif->message);

        // 2. Admin verifikasi pembayaran
        $response = $this->actingAs($admin)->post(route('admin.verifikasi.konfirmasi', $periksa->id));
        $response->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('terverifikasi', $periksa->status_pembayaran);

        // Notifikasi harus terkirim ke pasien
        $pasienNotif = AppNotification::where('user_id', $pasien->id)
            ->where('type', 'payment_verified')
            ->first();
        $this->assertNotNull($pasienNotif);
        $this->assertStringContainsString('Lunas', $pasienNotif->message);

        // 3. Pasien buka dashboard & riwayat pendaftaran
        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));
        $response->assertStatus(200);

        // 4. Test pay route redirect
        $response = $this->actingAs($pasien)->post(route('pasien.pembayaran.pay', $periksa->id));
        $response->assertRedirect(route('pasien.pembayaran.index'));

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.show', $daftar->id));
        $response->assertStatus(200);
    }
}
