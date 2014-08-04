<?php

ini_set('memory_limit','556M');

date_default_timezone_set('America/Los_Angeles');

$grade_match = array(
KDGN => " Kindergarten",
GR_1 => "First Grade",
GR_2 => "Second Grade",
GR_3 => "Third Grade",
GR_4 => "Fourth Grade",
GR_5 => "Fifth Grade",
GR_6 => "Sixth Grade",
GR_7 => "Seventh Grade",
GR_8 => "Eighth Grade",
GR_9 => "Ninth Grade",
GR_10 => "Tenth Grade",
GR_11 => "Eleventh Grade",
GR_12 => "Twelfth Grade",
UNGR_ELM => "Ungraded Elementry",
UNGR_SEC => "Ungraded Secondary",
ENR_TOTAL => "Enrolled Total (exclude Adults)",
ADULT => "Adults",
);

$format_years = array(
	"2013" => "2013",
	"2012" =>  "2013",
	"2011" => "2013",
	"2010" => "2013",
	"2009" => "2013",
	"2008" => "2008",
	"2007" => "2008",
	"2006" => "2008",
	"2005" => "2008",
	"2004" => "2008",
	"2003" => "2008",
	"2002" => "2008",
	"2001" => "2008",
	"2000" => "2008",
	"1999" => "2008",
	"1998" => "2008",
	"1997" => "1997",
	"1996" => "1997",
	"1995" => "1997",
	"1994" => "1997",
	"1993" => "1997",
);

$ethnic_2013 = array(
	"0" => "Not reported",
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White",
	"9" => "Two or More Races",
);

$ethnic_2008 = array(
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White, not Hispanic",
	"8" => "Multiple or No Response",
);

$ethnic_1997 = array(
	"1" => "American Indian or Alaska Native",
	"2" => "Asian",
	"3" => "Pacific Islander",
	"4" => "Filipino",
	"5" => "Hispanic or Latino",
	"6" => "African American",
	"7" => "White",
);

$years = array(
"2013" => "2013-14",
"2012" => "2012-13",
"2011" => "2011-12",
"2010" => "2010-11",
"2009" => "2009-10",
"2008" => "2008-09",
"2007" => "2007-08",
"2006" => "0607",
"2005" => "0506",
"2004" => "0405",
"2003" => "0304",
"2002" => "0203",
"2001" => "0102",
"2000" => "0001",
"1999" => "9900",
"1998" => "9899",
"1997" => "9798",
"1996" => "9697",
"1995" => "9596",
"1994" => "9495",
"1993" => "9394",
);

function parseFileSum($year,$filename) {
        global $format_years, $ethnic_2013, $ethnic_2008;

        $contents = file($filename);
        #$contents = array_slice($contents,0,3);

        $headers = $contents[0];
        unset($contents[0]);
        $header_fields = explode("\t",$headers);

        $data = array();

        if ($format_years[$year] == '2013') {
                $grade_fields = array_slice($header_fields,6);
                #print_r($header_fields);

                foreach ($contents as $line) {
                        $line = trim($line);
                        $bits = explode("\t",$line);

                        $grades = array_slice($bits,6);

                        $grade_array = array();
                        foreach($grades as $index=>$student_number) {
                                $grade_array[trim($grade_fields[$index])] = $student_number;
                        }

                        if (is_array($data[$bits[1]][$bits[2]][$bits[3]])) {
                                $add = function($a, $b) { return $a + $b; };
                                $summedArray = array_map($add, $data[$bits[1]][$bits[2]][$bits[3]], $grade_array);

                                $data[$bits[1]][$bits[2]][$bits[3]] = $summedArray;
                        }
                        else {
                                $data[$bits[1]][$bits[2]][$bits[3]] = $grade_array;
                        }
                }
        }

        return array($grade_array,$data);
}

function generateFile($outputFileName,$data) {
	global $grade_match;

        $outputFileName= preg_replace("/[^\s\da-z]/i","",$outputFileName);
        print "Generating $outputFileName\n";
        #continue;

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $sheet_ctr = 0;

        foreach ($data as $school => $year_data) {
                $sheet_name = preg_replace("/[^\s\da-z]/i","",substr($school,0,30));
                if ($sheet_ctr == 0) {
                        // Create a first sheet, representing a school
                        $objPHPExcel->setActiveSheetIndex();

                        // Rename sheet
                        $objPHPExcel->getActiveSheet()->setTitle($sheet_name);
                }
                else {
                        // Create a new worksheet, after the default sheet
                        $objPHPExcel->createSheet();
                        $objPHPExcel->setActiveSheetIndex($sheet_ctr);
                        // Rename sheet
                        $objPHPExcel->getActiveSheet()->setTitle($sheet_name);
                }

		$alphas = range('B', 'Z');
		$row_ctr = 0;
		foreach ($year_data as $key=>$school_data) {

			if($row_ctr == 0) {
		                $keys = array_keys($school_data);
        		        foreach ($keys as $k=>$v) {
					$grade_name = $grade_match[$v];
                		        $num = $k+2;
                        		$cell_name = "A$num";
		                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $grade_name);
        		        }
			}

			$objPHPExcel->getActiveSheet()->setCellValue($alphas[$row_ctr] . "1", $key);
			
	                $values = array_values($school_data);
        	        foreach ($values as $k=>$v) {
                	        $num = $k+2;
                        	$cell_name = $alphas[$row_ctr] . "$num";
	                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $v);
        	        }

			$row_ctr++;
		}

                $sheet_ctr++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('output/'. $outputFileName . '.xls');
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);

}
