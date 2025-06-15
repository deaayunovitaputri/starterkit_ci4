
<?php
include_once 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'wartawan') {
    header('Location: index.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch categories
$kategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");

// Edit news if ID is provided
$judul = $isi = $id_kategori = $gambar = '';
$edit = false;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = mysqli_query($koneksi, "SELECT * FROM berita WHERE id='$id' AND id_pengirim='$user_id'");
    if ($data = mysqli_fetch_assoc($q)) {
        $judul = $data['judul'];
        $isi = $data['isi'];
        $id_kategori = $data['id_kategori'];
        $gambar = $data['gambar'];
        $edit = true;
    }
}

// Process form submission
if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $id_kategori = intval($_POST['id_kategori']);
    $gambar_name = $gambar;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/' . $gambar_name);
    }
    if ($edit) {
        $sql = "UPDATE berita SET judul='$judul', isi='$isi', id_kategori='$id_kategori', gambar='$gambar_name' WHERE id='$id' AND id_pengirim='$user_id'";
    } else {
        $sql = "INSERT INTO berita (judul, isi, id_kategori, gambar, id_pengirim, status) VALUES ('$judul', '$isi', '$id_kategori', '$gambar_name', '$user_id', 'draft')";
    }
    if (mysqli_query($koneksi, $sql)) {
        header('Location: berita_list.php');
        exit;
    } else {
        $error = 'Gagal menyimpan berita.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit ? 'Edit' : 'Tambah' ?> Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #333333; /* Abu-abu sangat gelap */
            --secondary: #666666; /* Abu-abu sedang */
            --accent: #999999; /* Abu-abu terang */
            --bg-gradient: linear-gradient(45deg, #D3D3D3 0%, #808080 50%, #333333 100%); /* Gradient abu-abu dengan arah berbeda */
            --card-bg: rgba(211, 211, 211, 0.95); /* Abu-abu terang transparan */
            --shadow: 0 10px 30px rgba(51, 51, 51, 0.15); /* Bayangan lebih lembut */
            --text-light: #1C2526; /* Abu-abu sangat gelap untuk teks */
            --text-muted: #4B5EAA; /* Abu-abu kebiruan untuk teks sekunder */
            --glow: 0 0 10px rgba(51, 51, 51, 0.25); /* Efek glow abu-abu */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            color: var(--text-light);
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            padding: 2rem;
        }

        .container-wrapper {
            width: 100%;
            max-width: 1000px; /* Lebih lebar dari halaman sebelumnya */
            margin: 0 auto;
            flex-grow: 1;
        }

        .news-card {
            background: var(--card-bg);
            border-radius: 1rem; /* Border radius lebih kecil */
            box-shadow: var(--shadow);
            padding: 2.5rem; /* Padding lebih besar */
            border: 1px solid var(--accent);
        }

        .card-header {
            text-align: left; /* Berbeda: header rata kiri */
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 1rem;
        }

        .card-header h3 {
            color: var(--text-light);
            font-weight: 600;
            font-size: 2rem; /* Ukuran font lebih besar */
            margin: 0;
        }

        .badge-status {
            background: var(--secondary);
            color: #fff;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 1rem; /* Border radius lebih bulat */
            margin-left: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr; /* Proporsi kolom berbeda */
            gap: 2rem; /* Jarak antar elemen lebih besar */
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 0.6rem;
            display: block;
        }

        label[for*='kategori'], .kategori-label {
            color: var(--text-light) !important; /* Konsisten dengan teks lain */
        }

        .form-control, select, textarea {
            background: #D3D3D3 !important; /* Abu-abu terang */
            border: 1px solid var(--accent) !important;
            border-radius: 0.6rem !important; /* Border radius lebih kecil */
            color: var(--text-light) !important;
            font-size: 1rem;
            padding: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, select:focus, textarea:focus {
            border-color: var(--primary) !important;
            box-shadow: var(--glow);
        }

        select, select option {
            background: #D3D3D3 !important; /* Abu-abu terang */
            color: var(--text-light) !important;
        }

        textarea.form-control {
            min-height: 150px; /* Tinggi textarea lebih besar */
            resize: vertical;
            line-height: 1.5;
        }

        .custom-file-input {
            border-radius: 0.6rem;
            cursor: pointer;
        }

        .custom-file-label {
            background: rgba(153, 153, 153, 0.1); /* Abu-abu transparan */
            border: 1px solid var(--accent);
            border-radius: 0.6rem;
            padding: 0.7rem 1.2rem;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .custom-file-label::after {
            background: var(--primary);
            color: #fff;
            border-radius: 0 0.6rem 0.6rem 0;
            padding: 0.7rem 1.2rem;
        }

        .img-preview {
            max-width: 180px; /* Ukuran preview lebih besar */
            margin-top: 0.7rem;
            border-radius: 0.6rem;
            border: 1px solid var(--accent);
            background: #D3D3D3;
            padding: 4px;
            transition: transform 0.2s;
        }

        .img-preview:hover {
            transform: scale(1.03); /* Zoom lebih halus */
        }

        .alert {
            background: #D3D3D3; /* Abu-abu terang untuk alert */
            border: 1px solid #808080; /* Abu-abu sedang */
            border-radius: 0.6rem;
            padding: 0.9rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.2rem;
            color: var(--text-light);
        }

        .form-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.4rem;
        }

        .button-group {
            display: flex;
            justify-content: flex-start; /* Berbeda: tombol rata kiri */
            gap: 1.2rem;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            background: var(--primary);
            border: none;
            border-radius: 0.6rem;
            padding: 0.9rem 2rem;
            font-weight: 600;
            color: #fff;
            font-size: 1rem;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-secondary {
            background: var(--secondary);
            color: #fff;
        }

        .btn-primary:hover {
            background: #262626; /* Abu-abu lebih gelap */
            transform: translateY(-2px);
        }

        .btn-secondary:hover {
            background: #555555; /* Abu-abu sedikit lebih gelap */
            transform: translateY(-2px);
        }

        .footer {
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .news-card {
                padding: 1.8rem;
            }
            .card-header h3 {
                font-size: 1.6rem;
            }
            .form-control, select, textarea {
                font-size: 0.95rem;
                padding: 0.7rem;
            }
            .button-group {
                justify-content: center;
            }
            .img-preview {
                max-width: 140px;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="news-card">
        <div class="card-header">
            <h3><i class="fas fa-edit mr-2"></i><?= $edit ? 'Edit' : 'Tambah' ?> Berita</h3>
            <span class="badge-status"><?= $edit ? 'Edit Mode' : 'Tambah Baru' ?></span>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Judul Berita</label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($judul) ?>" required placeholder="Masukkan judul berita">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Kategori</label>
                        <select name="id_kategori" id="id_kategori" class="form-control custom-select" required>
                            <option value="" disabled hidden <?= $id_kategori == '' ? 'selected' : '' ?>>Pilih Kategori</option>
                            <?php
                            if ($kategori instanceof mysqli_result && $kategori->num_rows > 0) mysqli_data_seek($kategori, 0);
                            while ($row = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $row['id'] ?>" <?= $id_kategori == $row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div id="kategoriError" class="form-text text-danger" style="display:none;"><i class="fas fa-exclamation-triangle"></i> Silakan pilih kategori!</div>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> Isi Berita</label>
                        <textarea name="isi" class="form-control" rows="5" required placeholder="Tulis isi berita di sini..."><?= htmlspecialchars($isi) ?></textarea>
                        <small class="form-text"><i class="fas fa-info-circle mr-1"></i>Gunakan bahasa yang jelas dan informatif.</small>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> Gambar</label>
                        <?php if ($gambar): ?>
                            <div>
                                <img src="upload/<?= htmlspecialchars($gambar) ?>" class="img-preview">
                            </div>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" name="gambar" class="custom-file-input" id="gambarInput">
                            <label class="custom-file-label" for="gambarInput">Pilih file gambar...</label>
                        </div>
                        <small class="form-text"><i class="fas fa-info-circle mr-1"></i>Format: jpg, png, max 2MB.</small>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" name="simpan" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
                    <a href="berita_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
    <div class="footer">
        Today: 07:15 PM WIB, Sunday, June 15, 2025
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script>
$(function () {
    bsCustomFileInput.init();
    $('form').on('submit', function(e) {
        var kategori = $('#id_kategori').val();
        if (!kategori) {
            $('#kategoriError').show();
            $('#id_kategori').addClass('is-invalid');
            $('#id_kategori').focus();
            e.preventDefault();
            return false;
        } else {
            $('#kategoriError').hide();
            $('#id_kategori').removeClass('is-invalid');
        }
    });
    $('#id_kategori').on('change', function() {
        if ($(this).val()) {
            $('#kategoriError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
</body>
</html>
