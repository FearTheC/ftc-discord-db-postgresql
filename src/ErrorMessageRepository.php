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
    
    const GET_BY_ID = <<<'EOT'
SELECT * FROM guilds_channel
WHERE id = :id
EOT;

    const INSERT_GUILD_CHANNEL = <<<'EOT'
INSERT INTO guilds_channels VALUES (:id, :guild_id, :name, :position, :type_id, :permission_overwrite, :category_id)
ON CONFLICT (id) DO UPDATE SET name = :name, position = :position, permission_overwrite = :permission_overwrite, category_id = :category_id;
EOT;
    
    const INSERT_TEXT_CHANNEL = <<<'EOT'
INSERT INTO guilds_text_channels VALUES (:channel_id, :topic)
ON CONFLICT (channel_id) DO UPDATE SET topic = :topic;
EOT;
    
    const INSERT_VOICE_CHANNEL = <<<'EOT'
INSERT INTO guilds_voice_channels VALUES (:channel_id, :bitrate, :user_limit)
ON CONFLICT (channel_id) DO UPDATE SET bitrate = :bitrate, user_limit = :user_limit;
EOT;
    
    /**
     * @var GuildChannel[]
     */
    private $channels;
    
    
    public function save(ErrorMessage $errorMessage)
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_CHANNEL);
        $stmt->bindValue('id', $channel->getId()->get(), \PDO::PARAM_INT);
        
    }
    
    public function remove(ErrorMessage $errorMessage)
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_CHANNEL);
        $stmt->bindValue('id', $channel->getId()->get(), \PDO::PARAM_INT);
        
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
