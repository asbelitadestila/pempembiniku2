<div class="shadow p-3 mb-3 bg-white rounded">
    <h3 class="font-bold">Halaman Transaksi</h3>
</div>

<?php

$transaksi = array();
$ambil = $koneksi->query("SELECT * FROM transaksi");
while($pecah = $ambil->fetch_assoc())
{
    $transaksi[] = $pecah;
}

?>

<div class="bg-white shadow mt-3 rounded"> 
    <div class="p-6">
        <table class="w-full border-collapse border border-gray-300" id="tables">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">NO</th>
                    <th class="border border-gray-300 p-2">Nama</th>
                    <th class="border border-gray-300 p-2">Tanggal</th>
                    <th class="border border-gray-300 p-2">Alamat</th>
                    <th class="border border-gray-300 p-2">Total</th>
                    <th class="border border-gray-300 p-2">Status</th>
                    <th class="border border-gray-300 p-2">Opsi</th>
                </tr>
            </thead>
            <tbody>

            <?php foreach ($transaksi as $key => $value): ?>
                
                <tr class="hover:bg-gray-100 striped:bg-gray-50">
                    <td class="text-center border border-gray-300 p-2" style="width: 50px;"> <?php echo $key+1;  ?> </td>
                    <td class="border border-gray-300 p-2"> <?php echo $value['nama_pelanggan']; ?> </td>
                    <td class="border border-gray-300 p-2"> <?php echo date("d F Y", strtotime($value['tanggal'])); ?> </td>
                    <td class="border border-gray-300 p-2 uppercase"><?php echo $value['provinsi']; ?>, <?php echo $value['kota']; ?>, <?php echo $value['kecamatan']; ?>,  <?php echo $value['detail_alamat']; ?> </td>
                    <td class="border border-gray-300 p-2">Rp. <?php echo number_format($value['total']); ?> </td>
                    <td class="border border-gray-300 p-2"> <?php echo $value['status']; ?></td>
                    <td class="text-center border border-gray-300 p-2" style="width: 200px;">
                         <?php if (strtolower($value['status']) == 'settlement'): ?>
                            <button type="button"
                                    class="proses-pesanan-btn inline-block px-2 py-1 text-sm text-white bg-green-500 rounded hover:bg-green-600"
                                    data-id="<?php echo $value['id']; ?>">
                                <i class="fas fa-truck-pickup"></i> Proses
                            </button>
                        <?php endif; ?>
                        <a href="index.php?halaman=detail_pembelian&id=<?php 
                        echo $value['id']; ?>" class="inline-block px-2 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
                            <i class="fas fa-info"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<div id="prosesPesananModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md m-4">
        
        <form id="form-resi">
            <div class="flex justify-between items-center border-b px-6 py-4">
                <h5 class="text-xl font-bold text-gray-800">Proses & Kirim Pesanan</h5>
                <button type="button" class="text-gray-400 hover:text-red-600 text-3xl closeModalBtn">&times;</button>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">Masukkan detail pengiriman untuk ID Pesanan: <strong id="modal-transaksi-id" class="text-red-700"></strong></p>
                
                <div>
                    <label for="no_resi" class="block text-sm font-medium text-gray-700 mb-1">Nomor Resi (AWB)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-barcode text-gray-400"></i>
                        </div>
                        <input type="text" id="no_resi" name="no_resi" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: JP1234567890" required>
                    </div>
                </div>                
                
                <input type="hidden" id="id_transaksi" name="id_transaksi">
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t text-right">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2 closeModalBtn">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700">
                    <i class="fas fa-paper-plane mr-2"></i>Simpan & Kirim Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
    // === LOGIKA UNTUK MODAL PROSES PESANAN ===

    const modal = $('#prosesPesananModal');

    // 1. Event listener untuk membuka modal saat tombol "Proses" diklik
    $('.proses-pesanan-btn').on('click', function() {
        // Ambil ID dari atribut data-id
        var transaksiId = $(this).data('id');

        // Isi ID ke dalam modal dan form
        $('#modal-transaksi-id').text(transaksiId);
        $('#id_transaksi').val(transaksiId);

        // Reset form dan tampilkan modal
        $('#form-resi')[0].reset();
        modal.removeClass('hidden');
    });

    // 2. Event listener untuk menutup modal
    $('.closeModalBtn').on('click', function() {
        modal.addClass('hidden');
    });

    // 3. Event listener saat form di dalam modal disubmit
    $('#form-resi').on('submit', function(e) {
        e.preventDefault(); // Mencegah form submit cara biasa

        var formData = $(this).serialize(); // Ambil semua data form
        var submitButton = $(this).find('button[type="submit"]');

        // Tampilkan status loading di tombol
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Lakukan AJAX call ke file PHP
        $.ajax({
            type: 'POST',
            url: './Edit/edit_transaksi.php', // Sesuaikan path ke file PHP Anda
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload(); // Reload halaman untuk melihat perubahan
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi. Gagal menghubungi server.');
            },
            complete: function() {
                // Kembalikan tombol ke keadaan semula
                submitButton.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Simpan & Kirim Pesanan');
            }
        });
    });
});
</script>
