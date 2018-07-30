<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\Collection\GuildRoleIdCollection;
use FTC\Discord\Model\Aggregate\Guild;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Name\GuildName;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;
use FTC\Discord\Model\Collection\GuildMemberIdCollection;
use FTC\Discord\Model\Collection\GuildChannelIdCollection;
use FTC\Discord\Model\ValueObject\DomainName;

class GuildMapper
{
    
    public static function create(array $data) : Guild
    {
        $rolesIds = array_map([RoleId::class, 'create'], json_decode($data['roles_ids']));
        $membersIds = array_map([UserId::class, 'create'], json_decode($data['members_ids']));
        $channelsIds = array_map([ChannelId::class, 'create'], json_decode($data['channels_ids']));

        if ($domainName = $data['domain']) {
            $domainName = DomainName::create($domainName);
        }

        return Guild::create(
            GuildId::create($data['id']),
            GuildName::create($data['name']),
            UserId::create($data['owner_id']),
            new \DateTime($data['joined_date']),
            new GuildRoleIdCollection(...$rolesIds),
            new GuildMemberIdCollection(...$membersIds),
            new GuildChannelIdCollection(...$channelsIds),
            $domainName
       );
    }
    
}
