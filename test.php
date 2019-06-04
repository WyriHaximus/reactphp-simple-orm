<?php

use Plasma\SQL\QueryBuilder;

require 'vendor/autoload.php';

$query = QueryBuilder::create()->select('screenshots.*')->select('tags.*')->from('screenshots', 's')->
         innerJoin('tags', 't')->on('t.screenshot_id', 's.id')->where('s.id', '=', 1);

var_export([
    $query->getQuery(),
    $query->getParameters(),
]);
