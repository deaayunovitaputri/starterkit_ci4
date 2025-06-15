
<?php
include_once 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
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
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id $where ORDER BY b.created_at DESC");

// Count status for chart
$status_counts = ['draft' => 0, 'published' => 0, 'rejected' => 0];
while ($row = mysqli_fetch_assoc($query)) {
    $status_counts[$row['status']]++;
}
mysqli_data_seek($query, 0); // Reset pointer for table display
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #333333; /* Abu-abu sangat gelap */
            --secondary: #666666; /* Abu-abu sedang */
            --accent: #999999; /* Abu-abu terang */
            --bg-gradient: linear-gradient(90deg, #D3D3D3 0%, #808080 50%, #333333 100%); /* Gradient abu-abu horizontal */
            --card-bg: rgba(211, 211, 211, 0.95); /* Abu-abu terang transparan */
            --shadow: 0 8px 24px rgba(51, 51, 51, 0.15); /* Bayangan lebih lembut */
            --text-light: #1C2526; /* Abu-abu sangat gelap untuk teks */
            --text-muted: #4B5EAA; /* Abu-abu kebiruan untuk teks sekunder */
            --glow: 0 0 8px rgba(51, 51, 51, 0.25); /* Efek glow abu-abu */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .container-wrapper {
            max-width: 1300px; /* Lebih lebar dari sebelumnya */
            margin: 0 auto;
            padding: 1.5rem;
            flex-grow: 1;
        }

        .filter-bar {
            background: var(--card-bg);
            border-radius: 1rem; /* Border radius lebih besar */
            padding: 1.2rem;
            display: flex;
            flex-wrap: wrap; /* Berbeda: wrap untuk responsivitas */
            gap: 1.2rem;
            align-items: center;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            justify-content: space-between; /* Berbeda: elemen lebih tersebar */
        }

        .search-bar {
            flex: 2;
            padding: 0.8rem;
            background: #D3D3D3; /* Abu-abu terang */
            border: 1px solid var(--accent);
            border-radius: 0.6rem; /* Border radius lebih besar */
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .btn-filter {
            background: var(--primary);
            border: none;
            border-radius: 0.6rem;
            padding: 0.8rem 1.8rem;
            color: #fff;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-filter:hover {
            background: #262626; /* Abu-abu lebih gelap */
            transform: translateY(-2px);
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 1rem; /* Border radius lebih besar */
            box-shadow: var(--shadow);
            border: 1px solid var(--accent);
            padding: 2rem; /* Padding lebih besar */
        }

        .card-header {
            text-align: left; /* Berbeda: header rata kiri */
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 0.8rem;
        }

        .card-header h2 {
            font-size: 2rem; /* Ukuran font lebih besar */
            font-weight: 600;
            color: var(--text-light);
            margin: 0;
        }

        .table-responsive {
            border-radius: 0.8rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
            width: 100%;
            background: var(--card-bg);
        }

        .table thead th {
            background: linear-gradient(90deg, var(--secondary) 80%, var(--primary) 100%); /* Gradient abu-abu */
            color: var(--text-light);
            font-weight: 600;
            border: none;
            padding: 0.9rem;
            text-align: center;
        }

        .table tbody tr {
            background: rgba(153, 153, 153, 0.05); /* Abu-abu transparan */
            transition: transform 0.2s;
        }

        .table tbody tr:hover {
            background: rgba(51, 51, 51, 0.1); /* Abu-abu transparan saat hover */
            transform: translateY(-2px);
        }

        .table td {
            padding: 0.9rem;
            text-align: center;
            border-top: 1px solid rgba(153, 153, 153, 0.05);
            font-size: 0.9rem;
        }

        .img-preview {
            max-width: 100px; /* Ukuran preview lebih besar */
            border-radius: 0.6rem;
            border: 1px solid var(--accent);
            background: #D3D3D3;
            padding: 3px;
        }

        .btn-action {
            border-radius: 0.6rem;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            margin: 0.3rem;
            transition: all 0.2s;
        }

        .btn-warning {
            background: var(--secondary);
            color: #fff;
            border: none;
        }

        .btn-danger {
            background: linear-gradient(90deg, #d32f2f, #b71c1c); /* Red gradient untuk kontras */
            color: #fff;
            border: none;
        }

        .btn-success {
            background: linear-gradient(90deg, #388e3c, #4caf50); /* Green gradient untuk kontras */
            color: #fff;
            border: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .badge-status {
            font-size: 0.85rem;
            padding: 0.5em 1em;
            border-radius: 0.6rem;
            font-weight: 500;
        }

        .badge-draft {
            background: var(--secondary);
            color: #fff;
        }

        .badge-published {
            background: var(--secondary); /* Abu-abu untuk konsistensi */
            color: #fff;
        }

        .badge-rejected {
            background: var(--secondary); /* Abu-abu untuk konsistensi */
            color: #fff;
        }

        .fab {
            position: fixed;
            bottom: 3rem; /* Posisi lebih tinggi */
            left: 2rem; /* Berbeda: di kiri bawah */
            background: var(--primary);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            color: #fff;
            font-size: 1.5rem;
            transition: background 0.2s, transform 0.2s;
        }

        .fab:hover {
            background: #262626;
            transform: scale(1.1);
        }

        #canvasPanel {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            padding: 2rem;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            max-width: 600px; /* Lebih lebar */
            width: 95%;
        }

        #canvasPanel button {
            background: var(--primary);
            border: none;
            border-radius: 0.6rem;
            padding: 0.6rem 1.2rem;
            color: #fff;
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
        }

        #canvasPanel button:hover {
            background: #262626;
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
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .search-bar {
                width: 100%;
            }
            .table td, .table th {
                padding: 0.7rem;
                font-size: 0.85rem;
            }
            .btn-action {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
            .img-preview {
                max-width: 80px;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="filter-bar">
        <input type="text" class="search-bar" id="searchInput" placeholder="Cari judul berita..." onkeyup="filterTable()">
        <?php if ($level == 'wartawan'): ?>
            <a href="berita_form.php" class="btn btn-filter"><i class="fas fa-plus mr-2"></i>Tambah Berita</a>
        <?php endif; ?>
        <button class="btn btn-filter" onclick="location.reload();"><i class="fas fa-sync-alt mr-2"></i>Refresh</button>
    </div>
    <div class="glass-card">
        <div class="card-header">
            <h2><i class="fas fa-newspaper mr-2"></i>Daftar Berita</h2>
        </div>
        <div class="table-responsive">
            <table class="table" id="newsTable">
                <thead>
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
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori'] ?: 'Tidak ada kategori') ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <span class="badge-status badge-<?= $row['status'] == 'draft' ? 'draft' : ($row['status'] == 'published' ? 'published' : 'rejected') ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="upload/<?= htmlspecialchars($row['gambar']) ?>" class="img-preview" alt="Gambar Berita">
                            <?php else: ?>
                                <span class="text-muted">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($level == 'wartawan' && $row['status'] == 'draft' && $row['id_pengirim'] == $user_id): ?>
                                <a href="berita_form.php?id=<?= $row['id'] ?>" class="btn btn-action btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="berita_hapus.php?id=<?= $row['id'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus berita?')"><i class="fas fa-trash"></i> Hapus</a>
                            <?php endif; ?>
                            <?php if ($level == 'editor' && $row['status'] == 'draft'): ?>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=publish" class="btn btn-action btn-success"><i class="fas fa-check"></i> Publish</a>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=reject" class="btn btn-action btn-danger"><i class="fas fa-times"></i> Tolak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada berita.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <button class="fab" onclick="openCanvas()"><i class="fas fa-chart-bar"></i></button>
    <div id="canvasPanel" style="display:none;">
        <button onclick="closeCanvas()">Tutup</button>
        <div id="chartContainer"></div>
    </div>
    <div class="footer">
        Today: 07:20 PM WIB, Sunday, June 15, 2025
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    function filterTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let table = document.getElementById('newsTable');
        let tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByTagName('td')[0]; // Filter by Judul column
            if (td) {
                let text = td.textContent || td.innerText;
                tr[i].style.display = text.toLowerCase().indexOf(input) > -1 ? '' : 'none';
            }
        }
    }

    function openCanvas() {
        document.getElementById('canvasPanel').style.display = 'block';
        document.getElementById('chartContainer').innerHTML = '<pre><code class="chartjs">{\n  "type": "bar",\n  "data": {\n    "labels": ["Draft", "Published", "Rejected"],\n    "datasets": [{\n      "label": "Jumlah Berita",\n      "data": [<?= $status_counts['draft'] ?>, <?= $status_counts['published'] ?>, <?= $status_counts['rejected'] ?>],\n      "backgroundColor": ["#666666", "#999999", "#555555"],\n      "borderColor": ["#333333", "#666666", "#333333"],\n      "borderWidth": 1\n    }]\n  },\n  "options": {\n    "scales": {\n      "y": {\n        "beginAtZero": true\n      }\n    }\n  }\n}</code></pre>';
    }

    function closeCanvas() {
        document.getElementById('canvasPanel').style.display = 'none';
    }
</script>
</body>
</html>
