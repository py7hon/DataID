<?php
// set limit to zero
set_time_limit(0);
// set direktori/folder penyimpanan
$dir = "priv/";

function getlahir($nik, $kel)
{
    $rangetgl = substr($nik, 6, -4);
    $tglsplitted = str_split($rangetgl, 2);
    // set $kelamin = Pria
    $kelamin = "Pria";
    // tanggal lahir dikurangi 40 jika $kel == P
    if ($kel == 'P') {
        $tglsplitted[0] = $tglsplitted[0] - 40;
        if (strlen($tglsplitted[0]) == 1 ) {
            $tglsplitted[0] = '0' . $tglsplitted[0];
        }
        // set $kelamin = Wanita
        $kelamin = "Wanita";
    }
    // penggabungan array $tglsplitted dengan menambahkan pemisah
    $tgllahir = implode('-', $tglsplitted);
    return array($tgllahir, $kelamin);
}

function curl($url)
{
    $init = curl_init();
    curl_setopt($init, CURLOPT_URL, $url);
    curl_setopt($init, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
    $isi = curl_exec($init);
    curl_close($init);
    return $isi;
}

// ambil data Provinsi
$nasional = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/listNasional.json'), TRUE);

foreach ($nasional['aaData'] as $z => $a) {
	$namaWilayah = $a['namaWilayah'];

    // ambil data Kota/Kab
    $provinsi = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/listDps.json'), TRUE);
    if (!file_exists( $dir . $namaWilayah )) {
                                mkdir( $dir . $namaWilayah, 0777, true);
    }
    foreach ($provinsi['aaData'] as $y => $b) {
    	$namaKabKota = $b['namaKabKota'];

        // ambil data Kecamatan
        $kabkota = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/listDps.json'), TRUE);
        if (!file_exists( $dir . $namaWilayah . '/' . $namaKabKota )) {
                                mkdir( $dir . $namaWilayah . '/' . $namaKabKota, 0777, true);
        }
        foreach ($kabkota['aaData'] as $x => $c) {
        	$namaKecamatan = $c['namaKecamatan'];

            // ambil data Kelurahan
        	$kecamatan = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/listDps.json'), TRUE);
            if (!file_exists( $dir . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan )) {
                                mkdir( $dir . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan, 0777, true);
            }
        	foreach ($kecamatan['aaData'] as $w => $d) {
        		$namaKelurahan = $d['namaKelurahan'];

                // ambil data TPS
        		$kelurahan = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/' . $namaKelurahan . '/listDps.json'), TRUE);
        		foreach ($kelurahan['aaData'] as $v => $e) {
        			$nomortps = $e['tps'];

                    // ambil data Pemilih
        			$tps = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/' . $namaKelurahan . '/' . $nomortps . '/listDps.json'), TRUE);
        				foreach ($tps['aaData'] as $u => $f) {
                            list ($lahir, $kelamin) = getlahir($f['nik'], $f['jenisKelamin']);
                            $isine = array('NIK' => $f['nik'],
                            'nama' => $f['nama'],
                            'jeniskelamin' => $kelamin,
                            'tanggallahir' => $lahir,
                            'tempatlahir' => $f['tempatLahir'],
                            'kelurahan' => $namaKelurahan,
                            'kecamatan' => $namaKecamatan,
                            'kabkota' => $namaKabKota,
                            'provinsi' => $namaWilayah);
                            $path = $dir . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/' . $namaKelurahan . '.json';
                            $fh = fopen($path, 'a');
                            fwrite($fh, json_encode($isine, JSON_PRETTY_PRINT));
                            fclose($fh);
    				}
    			}
    		}
        }
    }
}
?>