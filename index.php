<?php
// Simple index with links
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Palawan National School - Hybrid QR Code Based Attendance Monitoring System</title>
    <?php $cssFile = file_exists(__DIR__ . '/style.min.css') ? 'style.min.css' : 'style.css'; ?>
    <link rel="stylesheet" href="<?php echo $cssFile; ?>">
    <link rel="manifest" href="/puta/manifest.json">
    <meta name="theme-color" content="#196619">
    <link rel="apple-touch-icon" href="/puta/webapp/icons/apple-touch-180.webp" type="image/webp">
    <link rel="icon" type="image/webp" sizes="192x192" href="/puta/webapp/icons/icon-192.webp">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .home-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .logo-section {
            margin-bottom: 24px;
        }
        .logo-section img {
            max-width: 100px;
            height: auto;
            margin-bottom: 16px;
        }
        .home-container h1 {
            color: #196619;
            font-size: 2.5em;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .subtitle {
            color: #176617;
            font-size: 1.1em;
            margin-bottom: 24px;
            font-weight: 500;
        }
        .home-container p {
            color: #176617;
            font-size: 1.1em;
            margin-bottom: 40px;
        }
        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            padding: 16px 32px;
            background: #196619;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-home:hover {
            background: #155a15;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
        }
        .btn-secondary {
            background: #fff;
            color: #196619;
            border: 2px solid #196619;
        }
        .btn-secondary:hover {
            background: #eaffea;
        }
        .home-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #b2e2b2;
        }
        .home-footer a {
            color: #176617;
            font-size: 0.9em;
            text-decoration: none;
        }
        .home-footer a:hover {
            text-decoration: underline;
        }

        /* Mobile responsive styles */
        @media screen and (max-width: 768px) {
            body {
                padding: 12px;
            }

            .home-container {
                padding: 40px 20px;
                border-radius: 12px;
                max-width: 100%;
            }

            .logo-section img {
                max-width: 80px;
            }

            .home-container h1 {
                font-size: 1.8em;
                margin-bottom: 8px;
            }

            .subtitle {
                font-size: 0.95em;
                margin-bottom: 24px;
            }

            .btn-group {
                flex-direction: column;
                gap: 12px;
                width: 100%;
            }

            .btn-home {
                width: 100%;
                padding: 14px 20px;
                font-size: 1em;
                box-sizing: border-box;
            }

            .home-footer {
                margin-top: 32px;
                padding-top: 20px;
            }

            .home-footer a {
                font-size: 0.85em;
            }
        }

        /* Small mobile devices */
        @media screen and (max-width: 480px) {
            .home-container {
                padding: 32px 20px;
            }

            .logo-section img {
                max-width: 70px;
            }

            .home-container h1 {
                font-size: 1.5em;
            }

            .subtitle {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="logo-section" style="display:flex; gap:16px; justify-content:center; align-items:center; flex-wrap:wrap;">
            <picture>
                <source type="image/webp" srcset="uploads/OIP (1)-72.webp 72w, uploads/OIP (1)-150.webp 150w, uploads/OIP (1)-300.webp 300w, uploads/OIP (1)-474.webp 474w">
                <img src="uploads/OIP (1)-150.webp" srcset="uploads/OIP (1)-72.webp 72w, uploads/OIP (1)-150.webp 150w, uploads/OIP (1)-300.webp 300w, uploads/OIP (1)-474.webp 474w" sizes="(max-width:480px) 72px, 100px" alt="Palawan National School Logo" title="Palawan National School" width="100" height="100" loading="eager">
            </picture>
            <picture>
                <source type="image/webp" srcset="uploads/System logo-72.webp 72w, uploads/System logo-150.webp 150w, uploads/System logo-300.webp 300w">
                <source type="image/jpeg" srcset="uploads/System logo.jpg 150w">
                <img src="uploads/System logo-150.webp" srcset="uploads/System logo-72.webp 72w, uploads/System logo-150.webp 150w, uploads/System logo-300.webp 300w" sizes="(max-width:480px) 72px, 100px" alt="QR Attendance System Logo" title="QR Attendance System" width="100" height="100" loading="eager">
            </picture>
        </div>

        <h1>Palawan National School</h1>
        <p class="subtitle">Hybrid QR Code Based Attendance Monitoring System integrating AI-powered validation, smart analytics, and resilient offline-ready access.</p>
        <p style="margin-top: 6px; font-weight: 600; color: #155a15;">No pen? No paper? No problem!! Use TapIn </p>
        <p>Select an option to continue.</p>

        <div class="btn-group">
            <a href="login.php" class="btn-home">Login</a>
            <a href="signup.php" class="btn-home btn-secondary">Sign Up</a>
        </div>

        <div class="home-footer">
            <a href="developer_login.php"> Developer Dashboard</a>
        </div>
    </div>
    <script>
        // Register service worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/puta/sw.js').then(function(reg) {
                    console.log('ServiceWorker registration successful with scope: ', reg.scope);
                }).catch(function(err) {
                    console.warn('ServiceWorker registration failed: ', err);
                });
            });
        }

        // Install prompt handling
        let deferredPrompt;
        const installBtn = document.createElement('button');
        installBtn.id = 'pwaInstallBtn';
        installBtn.style.position = 'fixed';
        installBtn.style.right = '18px';
        installBtn.style.bottom = '18px';
        installBtn.style.background = '#196619';
        installBtn.style.color = '#fff';
        installBtn.style.border = 'none';
        installBtn.style.padding = '10px 14px';
        installBtn.style.borderRadius = '8px';
        installBtn.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
        installBtn.style.fontWeight = '700';
        installBtn.style.display = 'none';
        installBtn.textContent = 'Install App';
        document.body.appendChild(installBtn);

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            deferredPrompt = e;
            // Show the install button
            installBtn.style.display = 'block';
        });

        installBtn.addEventListener('click', async () => {
            installBtn.style.display = 'none';
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log('PWA install outcome:', outcome);
            deferredPrompt = null;
        });

        // Hide button if app already installed
        window.addEventListener('appinstalled', () => {
            installBtn.style.display = 'none';
            console.log('PWA installed');
        });
    </script>
</body>
</html>
