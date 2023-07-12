<?php

// Strategy pattern

abstract class Collapser
{
  const FIRST_INDEX = 1;

  protected float $temperatureSum;
  protected int $count;
  protected array $collapsedArray;
  protected string $currentPeriod;

  protected function __construct(array $srcDataRows, array &$collapsedArray)
  {
    $this->collapsedArray = &$collapsedArray;

    $this->reset();

    for($i = self::FIRST_INDEX; $i < count($srcDataRows); $i++) {
      $this->processSrcDataRow($srcDataRows[$i]);
    }

    $this->pushCollapsedPeriod();   // push the last collapsed period
  }

  abstract protected function processSrcDataRow(array $srcDataRowCells);

  protected function reset() {
    $this->temperatureSum = 0;
    $this->count = 0;
  }

  protected function pushCollapsedPeriod()
  {
    $avg = $this->temperatureSum / $this->count;
    $this->collapsedArray[] = new PeriodData($this->currentPeriod, $avg);
  }

}


//================================================


class CollapserByStringChange extends Collapser
{
  private int $stringOffset;

  public function __construct(array $srcDataRows, array &$collapsedArray, int $stringOffset)
  {
    $this->stringOffset = $stringOffset;

    $this->currentPeriod = $this->parsePeriod($srcDataRows[self::FIRST_INDEX][0]);

    parent::__construct($srcDataRows, $collapsedArray);

  }

  private function parsePeriod(string $date)
  {
    return substr($date, $this->stringOffset, 2);
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
    $this->count++;
  }

}


//================================================


class CollapserByCount extends Collapser
{
  protected function processSrcDataRow(array $srcDataRowCells)
  {
    
  } 
  
}