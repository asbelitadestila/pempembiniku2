<div class="shadow-md p-3 mb-3 bg-white rounded-lg">
    <h3><b>Halaman Tambah Produk</b></h3>
</div>

<?php

$kategori = array();
$ambil = $koneksi->query("SELECT * FROM kategori");
while($pecah = $ambil->fetch_assoc())
{
    $kategori[] = $pecah;
}

?>

<form method="post" enctype="multipart/form-data">
    <div class="bg-white shadow-md rounded-lg">
        <div class="p-6">

<!-- Nama kategori -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Nama kategori :</label>
    <div class="w-full sm:w-3/4">
        <select name="id_kategori" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option selected disabled>Pilih Nama kategori</option>
            <?php foreach ($kategori as $key => $value): ?>
            <option value="<?php echo $value['id_kategori']; ?>">
             <?php echo $value['nama_kategori']; ?>

</option>
<?php endforeach ?>
</select>   
</div>
</div>

<!-- Nama Produk -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Nama Produk :</label>
    <div class="w-full sm:w-3/4">
    <input type="text" name="nama" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukan Nama Produk"> 
</div>
</div>

<!-- Harga Produk -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Harga Produk :</label>
    <div class="w-full sm:w-3/4">
    <input type="number" name="harga" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukan Harga Produk"> 
</div>
</div>

<!-- Foto Produk -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Foto Produk :</label>
    <div class="w-full sm:w-3/4">
        <div class="input-foto"></div>
    <input type="file" name="foto[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"> 
    <span class="inline-flex items-center px-3 py-1 mt-3 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 cursor-pointer btn-tambah">
        <i class="fas fa-plus"></i>
            </span>
</div>
</div>

<!-- Deskripsi Produk -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Deskripsi Produk :</label>
    <div class="w-full sm:w-3/4">
        <textarea type="text" name="deskripsi" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukan Deskripsi Produk"></textarea>
</div>
</div>

<!-- Stok Produk -->
<div class="flex flex-wrap items-center mb-4">
    <label class="w-full sm:w-1/4 text-sm font-medium text-gray-700 mb-2 sm:mb-0">Stok Produk :</label>
    <div class="w-full sm:w-3/4">
        <input type="number" name="stok" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukan Stok Produk">
</div>
</div>

<div class="bg-gray-50 px-6 py-3 border-t border-gray-200 rounded-b-lg">
    <div class="flex justify-between items-center">
        <div class="flex-1">
            <button name="simpan" class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Simpan</button>
            </div>
            <div class="flex-shrink-0">
                <a href="index.php?halaman=Produk" class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Kembali</a>
        </div>
    </div>
</div>

</div>
</form>

<?php

if(isset($_POST['simpan']))
{

    $id_kategori = $_POST['id_kategori'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    $nama_foto = $_FILES['foto']['name'];
    $lokasi_foto = $_FILES['foto']['tmp_name'];

    move_uploaded_file($lokasi_foto[0], "../assets/foto_produk/" . $nama_foto[0]);
    $koneksi->query("INSERT INTO produk (id_kategori,nama_produk,harga_produk,
    foto_produk,deskripsi_produk,stok_produk)
    VALUES ('$id_kategori','$nama','$harga','$nama_foto[0]','$deskripsi',
    '$stok')");

    $id_baru = $koneksi->insert_id;


    foreach ($nama_foto as $key => $tiap_nama)
    {
        $tiap_lokasi = $lokasi_foto[$key];
        move_uploaded_file($tiap_lokasi, "../assets/foto_produk/" . $tiap_nama);

        $koneksi->query("INSERT INTO produk_foto (id_produk,nama_produk_foto)
        VALUES ('$id_baru','$tiap_nama')");
    }

    //echo"<pre>";
    //print_r($_FILES['foto']);
    //echo "</pre>";
    echo "<script>alert('data berhasil disimpan'); </script>";
    echo "<script>location='index.php?halaman=Produk'; </script>";
}

?>
