<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PembayaranNotification extends Notification
{
    use Queueable;

    private $pembayaran;

    public function __construct($pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    public function via($notifiable)
    {
        // Kalau hanya mau simpan ke database
        return ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Konfirmasi Pembayaran')
            ->greeting('Halo, ' . $notifiable->name)
            ->line('Pembayaran Anda telah dikonfirmasi.')
            ->line('Tagihan ID: ' . $this->pembayaran->tagihan_id)
            ->line('Jumlah: Rp ' . number_format($this->pembayaran->jumlah_dibayar, 0, ',', '.'))
            ->action('Lihat Detail', route('pembayaran.show', $this->pembayaran->id))
            ->line('Terima kasih telah melakukan pembayaran.');
    }

    public function toArray($notifiable)
    {
        return [
            'tagihan_id'    => $this->pembayaran->tagihan_id,
            'wali_id'       => $this->pembayaran->wali_id,
            'pembayaran_id' => $this->pembayaran->id,
            'title'         => 'Pembayaran Tagihan',
            'messages'      => "{$this->pembayaran->wali->name} telah melakukan pembayaran tagihan.",
            'url'           => route('pembayaran.show', $this->pembayaran->id),
        ];
    }
}
