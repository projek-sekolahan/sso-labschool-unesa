<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            display: flex;
            padding : 24 px 48 px;
            flex-direction: column;
            align-items: center;
            gap : 10 px;
            align-self: stretch;
            background-image: linear-gradient(276 deg, #3082D4 0.31 %, #47B7E8 100 %);
            background-color: transparent;
        }
        .header h1 {
            color: #FFF;
            text-align: center;
            font-family: "Futura Md BT";
            font-size: 22px;
            font-style: normal;
            font-weight: 400;
            line-height: 100%;
        }
        .header img {
            width: 100%;
            height: auto;
            display: block;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #333;
        }
        .content p {
            color: #555;
            line-height: 1.6;
        }
        .verification-code {
            display: block;
            background-color: #1976D2;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: #fff;
            font-size: 14px;
        }
        .footer a {
            color: #ff6f61;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>SMA Labschool 1 Unesa<br>Surabaya</h1>
            <!-- <img src="https://devop-sso.smalabschoolunesa1.sch.id/assets/images/Top.webp" alt="top-header" style="width: 100%; height: auto;"> -->
        </div>
        <div class="content">
            <h2 id="identity">Hai <?=$identity?>,</h2>
            <p>Jangan panik! Kamu masih bisa kembali masuk ke akun kamu. <br>Kami akan bantu kamu reset password dengan cepat dan mudah.</p>
            <p>Kode Verifikasi Kamu:</p>
            <div class="verification-code" id="activation"><strong><?=$activation?></strong></div>
            <p>Masukkan kode ini di halaman verifikasi untuk menyelesaikan reset password. Simple, kan?</p>
            <p>Tips Memilih Password:</p>
            <ul>
                <li>Gunakan kombinasi huruf, angka, dan simbol.</li>
                <li>Jangan pakai password yang gampang ditebak, seperti "123456" atau "password".</li>
                <li>Pastikan kamu mengingat password baru kamu atau catat di tempat yang aman.</li>
            </ul>
            <p>Butuh Bantuan? Kalau ada pertanyaan atau butuh bantuan, langsung saja hubungi kami di <a href="mailto:smalabsunesa@gmail.com">smalabsunesa@gmail.com</a> atau DM kami di media sosial. Kami siap membantu!</p>
            <p>Terima kasih sudah bergabung dengan kami. Kami nggak sabar melihat kamu mulai petualangan baru di SmartApps!</p>
        </div>
        <div class="footer">
            <p>Sampai jumpa di dalam, <br><b>SmartApps</b><br>Support Team!!!</p>
            <p>© <?=date('Y')?> SmartApps. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
