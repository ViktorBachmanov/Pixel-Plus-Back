<?php

abstract class PeriodCollapser
{
  const DAY_OFFSET = 0;
  const DAY_LENGTH = 5;
  const MONTH_OFFSET = 3;
  const MONTH_LENGTH = 2;

  protected float $temperatureSum;
  protected int $temperaturesCount;
  public array $arr;  // array<PeriodData>
  protected string $currentPeriod;
  protected int $stringOffset;
  protected int $substringLength;

  protected function __construct(array $srcDataRows)
  {
    $this->temperatureSum = 0;
    $this->temperaturesCount = 0;

    foreach($srcDataRows as $srcDataRow) {
      $this->processSrcDataRow($srcDataRow);
    }

    $this->pushCollapsedPeriod();   // push the last collapsed period

    $this->seedSlidingFields();
  }

  abstract protected function processSrcDataRow(array $srcDataRowCells);

  protected function reset() {
    $this->temperatureSum = 0;
    $this->temperaturesCount = 0;
  }

  protected function parsePeriod(string $date)
  {
    return substr($date, $this->stringOffset, $this->substringLength);
  }

  protected function pushCollapsedPeriod()
  {
    $avg = $this->temperatureSum / $this->temperaturesCount;
    $this->arr[] = new PeriodData($this->currentPeriod, $avg);
  }

  protected function addTemperatureIntoCollapsedPeriod(string $temperature) {
    $this->temperatureSum += (float)$temperature;
    $this->temperaturesCount++;  
  }

  private function seedSlidingFields() 
  {
    $prevAverage = $this->arr[0]->average;

    for($i = 0; $i < count($this->arr); $i++) {
      $this->arr[$i]->sliding = ($prevAverage + $this->arr[$i]->average) / 2;
      
      $prevAverage = $this->arr[$i]->average;
    }
  }

}


//================================================


class PeriodCollapserBySubstringChange extends PeriodCollapser
{
  public function __construct(array $srcDataRows, int $stringOffset, int $substringLength)
  {
    $this->stringOffset = $stringOffset;
    $this->substringLength = $substringLength;

    $this->currentPeriod = $this->parsePeriod($srcDataRows[0][0]);

    parent::__construct($srcDataRows);

  }

  protected function processSrcDataRow(array $srcDataRowCells)
  {
    $period = $this->parsePeriod($srcDataRowCells[0]);

    if($period !== $this->currentPeriod) {
      $this->pushCollapsedPeriod();

      $this->reset();
      $this->currentPeriod = $period;
    }

    $this->addTemperatureIntoCollapsedPeriod($srcDataRowCells[1]);
  }

}


//================================================


class PeriodCollapserByWeek extends PeriodCollapser
{
  private string $currentDay;
  private int $dayCount;

  public function __construct(array $srcDataRows)
  {
    $this->stringOffset = self::DAY_OFFSET;
    $this->substringLength = self::DAY_LENGTH;
    $this->currentPeriod = '1';  
    $this->currentWeek = 1;
    $this->currentDay = $this->parsePeriod($srcDataRows[0][0]);
    $this->dayCount = 1;

    parent::__construct($srcDataRows);

  }

  protected function processSrcDataRow(array $srcDataRowCells)
  {
    $day = $this->parsePeriod($srcDataRowCells[0]);

    if($day !== $this->currentDay) {
      ++$this->dayCount;
      if($this->dayCount > 7) {
        $this->pushCollapsedPeriod();
        $this->reset();
      }
      $this->currentDay = $day;
    }

    $this->addTemperatureIntoCollapsedPeriod($srcDataRowCells[1]);
  } 

  protected function reset() {
    parent::reset();
    $this->dayCount = 1;
    $this->currentPeriod = (string)++$this->currentWeek;
  }
  
}


/////////////////////////////////////////////////


class PeriodData
{
  public string $period;
  public float $average;
  public float $sliding;

  public function __construct(string $period, float $average)
  {
    $this->period = $period;
    $this->average = $average;
  }
}
