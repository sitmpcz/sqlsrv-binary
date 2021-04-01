<?php

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

    function SendBinaryStream(string $tsql, $fileStream,array $params = [],bool $handleErrors = true): ?int
    {
        $retval = null;
        $tsql .= "; SELECT SCOPE_IDENTITY() AS MyID";
        $options = array("SendStreamParamsAtExec" => 0);
        $streamParam = array(&$fileStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'));

        /* Add stream parameter at the beginning. */
        array_unshift($params, $streamParam);

        $stmt = sqlsrv_prepare($this->link, $tsql, $params, $options);
        if ($stmt === false) {
            if (!$handleErrors) return false;
            die($this->FormatErrors(sqlsrv_errors()));
        }
        if (sqlsrv_execute($stmt) === false) {
            if (!$handleErrors) return false;
            die($this->FormatErrors(sqlsrv_errors()));
        }
        /* Stream data to the database in chunks of 8k. */
        while ($success = sqlsrv_send_stream_data($stmt)) {
        }

        if (sqlsrv_next_result($stmt) != false) {
            if (sqlsrv_fetch($stmt) != false) $retval = sqlsrv_get_field($stmt, 0);
        }
        return $retval;
    }

    function UpdateBinaryStream(string $tsql, $fileStream,array $params = [],bool $handleErrors = true): bool
    {
        // pozor tady je rozdil proti SendBinaryStream, ze to nanecitam ze souboru - jenze jak mu to predat? Zkusim stejne
        // http://msdn.microsoft.com/en-us/library/cc296183(SQL.90).aspx
        //$retval = false;
        $options = array("SendStreamParamsAtExec" => 0);
        $streamParam = array(&$fileStream, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'));

        /* Add stream parameter at the beginning. */
        array_unshift($params, $streamParam);

        $stmt = sqlsrv_prepare($this->link, $tsql, $params, $options);
        if ($stmt === false) {
            if (!$handleErrors) return false;
            die($this->FormatErrors(sqlsrv_errors()));
        }
        if (sqlsrv_execute($stmt) === false) {
            if (!$handleErrors) return false;
            die($this->FormatErrors(sqlsrv_errors()));
        }
        /* Stream data to the database in chunks of 8k. */
        while ($success = sqlsrv_send_stream_data($stmt)) {
        }
        $retval = true;
        return $retval;
    }

    function FormatErrors(mixed $errors): string
    {
        $retval = '';
        if ($this->debug) {
            $retval .= "Error information: <br/>";
            if ($errors != null) {
                foreach ($errors as $error) {
                    $retval .= "SQLSTATE: " . $error['SQLSTATE'] . "<br/>";
                    $retval .= "Code: " . $error['code'] . "<br/>";
                    $retval .= "Message: " . $error['message'] . "<br/>";
                }
            } else {
                $retval .= "<br/>No detailed error information available.";
            }
        }
        return $retval;
    }

}
