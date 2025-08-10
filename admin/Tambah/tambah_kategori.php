<div class="shadow-md p-3 mb-3 bg-white rounded-lg">
    <h3><b>Halaman Tambah kategori</b></h3>
</div>

<form method="post">
    <div class="shadow-md bg-white rounded-lg">
        <div class="p-6">

    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Nama -->
    <label class="w-full md:w-1/4 px-3 py-2 text-gray-700 text-sm font-medium">Nama kategori :</label>
    <div class="w-full md:w-3/4 px-3">
        <input type="text" name="nama" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan Nama kategori" required>

</div>
</div>
</div>

<div class="bg-gray-50 px-6 py-3 border-t border-gray-200 rounded-b-lg">
    <div class="flex flex-wrap -mx-3">
        <div class="w-full md:w-11/12 px-3">
            <button name="simpan" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition duration-200">Simpan</button>
            </div>
            <div class="w-full md:w-1/12 px-3 text-right">
                <a href="index.php?halaman=kategori" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition duration-200 inline-block">Kembali</a>
        </div>
    </div>
</div>


</div>
</form>

<?php

if(isset($_POST['simpan']))
{
    $nama = $_POST['nama'];

    $koneksi->query("INSERT INTO kategori (nama_kategori) VALUES ('$nama')");

    echo "<script>alert('data berhasil disimpan'); </script>";
    echo "<script>location='index.php?halaman=kategori'; </script>";
}

?>
