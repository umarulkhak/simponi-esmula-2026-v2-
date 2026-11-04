<?php
function formatRupiah($nominal, $prefix = null) {
    $prefix = $prefix ? $prefix : 'Rp. ';
    return "Rp " . number_format($nominal, 0, ',', '.');
}
