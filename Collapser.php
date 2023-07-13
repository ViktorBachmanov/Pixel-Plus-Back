<?php

// Strategy pattern

abstract class Collapser
{
  const FIRST_INDEX = 0;
  const DAY_OFFSET = 0;
  const DAY_LENGTH = 5;
  const MONTH_OFFSET = 3;
  const MONTH_LENGTH = 2;

  protected float $temperatureSum;
  protected int $count;
  protected array $collapsedArray;
  protected string $currentPeriod;
  protected int $stringOffset;
  protected int $substringLength;

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
    return substr($date, $this->stringOffset, $this->substringLength);
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
  public function __construct(array $srcDataRows, array &$collapsedArray, int $stringOffset, int $substringLength)
  {
    $this->stringOffset = $stringOffset;
    $this->substringLength = $substringLength;

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
    $this->substringLength = self::DAY_LENGTH;
    // $this->isLastDayOfYear = false;
    $this->currentPeriod = '1';  
    $this->currentDay = '31';
    $this->dayCount = 1;
    $this->currentWeek = 1;

    parent::__construct($srcDataRows, $collapsedArray);

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

    $this->temperatureSum += (float)$srcDataRowCells[1];
    $this->temperaturesCount++;    
  } 

  protected function reset() {
    parent::reset();
    $this->dayCount = 1;
    $this->currentPeriod = (string)++$this->currentWeek;
  }
  
}