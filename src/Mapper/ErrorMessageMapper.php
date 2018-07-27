<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;


use FTC\Discord\Model\Aggregate\ErrorMessage;

class ErrorMessageMapper
{
    
    public static function create(array $data) : ErrorMessage
    {
        return ErrorMessage::createFromScalarTypes(
            (int) $data['id'],
            (int) $data['code'],
            $data['error_message'],
            $data['file'],
            (int) $data['line'],
            $data['message'],
            $data['time']
            );
    }
    
}
