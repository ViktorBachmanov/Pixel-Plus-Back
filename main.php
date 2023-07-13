<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once 'Collapser.php';


$file = fopen('weather_statistics.csv', 'rt') or die('Error');

while($rowCells = fgetcsv($file, null, ";", '"')) {
  $dateTemperature[] = [$rowCells[0], $rowCells[1]];
}

fclose($file);

$dateTemperature = array_reverse($dateTemperature);

array_pop($dateTemperature);  // remove header of source data


$period = $_GET['period'];

switch($period) {
  case 'day':
    $collapsedData = new CollapserBySubstringChange($dateTemperature, Collapser::DAY_OFFSET, Collapser::DAY_LENGTH); 
    break;
  case 'week':
    $collapsedData = new CollapserByWeek($dateTemperature);  
    break;
  case 'month':
    $collapsedData = new CollapserBySubstringChange($dateTemperature, Collapser::MONTH_OFFSET, Collapser::MONTH_LENGTH);
    break;
}


$prevAverage = $collapsedData->arr[0]->average;

for($i = 0; $i < count($collapsedData->arr); $i++) {
  $collapsedData->arr[$i]->sliding = ($prevAverage + $collapsedData->arr[$i]->average) / 2;
  
  $prevAverage = $collapsedData->arr[$i]->average;
}


echo json_encode($collapsedData->arr);