<?php
set_time_limit(0);
function getlahir($nik, $kel)
{
    //substring
    $hasil = substr($nik, 6, -4);
    $split = str_split($hasil, 2);
    $kelamin = "Pria";
    if ($kel == 'P') {
        $split[0] = $split[0] - 40;
        if (strlen($split[0]) == 1 ) {
            $split[0] = '0' . $split[0];
        }
        $kelamin = "Wanita";
    }
    $result = implode('-', $split);
    return array($result, $kelamin);
}
function curl($url)
{
    //inisialisasi
    $init = curl_init();
    //nge-set url
    curl_setopt($init, CURLOPT_URL, $url);
    //nge-set user agent
    curl_setopt($init, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    //mengembalikan hasil menjadi string
    curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
    //eksekusi!! var $isi berisi string output
    $isi = curl_exec($init);
    //tutup curl untuk mengurangi beban sistem
    curl_close($init);
    return $isi;
}
$nasional = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/listNasional.json'), TRUE);
foreach ($nasional['aaData'] as $z => $a) {
	$namaWilayah = $a['namaWilayah'];
    $provinsi = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/listDps.json'), TRUE);
    if (!file_exists( 'priv/' . $namaWilayah )) {
                                mkdir( 'priv/' . $namaWilayah, 0777, true);
    }
    foreach ($provinsi['aaData'] as $y => $b) {
    	$namaKabKota = $b['namaKabKota'];
        $kabkota = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/listDps.json'), TRUE);
        if (!file_exists( 'priv/' . $namaWilayah . '/' . $namaKabKota )) {
                                mkdir( 'priv/' . $namaWilayah . '/' . $namaKabKota, 0777, true);
        }
        foreach ($kabkota['aaData'] as $x => $c) {
        	$namaKecamatan = $c['namaKecamatan'];
        	$kecamatan = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/listDps.json'), TRUE);
            if (!file_exists( 'priv/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan )) {
                                mkdir( 'priv/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan, 0777, true);
            }
        	foreach ($kecamatan['aaData'] as $w => $d) {
        		$namaKelurahan = $d['namaKelurahan'];
        		$kelurahan = json_decode(curl('https://infopemilu.kpu.go.id/pilkada2018/pemilih/dpt/1/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/' . $namaKelurahan . '/listDps.json'), TRUE);
        		foreach ($kelurahan['aaData'] as $v => $e) {
        			$nomortps = $e['tps'];
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
                            $path = 'priv/' . $namaWilayah . '/' . $namaKabKota . '/' . $namaKecamatan . '/' . $namaKelurahan . '.json';
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