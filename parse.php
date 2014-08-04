<?php
require_once("lib.php");
require_once "PHPExcel/Classes/PHPExcel.php";
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

$all_data = array();
foreach ($years as $year=>$filename) {
	$fullname = 'source/data/' . $filename . '.txt';
	$all_data[$year] = parseFileSum($year,$fullname);
}

$formatted_data = array();

foreach ($all_data as $year => $year_data) {
	$grades = array_keys($year_data[0]);
	$stats = $year_data[1];	

	foreach ($stats as $county => $county_data) {
		foreach ($county_data as $district => $district_data) {
			foreach ($district_data as $school => $school_data) {
				$new_school_data = array();
				foreach ($school_data as $k=>$v) {
					$new_school_data[$grades[$k]] = $v;
				}
				$formatted_data[$county][$district][$school][$year] = $new_school_data;
			}
		}
	}
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

foreach ($formatted_data as $county => $county_data) {
	foreach ($county_data as $district => $district_data) {
		generateFile($district,$district_data);
		#exit;
	}
}

