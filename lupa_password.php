```php
<?php
include 'conf/config.php';
// Proses form lupa password
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $error = "Email harus diisi.";
    } else {
        // Cek email di database
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE email='$email'");
        if (mysqli_num_rows($cek) == 1) {
            $user = mysqli_fetch_assoc($cek);
            // Generate token reset
            $token = bin2hex(random_bytes(16));
            // Simpan token ke database
            mysqli_query($koneksi, "UPDATE tb_users SET reset_token='$token' WHERE email='$email'");
            // Simulasi kirim link reset (tampilkan di halaman)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
            $success = "Link reset password: <a href='$reset_link'>$reset_link</a>";
        } else {
            $error = "Email tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
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
            flex-direction: column;
        }

        .footer {
            position: sticky;
            top: 0;
            width: 100%;
            background: var(--card-bg);
            border-bottom: 1px solid var(--accent);
            text-align: center;
            padding: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--accent);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            margin: 2rem auto;
            padding: 0;
        }

        .card-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 1rem;
            border-radius: 0.8rem 0.8rem 0 0;
            text-align: center;
        }

        .login-box h1 {
            font-size: 1.8rem;
            color: #fff;
            margin: 0;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group .form-control {
            background: #D3D3D3; /* Abu-abu terang untuk input */
            border: 1px solid var(--accent);
            border-radius: 0.4rem 0 0 0.4rem;
            border-right: none;
            color: var(--text-light);
            font-size: 1rem;
            padding: 0.7rem;
        }

        .input-group-append .input-group-text {
            background: #D3D3D3; /* Abu-abu terang untuk ikon */
            border: 1px solid var(--accent);
            border-left: none;
            border-radius: 0 0.4rem 0.4rem 0;
            color: var(--primary);
            padding: 0.7rem 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        button[type="submit"], .btn-primary {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 0.4rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        button[type="submit"]:hover, .btn-primary:hover {
            background: #262626; /* Abu-abu lebih gelap saat hover */
            transform: translateY(-2px);
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.95rem;
            padding: 0.7rem 1.5rem;
            border: 1px solid var(--primary);
            border-radius: 0.4rem;
            display: inline-block;
            transition: background 0.2s, color 0.2s;
        }

        .links a:hover {
            background: var(--primary);
            color: #fff;
        }

        .alert {
            border-radius: 0.4rem;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            text-align: left;
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

        @media (max-width: 576px) {
            .login-box {
                max-width: 90%;
                margin: 1rem auto;
            }
            .card-header h1 {
                font-size: 1.5rem;
            }
            .form-actions {
                flex-direction: column;
            }
            .input-group .form-control {
                font-size: 0.95rem;
                padding: 0.6rem;
            }
            .input-group-append .input-group-text {
                padding: 0.6rem 0.8rem;
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
    <div class="footer">
        Today: 08:54 PM WIB, Saturday, June 14, 2025
    </div>
    <div class="login-box">
        <div class="card-header">
            <h1><b>Lupa</b> Password</h1>
        </div>
        <div class="card-body login-card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">Kirim</button>
                    <div class="links"><a href="index.php">Login</a></div>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
```