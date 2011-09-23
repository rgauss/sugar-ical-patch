<?php
// adding date and time due fields

$dictionary['ProjectTask']['fields']['date_due'] = array (
'name' => 'date_due',
'vname' => 'LBL_DATE_DUE',
'type' => 'date',
'rel_field' => 'time_due',
'audited' => true
);

$dictionary['ProjectTask']['fields']['time_due'] = array (
'name' => 'time_due',
'vname' => 'LBL_TIME_DUE',
'type' => 'time',
'rel_field' => 'date_due',
'audited' => true
);

?>