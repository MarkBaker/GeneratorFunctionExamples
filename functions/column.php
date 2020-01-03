<?php

function column(Traversable $filter, $columnKey, $indexKey = null) {
    $numericKey = 0;
    foreach ($filter as $value) {
        $key = ($indexKey !== null) ? $value->$indexKey : $numericKey;
        yield $key => $value->$columnKey;
        ++$numericKey;
    }
}
