<?php
/*
This code was scraped together by Nick G and Wade S during the GeoHuntsville Hackathon.  Need to still address:
1) web directory to local directory mapping
2) GeoJSON format for images embedded
3) Modificaitons to the GeoJSON import in GeoQ
4) Comments and attribution (esp for GetGPps function and gps2Num function)
5) Still need to figure out the right places to put the data in the GeoJSON to expose it as an image (thumbnail) and clickable URL in GeoQ
*/

// Set this variable to the direcoty that serves the images
$webdirectory = 'http://rcal.ist.psu.edu/campusconstruction/';

function getGps($exifCoord, $hemi) {
    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;
    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;
    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}
function gps2Num($coordPart) {
    $parts = explode('/', $coordPart);
    if (count($parts) <= 0)
        return 0;
    if (count($parts) == 1)
        return $parts[0];
    return floatval($parts[0]) / floatval($parts[1]);
}
function reportGps ($exif) {
	$lon = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
	$lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
	$datetime = $exif["DateTimeOriginal"];
	return array ($lat,$lon,$datetime);
}
// Main
/*
MR: Added GeoQ parameters below:
*/
$geojson = array(
	'features'  => array(), 
	'id' => 1000,
	'type' => "FeatureCollection",
	'format' => "",
	'url' => "",
	'subdomains' => "",
	'layer' => "",
	'transparent' => True,
	'layerParams' => "{}",
	'dynamicParams' => "{}",
	'refreshrate' => 0,
	'token' => "",
	'attribution' => "",
	'spatialReference' => "",
	'layerParsingFunction' => "",
	'enableIdentify' => False,
	'rootField' => "",
	'infoFormat' => "",
	'fieldsToShow' => "",
	'description' => "",
	'downloadableLink' => null,
	'layer_info_link' => null,
	'styles' => ""
);
$directory = './';
$scanned_directory = array_diff(scandir($directory), array('..', '.', '.images.php.swp', 'images.php','.php'));
foreach ($scanned_directory as $filename) {
	
	if ((strpos($filename, '.jpg') !== false ) OR (strpos($filename, '.JPG') !== false )) {
		$exifforfile = exif_read_data($filename);
		list ($lat,$lon,$datetime) = reportGps ($exifforfile);
		$feature = array(
			'type' => 'Feature',
			'properties' => array (
				'description' => $webdirectory.$filename
			),
			'geometry' => array (
				'type' => 'Point',
				'coordinates' => (array($lon,$lat)
			)
		));
		array_push($geojson['features'],$feature);
	}
}
header('Content-type: application/json');
echo json_encode($geojson, JSON_NUMERIC_CHECK);
?>
