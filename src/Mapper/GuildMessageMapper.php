<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\Aggregate\GuildMessage;
use FTC\Discord\Model\ValueObject\Snowflake\MessageId;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;
use FTC\Discord\Model\ValueObject\MessageType;
use FTC\Discord\Model\ValueObject\Text\ChannelMessage;

class GuildMessageMapper
{
    
    public static function create(array $data) : GuildMessage
    {
        $updateTime = null;
        if (isset($data['update_time'])) {
            $updateTime = new \DateTime($data['edited_timestamp']);
        }
        
        return new GuildMessage(
            MessageId::create((int) $data['id']),
            ChannelId::create((int) $data['channel_id']),
            UserId::create((int) $data['author_id']),
            MessageType::create((int) $data['type']),
            ChannelMessage::create($data['content']),
            new \DateTime($data['creation_time']),
            $updateTime,
            $data['is_pinned']
            );
    }
    
}
