<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit;
}
include '../conf/config.php';
// Statistik berita
date_default_timezone_set('Asia/Jakarta');
$jml_berita = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita"))[0];
$jml_draft = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='draft'"))[0];
$jml_published = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='published'"))[0];
$jml_rejected = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='rejected'"))[0];
$level = $_SESSION['level'];
$user_id = $_SESSION['user_id'];
// Filter berita
$where = '';
if ($level == 'wartawan') {
    $where = "WHERE b.id_pengirim='$user_id'";
}
if ($level == 'editor') {
    $where = "WHERE b.status='draft'";
}
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id $where ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Dashboard</title>
  <!-- Google Font: Roboto -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <style>
    :root {
      --primary: #333333; /* Abu-abu sangat gelap */
      --secondary: #666666; /* Abu-abu sedang */
      --accent: #999999; /* Abu-abu terang */
      --bg-gradient: linear-gradient(135deg, #D3D3D3 0%, #808080 50%, #333333 100%); /* Gradient abu-abu */
      --card-bg: rgba(211, 211, 211, 0.95); /* Abu-abu terang transparan */
      --shadow: 0 6px 20px rgba(51, 51, 51, 0.2); /* Bayangan lebih gelap */
      --text-light: #1C2526; /* Abu-abu sangat gelap untuk teks */
      --text-muted: #4B5EAA; /* Abu-abu kebiruan untuk teks sekunder */
      --glow: 0 0 10px rgba(51, 51, 51, 0.3); /* Efek glow lebih gelap */
    }

    body {
      background: var(--bg-gradient);
      min-height: 100vh;
      font-family: 'Roboto', sans-serif;
      background-attachment: fixed;
      font-size: 1.15rem;
    }

    /* Link dan highlight selaras abu-abu */
    a, a:visited, a:active {
      color: var(--text-light); /* Abu-abu sangat gelap */
    }
    a:hover {
      color: var(--primary); /* Abu-abu sangat gelap */
    }

    /* Highlight, badge, dan border selaras abu-abu */
    .badge, .highlight, .border-warning, .border-info {
      background: var(--secondary) !important; /* Abu-abu sedang */
      color: var(--text-light) !important; /* Abu-abu sangat gelap */
      border-color: var(--accent) !important; /* Abu-abu terang */
    }

    /* Table header dan alert selaras abu-abu */
    .table thead th, th {
      background: linear-gradient(90deg, var(--secondary) 80%, var(--primary) 100%) !important; /* Gradient abu-abu */
      color: var(--text-light) !important; /* Abu-abu sangat gelap */
      border: none !important;
    }
    .alert, .alert-info, .alert-warning, .alert-primary {
      background: var(--card-bg) !important; /* Abu-abu terang transparan */
      color: var(--text-light) !important; /* Abu-abu sangat gelap */
      border: 1px solid var(--accent) !important; /* Abu-abu terang */
    }

    .content-wrapper {
      background: var(--card-bg); /* Abu-abu terang transparan */
      border-radius: 2rem;
      box-shadow: var(--shadow);
      padding: 2.5rem 2rem;
      margin-top: 2rem;
      font-size: 1.18rem;
      border: 2px solid var(--accent); /* Abu-abu terang */
    }

    /* FORM TAMBAH BERITA ABU-ABU */
    form, .card, .modal-content, .form-tambah-berita {
      background: var(--card-bg) !important; /* Abu-abu terang transparan */
      border-radius: 1.5rem !important;
      border: 2px solid var(--accent) !important; /* Abu-abu terang */
      box-shadow: var(--shadow);
      color: var(--text-light) !important; /* Abu-abu sangat gelap */
    }
    .form-group label, .form-label {
      color: var(--text-muted) !important; /* Abu-abu kebiruan */
      font-weight: 600;
    }
    .form-control, input, textarea, select {
      background: #D3D3D3 !important; /* Abu-abu terang */
      border: 1.5px solid var(--accent) !important; /* Abu-abu terang */
      border-radius: 1rem !important;
      color: var(--text-light) !important; /* Abu-abu sangat gelap */
      font-size: 1.08rem;
    }
    .form-control:focus, input:focus, textarea:focus, select:focus {
      border-color: var(--primary) !important; /* Abu-abu sangat gelap */
      box-shadow: var(--glow);
    }

    .btn-tambah, .btn-primary, button[type='submit'] {
      background: var(--primary) !important; /* Abu-abu sangat gelap */
      color: #fff !important;
      border-radius: 1.5rem !important;
      font-weight: 600;
      box-shadow: var(--shadow);
      border: none !important;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-tambah:hover, .btn-primary:hover, button[type='submit']:hover {
      background: #262626 !important; /* Abu-abu lebih gelap */
      color: #fff !important;
      box-shadow: 0 4px 16px rgba(51, 51, 51, 0.3); /* Bayangan lebih gelap */
    }

    .small-box {
      border-radius: 1.2rem;
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .small-box.bg-info,
    .small-box.bg-warning,
    .small-box.bg-success,
    .small-box.bg-danger {
      background: var(--primary) !important; /* Abu-abu sangat gelap */
      color: #fff !important;
      box-shadow: var(--shadow);
    }
    .small-box .icon > i, .small-box .icon > svg {
      color: #fff !important;
      opacity: 0.9;
    }
    .small-box .inner h3, .small-box .inner p {
      color: #fff !important;
      text-shadow: 0 1px 8px rgba(0, 0, 0, 0.13);
    }
    .small-box:hover {
      transform: translateY(-4px) scale(1.04);
      box-shadow: 0 8px 32px rgba(51, 51, 51, 0.3); /* Bayangan lebih gelap */
    }

    .table {
      background: rgba(51, 51, 51, 0.1) !important; /* Abu-abu transparan */
      border-radius: 1rem;
      overflow: hidden;
      color: var(--text-light); /* Abu-abu sangat gelap */
      font-size: 1.1rem;
    }
    .table thead th {
      background: linear-gradient(90deg, var(--secondary) 80%, var(--primary) 100%) !important; /* Gradient abu-abu */
      color: var(--text-light);
      border: none;
    }
    .table tbody tr:nth-child(even) {
      background: rgba(153, 153, 153, 0.13) !important; /* Abu-abu terang transparan */
    }
    .table tbody tr:hover {
      background: rgba(51, 51, 51, 0.09) !important; /* Abu-abu transparan saat hover */
    }

    .btn, .badge {
      border-radius: 0.8rem !important;
      font-size: 1.07rem;
      font-weight: 500;
      padding: 0.6em 1.2em;
    }
    .btn-primary, .btn-info {
      background: var(--primary) !important; /* Abu-abu sangat gelap */
      border: none;
      color: #fff;
    }
    .btn-warning {
      background: var(--secondary) !important; /* Abu-abu sedang */
      border: none;
      color: #fff;
    }
    .btn-danger {
      background: linear-gradient(90deg, #d32f2f 60%, #b71c1c 100%) !important; /* Red gradient tetap untuk kontras */
      border: none;
      color: #fff;
    }
    .btn-success {
      background: linear-gradient(90deg, #388e3c 60%, #4caf50 100%) !important; /* Green gradient tetap untuk kontras */
      border: none;
      color: #fff;
    }
    .card, .box, .table-responsive {
      background: var(--card-bg) !important; /* Abu-abu terang transparan */
      border-radius: 1rem;
      box-shadow: var(--shadow);
    }
    .alert, .callout {
      border-radius: 1rem;
    }
    .btn:hover, .btn:focus {
      filter: brightness(1.09);
      box-shadow: 0 4px 20px rgba(51, 51, 51, 0.2); /* Bayangan lebih gelap */
    }

    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 10px;
      background: var(--secondary); /* Abu-abu sedang */
      border-radius: 8px;
    }
    ::-webkit-scrollbar-thumb {
      background: var(--primary); /* Abu-abu sangat gelap */
      border-radius: 8px;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= $jml_berita ?></h3>
              <p>Total Berita</p>
            </div>
            <div class="icon"><i class="fas fa-newspaper"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $jml_draft ?></h3>
              <p>Draft</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $jml_published ?></h3>
              <p>Published</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= $jml_rejected ?></h3>
              <p>Rejected</p>
            </div>
            <div class="icon"><i class="fas fa-times"></i></div>
          </div>
        </div>
      </div>
      <!-- Daftar berita lengkap -->
      <div class="card mt-4 shadow">
        <div class="card-header bg-primary d-flex align-items-center">
          <i class="fas fa-newspaper fa-lg mr-2"></i>
          <h3 class="card-title mb-0">Daftar Berita</h3>
          <span class="ml-2 text-white-50"> Semua berita terbaru, lengkap dengan aksi!</span>
        </div>
        <div class="card-body">
          <?php if($level=='wartawan'): ?>
            <a href="../berita_form.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Tambah Berita</a>
          <?php endif; ?>
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
              <thead class="thead-dark">
                <tr>
                  <th>Judul</th>
                  <th>Kategori</th>
                  <th>Pengirim</th>
                  <th>Status</th>
                  <th>Gambar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                  <td><i class="far fa-file-alt text-info"></i> <b><?= htmlspecialchars($row['judul']) ?></b></td>
                  <td><span class="badge badge-info"><i class="fas fa-tag"></i> <?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                  <td>
                    <span class="d-flex align-items-center">
                      <span class="avatar bg-secondary text-white rounded-circle mr-2" style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;">
                        <i class="fas fa-user"></i>
                      </span> <?= htmlspecialchars($row['username']) ?>
                    </span>
                  </td>
                  <td>
                    <?php
                      $status = $row['status'];
                      $badge = 'secondary';
                      if ($status == 'published') $badge = 'success';
                      elseif ($status == 'draft') $badge = 'warning';
                      elseif ($status == 'rejected') $badge = 'danger';
                    ?>
                    <span class="badge badge-<?= $badge ?> text-uppercase"><i class="fas fa-circle"></i> <?= htmlspecialchars($status) ?></span>
                  </td>
                  <td><?php if($row['gambar']): ?><img src="../upload/<?= htmlspecialchars($row['gambar']) ?>" width="60" class="img-thumbnail shadow-sm"><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                  <td>
                    <?php if($level=='wartawan' && $row['status']=='draft' && $row['id_pengirim']==$user_id): ?>
                      <a href="../berita_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1"><i class="fas fa-edit"></i> Edit</a>
                      <a href="../berita_hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Hapus berita?')"><i class="fas fa-trash"></i> Hapus</a>
                    <?php endif; ?>
                    <?php if($level=='editor' && $row['status']=='draft'): ?>
                      <a href="../berita_approval.php?id=<?= $row['id'] ?>&aksi=publish" class="btn btn-sm btn-success mb-1"><i class="fas fa-upload"></i> Publish</a>
                      <a href="../berita_approval.php?id=<?= $row['id'] ?>&aksi=reject" class="btn btn-sm btn-danger mb-1"><i class="fas fa-times"></i> Reject</a>
                    <?php endif; ?>
                    <a href="../berita_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info mb-1"><i class="fas fa-eye"></i> Detail</a>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>
<!-- JS -->
<script src="plugins/jquery/jquery.min.js"></small-box>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
