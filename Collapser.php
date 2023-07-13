<?php

// Strategy pattern

abstract class Collapser
{
  const FIRST_INDEX = 1;
  const DAY_OFFSET = 0;
  const MONTH_OFFSET = 3;

  protected float $temperatureSum;
  protected int $count;
  protected array $collapsedArray;
  protected string $currentPeriod;
  protected int $stringOffset;

  protected function __construct(array $srcDataRows, array &$collapsedArray)
  {
    $this->collapsedArray = &$collapsedArray;

    // $this->reset();
    $this->temperatureSum = 0;
    $this->temperaturesCount = 0;

    for($i = self::FIRST_INDEX; $i < count($srcDataRows); $i++) {
      $this->processSrcDataRow($srcDataRows[$i]);
    }

    $this->pushCollapsedPeriod();   // push the last collapsed period
  }

  abstract protected function processSrcDataRow(array $srcDataRowCells);

  protected function reset() {
    $this->temperatureSum = 0;
    $this->temperaturesCount = 0;
  }

  protected function parsePeriod(string $date)
  {
    return substr($date, $this->stringOffset, 2);
  }

  protected function pushCollapsedPeriod()
  {
    $avg = $this->temperatureSum / $this->temperaturesCount;
    $this->collapsedArray[] = new PeriodData($this->currentPeriod, $avg);
  }

}


//================================================


class CollapserBySubstringChange extends Collapser
{
  public function __construct(array $srcDataRows, array &$collapsedArray, int $stringOffset)
  {
    $this->stringOffset = $stringOffset;

    $this->currentPeriod = $this->parsePeriod($srcDataRows[self::FIRST_INDEX][0]);

    parent::__construct($srcDataRows, $collapsedArray);

  }

  protected function processSrcDataRow(array $srcDataRowCells)
  {
    $period = $this->parsePeriod($srcDataRowCells[0]);

    if($period !== $this->currentPeriod) {
      $this->pushCollapsedPeriod();

      $this->reset();
      $this->currentPeriod = $period;
    }

    $this->temperatureSum += (float)$srcDataRowCells[1];
    $this->temperaturesCount++;
  }

}


//================================================


class CollapserByWeek extends Collapser
{
  private bool $isLastDayOfYear;
  private string $currentDay;
  private int $dayCount;

  public function __construct(array $srcDataRows, array &$collapsedArray)
  {
    $this->stringOffset = self::DAY_OFFSET;
    $this->isLastDayOfYear = true;
    $this->currentPeriod = '53';  // В 2021г было 52 полных недели + 1 день
    $this->currentDay = '31';
    $this->dayCount = 1;
    $this->currentWeek = 53;

    parent::__construct($srcDataRows, $collapsedArray);

  }

  protected function processSrcDataRow(array $srcDataRowCells)
  {
    $day = $this->parsePeriod($srcDataRowCells[0]);

    if($day !== $this->currentDay) 
    {
      if($this->isLastDayOfYear) {
        $this->isLastDayOfYear = false;

        $this->pushCollapsedPeriod();
        $this->reset(); 
      }
      else if(++$this->dayCount > 7) {
        $this->pushCollapsedPeriod();
        $this->reset();
      }
      $this->currentDay = $day;
    }

    $this->temperatureSum += (float)$srcDataRowCells[1];
    $this->temperaturesCount++;    
  } 

  protected function reset() {
    parent::reset();
    $this->dayCount = 1;
    $this->currentPeriod = (string)--$this->currentWeek;
  }
  
}