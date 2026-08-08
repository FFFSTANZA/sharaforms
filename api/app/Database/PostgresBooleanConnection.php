<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection;

class PostgresBooleanConnection extends PostgresConnection
{
    /**
     * PostgreSQL is strict about parameter types. With PDO::ATTR_EMULATE_PREPARES
     * enabled (required for pgbouncer transaction pooling), PDO interpolates
     * bindings directly into the SQL string: PHP booleans and integers become
     * unquoted typed literals (0/1/427), which PostgreSQL rejects against
     * boolean ("column is of type boolean but expression is of type integer")
     * and character varying ("operator does not exist") columns.
     *
     * Convert every scalar binding to a quoted string literal ('true'/'false',
     * '427', ...). Quoted literals have "unknown" type, so PostgreSQL coerces
     * them to whatever the column or operator needs.
     */
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            } elseif (is_int($value) || is_float($value)) {
                $bindings[$key] = (string) $value;
            }
        }

        return parent::prepareBindings($bindings);
    }
}
