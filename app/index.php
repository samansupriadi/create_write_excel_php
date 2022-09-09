<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 


$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

$filename = 'Penerimaan-31_Juli.xlsx';
$spreadsheet = $reader->load($filename);
$sheetname = $spreadsheet->getSheetNames()[0]; 
//$d=$spreadsheet->getSheet(0)->toArray();

$sheetData = $spreadsheet->getActiveSheet()->toArray();


$i=1;
$jenisExport = ["donasi", "dana_titipan"];
$date = date('d-m-y');
$filename = $sheetname . "_" . "$date" . "_" . $filename;
$filename = str_replace(' ', '_', $filename);
unset($sheetData[0]);
$temp = [];
$newData = [];
foreach ($sheetData as $t) {
	if($t[0] != "" and $t[1] != ""){
		$rawTanggal = strtotime($t[0]);
		$tanggal = date('Y-m-d', $rawTanggal);
		$t[0] = $tanggal;
	}
	//script pertama di jalankan untuk membuat array baru / array nya di isi lalu di skip ke array ke index 1
	if(count($temp) < 1 ){
		$t = [$t[0], $t[1], $t[2], $t[3], $t[4], (int)str_replace(',', '', $t[5]), $t[6], $t[7], $t[8]];
		$temp = $t;
		array_push($newData, $t);
		continue;
	}else{
		//jika tanggal dan kode akun sama, maka itu satu kesatuan dengan jurnal yang sebelumnya
		if($t[0] == $temp[0] and $t[1] == $temp[1]){
			$newData[] = ["", "", $t[2], $t[3], $t[4], (int)str_replace(',', '', $t[5]), $t[6], $t[7]];
		//atau jika tanggal kosong dan kode akun kosong tapi tipe donasi tidak kosong satu kesatuan dengan jurnal sebelumnya
		}elseif($t[0] == "" and $t[1] == "" and $t[2] != ""){
			//$newData[] = $t;
			//$newData[] = [$tanggal, $t[1], $t[2], $t[3], $t[4], (int)str_replace(',', '', $t[5]), $t[6], $t[7]];
			$newData[] = ["", "", $t[2], $t[3], $t[4], (int)str_replace(',', '', $t[5]), $t[6], $t[7]];
		//jika tanggal kode akun dan tipe dana tidak di isi berarti looping berhenti end 
		}elseif($t[0] == "" and $t[1] == "" and $t[2] == ""){
			break;
		}else{
			$temp = $t;
			//$newData[] = $t;
			$newData[] = [$t[0], $t[1], $t[2], $t[3], $t[4], (int)str_replace(',', '', $t[5]), $t[6], $t[7], $t[8]];

 		}
	}

}
// Creates New Spreadsheet 
$spreadsheet = new Spreadsheet(); 
// Retrieve the current active worksheet 
$sheet = $spreadsheet->getActiveSheet(); 
//set column header
//set your own column header
$headerdata = [
		["tanggal", "kode_akun", "tipe", "catatan", "total", "nilai", "keterangan", "jenis_donasi", "data_from"],
		["tanggal", "kode_akun", "tipe", "catatan", "total", "nilai", "keterangan", "tipe_dana", "data_from"]

	];
$column_header= $headerdata[1];
$j=1;
foreach($column_header as $x_value) {
	$sheet->setCellValueByColumnAndRow($j,1,$x_value);
  	$j=$j+1;
}


for($i=0;$i<count($newData);$i++)
{

$jumlah = $jumlah + $newData[$i][5];
//set value for indi cell
$row=$newData[$i];
$j=1;
	foreach($row as $x => $x_value) {
		$sheet->setCellValueByColumnAndRow($j,$i+2,$x_value);
  		$j=$j+1;
	}

}


$writer = new Xlsx($spreadsheet); 

$writer->save($filename); 


?>
