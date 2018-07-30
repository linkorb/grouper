<?php

namespace Grouper;

class Grouper
{
    /**
     * Group rows in an array by columns
     *
     * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
     * and to provide some background information or textual references.
     *
     * @param array $rows Input rows
     * @param array $columns Column names in the rows to group by (i.e. email, phone, etc)
     * @param string $keyColumnName Name of the column that contains the id of the row
     *
     * @return array
     */
    public function group(array $rows, array $columns, string $keyColumnName = 'id')
    {
        // todo
    }
}
