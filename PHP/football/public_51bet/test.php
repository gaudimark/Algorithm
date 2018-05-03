<?php
$data = array(
    array(
        'target' =>'home',
        'item'   =>'',
        'money'  => 500,
    )
);
die(base64_encode(json_encode($data)));