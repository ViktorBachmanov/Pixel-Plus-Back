<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once 'PeriodCollapser.php';


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
    $collapsedData = new PeriodCollapserBySubstringChange($dateTemperature, PeriodCollapser::DAY_OFFSET, PeriodCollapser::DAY_LENGTH); 
    break;
  case 'week':
    $collapsedData = new PeriodCollapserByWeek($dateTemperature);  
    break;
  case 'month':
    $collapsedData = new PeriodCollapserBySubstringChange($dateTemperature, PeriodCollapser::MONTH_OFFSET, PeriodCollapser::MONTH_LENGTH);
    break;
}


$prevAverage = $collapsedData->arr[0]->average;

for($i = 0; $i < count($collapsedData->arr); $i++) {
  $collapsedData->arr[$i]->sliding = ($prevAverage + $collapsedData->arr[$i]->average) / 2;
  
  $prevAverage = $collapsedData->arr[$i]->average;
}


echo json_encode($collapsedData->arr);