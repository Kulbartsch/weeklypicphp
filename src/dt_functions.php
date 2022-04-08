<?php

  // timestamp functions

  // ['timeframe'] = 'W' = week
  // ['period'] = XX = week numer
  //              Fr Sa So Mo Di Mi Do Fr Sa So Mo Di 
  // WePi Week      |        xx          |               ['picdatestart'] ... ['picdateend']
  // Real Week            |        xx          |         
  // Upload Range   |              xx          |         ['upldatestart'] ... ['uploaddateend']
  // Deadline                                07          ['deadline']
  // Nachzügler                              07   |      ['latesend']
  //
  // ['timeframe'] = 'M' = month
  // ['period'] = XX = month numer
  //              30 01 02 ... 30 31 01 01
  // WePi Month     |      xx       |                    ['picdatestart'] ... ['picdateend']
  // Real Month     |      xx       |     
  // Upload Range   |      xx       |                    ['upldatestart'] ... ['uploaddateend']
  // Deadline                     07                     ['deadline']
  // Nachzügler                   07   |                 ['latesend']  
  

  function requested_timerange($timeframe, $period) {

  }

  function current_timeranges() {
    $today = new DateTime('now');
    $month = $today->format('Y\mm');
    $ret[$month] = get_month_timestamps($today->format('Y'), $today->format('m'));


  }

  function picture_timeranges($tags) {

  }

  function get_month_timestamps($year, $month) {
    $dto = new DateTime();
    $dto->setDate($year, $month, 1);
    $ret['week_start'] = $dto->format('Y-m-d');
    $dto->modify('+6 days');
    $ret['week_end'] = $dto->format('Y-m-d');
    return $ret;
  }

  function get_week_timestamps($year, $week) {
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $ret['week_start'] = $dto->format('Y-m-d');
    $dto->modify('+6 days');
    $ret['week_end'] = $dto->format('Y-m-d');
    return $ret;
  }


?>