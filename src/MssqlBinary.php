<?php

declare(strict_types=1);

namespace Sitmpcz;

class MssqlBinary
{
    /** @var resource */
    private $link = null;
    private bool $debug = false;

    /**
     * MssqlBinary constructor.
     * @param resource $resource
     */
    function __construct($resource)
    {
        $this->link = $resource;
    }

    /**
     * @param string $tsql
     * @param resource $fileStream
     * @param array $params
     * @return int|null
     * @throws MssqlBinaryException
     */
    function SendBinaryStream(string $tsql, $fileStream, array $params = []): ?int
    {
        $retval = null;
        $tsql .= "; SELECT SCOPE_IDENTITY() AS MyID";
        $options = array("SendStreamParamsAtExec" => 0);
        $streamParam = array(&$fileStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'));

        /* Add stream parameter at the beginning. */
        array_unshift($params, $streamParam);

        $stmt = sqlsrv_prepare($this->link, $tsql, $params, $options);
        if ($stmt === false) {
            throw new MssqlBinaryException(sqlsrv_errors());
        }
        if (sqlsrv_execute($stmt) === false) {
            throw new MssqlBinaryException(sqlsrv_errors());
        }
        /* Stream data to the database in chunks of 8k. */
        while ($success = sqlsrv_send_stream_data($stmt)) {
        }

        if (sqlsrv_next_result($stmt) != false) {
            //pokud by výsledkem mělo být desetinné číslo, pak parametr get_as_type musí být SQLSRV_PHPTYPE_FLOAT
            if (sqlsrv_fetch($stmt) != false) $retval = sqlsrv_get_field($stmt, 0,SQLSRV_PHPTYPE_INT);
        }
        return $retval;
    }

    /**
     * @param string $tsql
     * @param resource $fileStream
     * @param array $params
     * @return void
     * @throws MssqlBinaryException
     */
    function UpdateBinaryStream(string $tsql, $fileStream, array $params = []): void
    {
        $options = array("SendStreamParamsAtExec" => 0);
        $streamParam = array(&$fileStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'));

        /* Add stream parameter at the beginning. */
        array_unshift($params, $streamParam);

        $stmt = sqlsrv_prepare($this->link, $tsql, $params, $options);
        if ($stmt === false) {
            throw new MssqlBinaryException(sqlsrv_errors());
        }
        if (sqlsrv_execute($stmt) === false) {
            throw new MssqlBinaryException(sqlsrv_errors());
        }
        /* Stream data to the database in chunks of 8k. */
        while ($success = sqlsrv_send_stream_data($stmt)) {
        }
    }

}
