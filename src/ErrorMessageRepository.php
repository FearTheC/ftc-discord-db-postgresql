<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\ErrorMessageRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildChannel;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\Collection\GuildChannelCollection;
use FTC\Discord\Db\Postgresql\Mapper\GuildChannelMapper;
use FTC\Discord\Model\Aggregate\ErrorMessage;
use FTC\Discord\Db\Postgresql\Mapper\ErrorMessageMapper;
use FTC\Discord\Model\Collection\ErrorMessageCollection;

class ErrorMessageRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const SELECT_ALL = <<<'EOT'
SELECT id, error_message, code, file, line, message, time
FROM message_handling_error_logs
EOT;
    
    const INSERT_ERROR = <<<'EOT'
INSERT INTO message_handling_error_logs (code, error_message, file, line, message)
VALUES (:code, :error_message, :file, :line, :message)
EOT;

    
    public function save(ErrorMessage $errorMessage)
    {
        $stmt = $pdo->prepare(INSERT_ERROR);
        $stmt->bindValue('code', $e->getCode(), \PDO::PARAM_STR);
        $stmt->bindValue('error_message', $e->getMessage(), \PDO::PARAM_STR);
        $stmt->bindValue('file', $e->getFile(), \PDO::PARAM_STR);
        $stmt->bindValue('line', $e->getLine(), \PDO::PARAM_STR);
        $stmt->bindvalue('message', (string) $message, \PDO::PARAM_STR);
        $stmt->execute(); 
    }
    
    public function remove(ErrorMessage $errorMessage)
    {
        throw new \Exception('Not Implemented');
        
    }
    
    public function getAll() : ErrorMessageCollection
    {
        $stmt = $this->persistence->prepare(self::SELECT_ALL);
        $stmt->execute();
        
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $errorMessages = array_map([ErrorMessageMapper::class, 'create'], $data);
        return new ErrorMessageCollection(...$errorMessages);
    }
    
}
