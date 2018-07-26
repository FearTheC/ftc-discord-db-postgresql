<?php
declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\GuildMessageRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildMember;
use FTC\Discord\Model\Collection\GuildMemberCollection;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Db\Postgresql\Mapper\GuildMemberMapper;
use FTC\Discord\Model\Aggregate\GuildMessage;
use FTC\Discord\Model\ValueObject\Snowflake\MessageId;
use FTC\Discord\Model\Collection\GuildMessageCollection;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;

class GuildMessageRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const INSERT_GUILD_MESSAGE = <<<'EOT'
INSERT INTO channels_messages  (id, channel_id, author_id, type, content, creation_time, update_time, is_pinned)
VALUES (:id, :channel_id, :author_id, :type, :content, :creation_time, :update_time, :is_pinned)
ON CONFLICT (id) DO UPDATE SET content = :content
EOT;

    const SELECT_ALL_GUILD_MESSAGE = <<<'EOT'
EOT;
    
    public function save(GuildMessage $message)
    {
        $this->persistence->beginTransaction();
        
        if ($message->getUpdateTime()) {
            $updateTime = $message->getUpdateTime()->format('Y-m-d H:i:s');
        }
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_MESSAGE);
        $stmt->bindValue('id', (int) (string) $message->getId(), \PDO::PARAM_INT);
        $stmt->bindValue('channel_id', $message->getChannelId(), \PDO::PARAM_INT);
        $stmt->bindValue('author_id', $message->getAuthorId(), \PDO::PARAM_INT);
        $stmt->bindValue('type', (int) (string) $message->getType(), \PDO::PARAM_INT);
        $stmt->bindValue('is_pinned', $message->isPinned(), \PDO::PARAM_BOOL);
        $stmt->bindValue('content', (string) $message->getContent(), \PDO::PARAM_STR);
        $stmt->bindValue('creation_time', $message->getCreationTime()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stmt->bindValue('update_time', $updateTime, \PDO::PARAM_STR);
        $stmt->execute();
        
        $this->persistence->commit();
    }
    
    public function remove(GuildMessage $message)
    {
        
    }
    
    
    
    public function getAllForGuild(GuildId $guildId) : GuildMessageCollection
    {
        
    }
    
    public function getAllForAuthor(GuildId $guildId, UserId $userId) : GuildMessageCollection
    {
        
    }
    
    public function getAllForChannel(ChannelId $channelId) : GuildMessageCollection
    {
        
    }
    
    public function findById(MessageId $messageId) : GuildMessage
    {
        
    }
    
}
