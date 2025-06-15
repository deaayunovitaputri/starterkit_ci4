<?php
include 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location: index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
// Ambil data user
$query = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
// Proses update profil
if (isset($_POST['update'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi = trim($_POST['konfirmasi']);
    $update_query = "UPDATE tb_users SET nama_lengkap='$nama_lengkap', email='$email'";
    if (!empty($password_baru)) {
        if ($password_baru !== $konfirmasi) {
            $error = "Konfirmasi password tidak cocok.";
        } else {
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_query .= ", password='$password_hash'";
        }
    }
    $update_query .= " WHERE id='$user_id'";
    if (!isset($error)) {
        $simpan = mysqli_query($koneksi, $update_query);
        if ($simpan) {
            $success = "Profil berhasil diperbarui.";
            // Refresh data user
            $query = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE id='$user_id'");
            $user = mysqli_fetch_assoc($query);
        } else {
            $error = "Gagal memperbarui profil.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
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
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--accent);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            margin: 2rem;
        }

        .card-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 1.5rem;
            border-radius: 0.8rem 0.8rem 0 0;
            text-align: center;
        }

        .card-header h1 {
            font-size: 1.8rem;
            color: #fff;
            margin: 0;
            font-weight: 600;
        }

        .card-header i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #fff;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            font-size: 0.95rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group .form-control {
            background: #D3D3D3; /* Abu-abu terang untuk input */
            border: 1px solid var(--accent);
            border-radius: 0.4rem;
            color: var(--text-light);
            font-size: 0.95rem;
            padding: 0.7rem;
            width: 100%;
        }

        .form-group .form-control:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        button[type="submit"], .btn-primary {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 0.7rem 2rem;
            border-radius: 0.4rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            width: 100%;
        }

        button[type="submit"]:hover, .btn-primary:hover {
            background: #262626; /* Abu-abu lebih gelap saat hover */
            transform: translateY(-2px);
        }

        .links {
            margin-top: 1rem;
            text-align: center;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #262626;
            text-decoration: underline;
        }

        .alert {
            border-radius: 0.4rem;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: #D3D3D3; /* Abu-abu terang untuk alert */
            border: 1px solid #808080;
            color: #333333;
        }

        .alert-success {
            background: #D3D3D3; /* Abu-abu terang untuk alert */
            border: 1px solid #666666;
            color: #1C2526;
        }

        .footer {
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            padding: 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-radius: 0 0 0.8rem 0.8rem;
        }

        @media (max-width: 576px) {
            .login-box {
                max-width: 90%;
                margin: 1rem;
            }
            .card-header h1 {
                font-size: 1.5rem;
            }
            .card-header i {
                font-size: 1.5rem;
            }
            .form-group .form-control {
                font-size: 0.9rem;
                padding: 0.6rem;
            }
            .alert {
                font-size: 0.9rem;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="card-header">
            <i class="fas fa-user-circle"></i>
            <h1><b>Edit</b> Profil</h1>
        </div>
        <div class="card-body login-card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" placeholder="Nama Lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password_baru">Password Baru (opsional)</label>
                    <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Password Baru">
                </div>
                <div class="form-group">
                    <label for="konfirmasi">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi" id="konfirmasi" class="form-control" placeholder="Konfirmasi Password Baru">
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update Profil</button>
                <div class="links">
                    <a href="berita_list.php">Kembali</a>
                </div>
            </form>
        </div>
        <div class="footer">
            Today: 07:00 PM WIB, Sunday, June 15, 2025
        </div>
    </div>
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
