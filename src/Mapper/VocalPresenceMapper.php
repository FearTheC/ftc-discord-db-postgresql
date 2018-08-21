<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\ValueObject\Presence\VocalPresence;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Hash\MD5;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;

class VocalPresenceMapper
{
    
    public static function create(array $data) : VocalPresence
    {
        $vp = VocalPresence::create(
            UserId::create($data['member_id']),
            ChannelId::create($data['channel_id']),
            MD5::create($data['session_id'])
            );
        
        if ($data['start_time']) {
            $vp->start(new \DateTime($data['start_time']));
        }
        
        if ($data['end_time']) {
            $vp->stop(new \DateTime($data['end_time']));
        }
        
        return $vp;
    }
    
}
