<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Repository\VocalPresenceRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\Guild;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Db\Postgresql\Mapper\GuildMapper;
use FTC\Discord\Model\Collection\GuildCollection;
use FTC\Discord\Model\ValueObject\Presence\VocalPresence;
use FTC\Discord\Db\Postgresql\Mapper\VocalPresenceMapper;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;

class VocalPresenceRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const UPSERT_VOCAL_PRESENCE = <<<'EOT'
INSERT INTO members_vocal_presence VALUES (:channel_id, :member_id, :session_id, :start_time, :end_time)
ON CONFLICT (member_id, channel_id, start_time) DO UPDATE SET end_time = :end_time
EOT;

    const SELECT_LAST_VOCAL_PRESENCE = <<<'EOT'
SELECT channel_id, member_id, session_id, start_time, end_time
FROM members_vocal_presence members_vp
JOIN guilds_channels ON guilds_channels.id = members_vp.channel_id AND guilds_channels.guild_id = :guild_id
WHERE members_vp.member_id = :member_id
ORDER BY members_vp.start_time DESC
LIMIT 1
EOT;
    
    
    /**
     * @var Guild[]
     */
    private $guilds;
    
    public function save(VocalPresence $vp) : void
    {
        $endTime = null;
        if ($vp->getEnd()) {
            $endTime = $vp->getEnd()->format('Y-m-d H:i:s.u');
        }
        $stmt = $this->persistence->prepare(self::UPSERT_VOCAL_PRESENCE);
        $stmt->bindValue('member_id', $vp->getMemberId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('channel_id', $vp->getChannelId()->get(), \PDO::PARAM_STR);
        $stmt->bindValue('session_id', $vp->getSessionId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('start_time', $vp->getStart()->format('Y-m-d H:i:s.u'), \PDO::PARAM_STR);
        $stmt->bindValue('end_time', $endTime, \PDO::PARAM_STR);
        $stmt->execute();
    }
    
    
    public function getLastPresence(UserId $memberId, GuildId $guildId) : ?VocalPresence
    {
        $stmt = $this->persistence->prepare(self::SELECT_LAST_VOCAL_PRESENCE);
        $stmt->bindValue('member_id', $memberId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->execute();
        
        $data =  $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        
        return VocalPresenceMapper::create($data);
    }
    
    public function getAll() : GuildCollection
    {
        $stmt = $this->persistence->prepare(self::SELECT_ALL_GUILDS);
        $stmt->execute();
        
        $array =  $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $array = array_map([GuildMapper::class, 'create'], $array);
        $guilds = new GuildCollection(...$array);
        
        return $guilds;
    }
    
}
