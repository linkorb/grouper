<?php

namespace Grouper;

class Grouper
{
    /**
     * Group rows in an array by columns.
     *
     * Rows are grouped together if they have the same values in any of the
     * specified columns.
     *
     * @param array $rows Input rows
     * @param array $columns Column names in the rows to group by (i.e. email, phone, etc)
     * @param string $keyColumnName Name of the column that contains the id of the row
     *
     * @return array
     */
    public function group(array $rows, array $columns, string $keyColumnName = 'id')
    {
        if (empty($rows)) {
            return [];
        }
        if (empty($columns)) {
            throw new \InvalidArgumentException('The columns argument cannot be an empty list.');
        }

        $keyedRows = [];
        \array_map(
            function ($r) use ($keyColumnName, &$keyedRows) {
                if (!\is_array($r)) {
                    throw new \UnexpectedValueException(
                        'All rows must be an associative array. There is at least one row which is not.'
                    );
                }
                if (!isset($r[$keyColumnName])) {
                    throw new \UnexpectedValueException(
                        "All rows must have an entry in the \"{$keyColumnName}\" column. There is at least one row which has not."
                    );
                }
                $keyedRows[$r[$keyColumnName]] = $r;
            },
            $rows
        );

        // create groupings for each of the columns based on exactly matching values
        $groupings = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $value = $row[$column];
                $groupings[$column][$value][] = $row[$keyColumnName];
            }
        }

        // populate groups with rows according to the groupings of the first column
        $groups = [];
        foreach (\array_shift($groupings) as $columnGrouping) {
            $group = new Group();
            foreach ($columnGrouping as $rowId) {
                $group->rows[$rowId] = $keyedRows[$rowId];
                $groups[$rowId] = $group;
            }
        }

        // populate groups with rows according to the remaining groupings
        // but adding rows to exsting groups where applicable
        foreach ($groupings as $column) {
            foreach ($column as $columnGrouping) {
                // locate existing groups which hold rows of this grouping
                $existingGroups = [];
                foreach ($columnGrouping as $rowId) {
                    if (!isset($groups[$rowId])) {
                        continue;
                    }
                    $existingGroups[] = $groups[$rowId];
                }
                $group = null;
                if (\sizeof($existingGroups)) {
                    $group = \array_shift($existingGroups);
                }
                // this grouping is a signal to link multiple groups together...
                if (\sizeof($existingGroups)) {
                    foreach ($existingGroups as $redundantGroup) {
                        foreach ($redundantGroup->rows as $rowId => $row) {
                            $group->rows[$rowId] = $row;
                            $groups[$rowId] = $group;
                        }
                    }
                }
                if (!$group) {
                    $group = new Group();
                }
                // now add the rows to the group as per this grouping
                foreach ($columnGrouping as $rowId) {
                    if (isset($group->rows[$rowId])) {
                        continue;
                    }
                    $group->rows[$rowId] = $keyedRows[$rowId];
                    \assert(!isset($groups[$rowId]), 'We are not clobbering an existing group.');
                    $groups[$rowId] = $group;
                }
            }
        }

        // return a list grouped rows
        $groupedRows = [];
        $seenGroups = [];
        foreach ($groups as $group) {
            $groupId = \spl_object_hash($group);
            if (\array_key_exists($groupId, $seenGroups)) {
                continue;
            }
            $seenGroups[$groupId] = true;
            $groupedRows[] = \array_values($group->rows);
        }

        return $groupedRows;
    }
}
