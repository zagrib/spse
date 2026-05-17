<?php

error_reporting(E_ALL);

$baseUrl = "https://spse.inaproc.id";
$pageUrl = $baseUrl . "/malangkota/nontender";
$ajaxUrl = $baseUrl . "/malangkota/dt/pl?tahun=2026";

$cookieFile = __DIR__ . "/cookie.txt";

function curlGet($url, $cookieFile)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}

function curlPost($url, $postData, $cookieFile)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_HTTPHEADER => [
            "X-Requested-With: XMLHttpRequest",
            "Referer: https://spse.inaproc.id/malangkota/nontender"
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}

/*
|--------------------------------------------------------------------------
| STEP 1 - ambil halaman awal
|--------------------------------------------------------------------------
*/

$html = curlGet($pageUrl, $cookieFile);

/*
|--------------------------------------------------------------------------
| STEP 2 - extract token
|--------------------------------------------------------------------------
*/

preg_match("/authenticityToken = '([^']+)'/", $html, $matches);

$token = $matches[1] ?? '';

if (!$token) {
    die("Token tidak ditemukan");
}

/*
|--------------------------------------------------------------------------
| STEP 3 - request data paket
|--------------------------------------------------------------------------
*/

$postData = [
    "draw" => 1,
    "start" => 0,
    "length" => 100,
    "search[value]" => "",
    "search[regex]" => "false",
    "authenticityToken" => $token
];

$json = curlPost($ajaxUrl, $postData, $cookieFile);

$data = json_decode($json, true);

$rows = $data['data'] ?? [];

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LPSE Kota Malang</title>

    <style>
        body{
            font-family: Arial;
            margin:20px;
        }

        table{
            border-collapse: collapse;
            width:100%;
        }

        th, td{
            border:1px solid #ccc;
            padding:8px;
            font-size:14px;
        }

        th{
            background:#f0f0f0;
        }

        tr:nth-child(even){
            background:#fafafa;
        }
    </style>
</head>
<body>

<h2>Data Paket Non Tender LPSE Kota Malang</h2>

<table>
    <tr>
        <th>Kode</th>
        <th>Nama Paket</th>
        <th>Instansi</th>
        <th>Tahapan</th>
        <th>HPS</th>
        <th>Jadwal</th>
    </tr>

    <?php foreach($rows as $row): ?>

        <tr>
            <td><?= htmlspecialchars($row[0]) ?></td>
            <td><?= htmlspecialchars(strip_tags($row[1])) ?></td>
            <td><?= htmlspecialchars($row[2]) ?></td>
            <td><?= htmlspecialchars($row[3]) ?></td>
            <td><?= htmlspecialchars($row[4]) ?></td>
            <td><?= htmlspecialchars($row[5]) ?></td>
        </tr>

    <?php endforeach; ?>

</table>

</body>
</html>
