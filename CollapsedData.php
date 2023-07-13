<?php

require_once 'Collapser.php';


abstract class CollapsedData
{
  public array $arr; // array<PeriodData>

  public function __construct()
  {   
    $this->arr = [];
  } 
}


//===============================================


class CollapsedDataByDay extends CollapsedData 
{
  public function __construct(array $srcDataRows)
  {
    parent::__construct();

    new CollapserBySubstringChange($srcDataRows, $this->arr, Collapser::DAY_OFFSET, Collapser::DAY_LENGTH);    
  }
  
}


//===============================================


class CollapsedDataByMonth extends CollapsedData 
{
  public function __construct(array $srcDataRows)
  {
    parent::__construct();

    new CollapserBySubstringChange($srcDataRows, $this->arr, Collapser::MONTH_OFFSET, Collapser::MONTH_LENGTH);    
  }
  
}


//===============================================


class CollapsedDataByWeek extends CollapsedData 
{
  public function __construct(array $srcDataRows)
  {
    parent::__construct();

    new CollapserByWeek($srcDataRows, $this->arr);    
  }

  protected function processSrcDataRow(array $srcDataRowCells)
  {
    print_r($srcDataRowCells) ;
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


