<?php

declare(strict_types=1);

namespace Sitmpcz;

class MssqlBinaryException extends \Exception
{

    public function __construct(?array $errors = null)
    {
        //konstruktor se volá ve funkci writeError
        if ($errors === null || !is_array($errors) || !count($errors)) {
            throw new \InvalidArgumentException("Chybí chyby!");
        }else{
            $error = array_shift($errors);
            if(count($errors) > 0){
                $lastError = new MssqlBinaryException($errors);
                $this->writeError($error, $lastError);
            }else{
                $this->writeError($error);
            }
        }
    }

    private function writeError(array $error, MssqlBinaryException $previous = null){
        $code = $error['code'];
        $message = $error['message']." (SQLSTATE: ".$error['SQLSTATE'].")";
        parent::__construct($message, $code, $previous);
    }
}