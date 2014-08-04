<?php

$download_url = "http://dq.cde.ca.gov/dataquest/dlfile/dlfile.aspx?cLevel=School&cYear=__DOWNLOAD__&cCat=Enrollment&cPage=filesenr.asp";

$file = "main.html";
$contents = file_get_contents($file);
#print $contents;

preg_match_all("/cYear\=(.*?)\&/x",$contents,$m);
#print_r($m);

foreach ($m[1] as $year) {
	$url = preg_replace("/__DOWNLOAD__/",$year,$download_url);
	print "Downloading $url\n";
	$cmd = "wget -O \"data/$year.txt\" \"$url\"";
	shell_exec($cmd);
}

