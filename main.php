<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once 'CollapsedData.php';


$file = fopen('weather_statistics.csv', 'rt') or die('Error');

$count = 0;
while($rowCells = fgetcsv($file, null, ";", '"')) {
  // print_r($rowCells);
  // $dateTemperature[$row[0]] = $row[1];
  $dateTemperature[] = [$rowCells[0], $rowCells[1]];

  // if(++$count > 30) {
  //   break;
  // }
}

fclose($file);

$reversedArr = array_reverse($dateTemperature);

array_pop($reversedArr);  // remove header of source data

// print_r($reversedArr);


$period = $_GET['period'];

switch($period) {
  case 'day':
    $collapsedData = new CollapsedDataByDay($reversedArr);
    break;
  case 'week':
    $collapsedData = new CollapsedDataByWeek($reversedArr);
    break;
  case 'month':
    $collapsedData = new CollapsedDataByMonth($reversedArr);
    break;
}

// echo "///////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////////////////////////////\n";

// print_r($dateTemperature);

// for($i = 0; $i < 20; $i++) {
//   print_r($dateTemperature[$i]); 
// }

$prevAverage = $collapsedData->arr[0]->average;

for($i = 0; $i < count($collapsedData->arr); $i++) {
  $collapsedData->arr[$i]->sliding = ($prevAverage + $collapsedData->arr[$i]->average) / 2;
  
  $prevAverage = $collapsedData->arr[$i]->average;
}



// print_r($collapsedData->arr);

echo json_encode($collapsedData->arr);

