<?php

namespace App\Traits;

/**
 * Trait HasFormatRupiah
 *
 * Menyediakan method untuk format field angka menjadi Rupiah.
 */
trait HasFormatRupiah
{
    /**
     * Format nilai field ke dalam format Rupiah.
     *
     * @param  string  $field   Nama field di database/model
     * @param  string  $prefix  Prefix mata uang (default: Rp)
     * @return string
     */
    public function formatRupiah(string $field, string $prefix = 'Rp'): string
    {
        $nominal = $this->attributes[$field] ?? 0;

        return $prefix . ' ' . number_format((float) $nominal, 0, ',', '.');
    }
}
