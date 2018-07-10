<?php
declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\GuildChannelRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildChannel;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;

class GuildChannelRepository extends PostgresqlRepository implements RepositoryInterface
{
    
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
    

    public function save(GuildChannel $channel)
    {
        
    }
    
    public function findById(ChannelId $id) : ?GuildChannel
    {
        $stmt = $this->persistence->prepare(self::GET_BY_ID);
        $stmt->bindValue('id', $id->get(), PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
