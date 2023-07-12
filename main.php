<?php

require_once 'CollapsedData.php';


$file = fopen('weather_statistics.csv', 'rt') or die('Error');

$count = 0;
while($rowCells = fgetcsv($file, null, ";", '"')) {
  // print_r($row);
  // $dateTemperature[$row[0]] = $row[1];
  $dateTemperature[] = [$rowCells[0], $rowCells[1]];

  // if(++$count > 30) {
  //   break;
  // }
}

fclose($file);


echo "///////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////\n";

// print_r($dateTemperature);

// for($i = 0; $i < 20; $i++) {
//   print_r($dateTemperature[$i]); 
// }




$collapsedData = new CollapsedDataByMonth($dateTemperature);

print_r($collapsedData->arr);

