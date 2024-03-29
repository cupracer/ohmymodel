<?php

namespace App\Service;

class DatatablesService
{
    protected function getOrderingData(array $sortableColumns, array $requestColumns, array $requestOrders, $defaultDirection = 'asc'): array
    {
        $columns = [];
        $orderColumns = [];

        // requestColumns is provided by Datatables as request query param
        if(is_array($requestColumns)) {
            // We're on interested in the "data" value, which should be a key of the object's jsonSerialize array,
            // so we initialize a fake class instance to get the keys from it.
            // Each request column name is only accepted if it matches one of these keys.
            foreach($requestColumns as $index => $requestColumn) {
                if(in_array($requestColumn['data'], $sortableColumns)) {
                    $columns[$index] = $requestColumn['data'];
                }
            }
        }

        // Sorting is requested by Datatables with one or more column names and order directions.
        // We check if the column names match the validated columns (above)
        // and also check whether the direction is valid.
        foreach($requestOrders as $requestOrder) {
            $orderColumn = [];

            if (array_key_exists('column', $requestOrder)) {
                $orderColumn['num'] = (int)$requestOrder['column'];
                $orderColumn['name'] = array_key_exists($orderColumn['num'], $columns) ?
                    $columns[$orderColumn['num']] : null;
            }

            if (array_key_exists('dir', $requestOrder)) {
                $orderColumn['dir'] = in_array(strtolower($requestOrder['dir']), ['asc', 'desc']) ?
                    strtolower($requestOrder['dir']) : $defaultDirection;
            }

            $orderColumns[] = $orderColumn;
        }

        // Example return:
        // Array
        //(
        //    [0] => Array
        //        (
        //            [num] => 2
        //            [name] => bookingCategory
        //            [dir] => desc
        //        )
        //)

        return $orderColumns;
    }
}