<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Aggregate\GuildRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\Guild;
use FTC\Discord\Model\Aggregate\GuildMember;
use FTC\Discord\Model\Aggregate\GuildRole;
use FTC\Discord\Model\Aggregate\GuildChannel;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\Channel\GuildChannel\TextChannel;
use FTC\Discord\Model\Channel\GuildChannel\Voice;
use FTC\Discord\Db\Postgresql\Mapper\GuildMemberMapper;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;

class GuildRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const SD = <<<'EOT'
CREATE OR REPLACE VIEW guilds_aggregates AS
SELECT guilds.id, guilds.name, jsonb_agg(guilds_users.id) as members, jsonb_agg(guilds_roles.*) AS roles FROM guilds
LEFT JOIN guilds_users ON guilds_users.guild_id = guilds.id
LEFT JOIN guilds_roles ON guilds_roles.guild_id = guilds.id
GROUP BY guilds.id, guilds.name
EOT;

    const SELECT_GUILD = <<<'EOT'
SELECT * from guilds_aggregates
WHERE id = :id;
EOT;

    const SELECT_GUILD_MEMBER = <<<'EOT'
SELECT guilds_users.user_id as id, guilds_users.nickname, guilds_users.joined_date, json_agg(users_roles.role_id) AS roles_ids FROM guilds_users
JOIN users_roles ON users_roles.user_id = guilds_users.user_id
where guilds_users.guild_id = :guild_id AND guilds_users.user_id = :member_id
GROUP BY guilds_users.user_id, guilds_users.nickname, guilds_users.joined_date
EOT;

    const INSERT_GUILD = "INSERT INTO guilds VALUES (:id, :name, :owner_id) ON CONFLICT (id) DO UPDATE SET name = :name, owner_id = :owner_id";
    
    const INSERT_GUILD_MEMBER = <<<'EOT'
INSERT INTO guilds_users VALUES (:id, :user_id, :nickname, :joined_at)
ON CONFLICT (guild_id, user_id) DO UPDATE SET nickname = :nickname
EOT;

    const INSERT_GUILD_MEMBER_ROLES = <<<'EOT'
INSERT INTO users_roles VALUES (:user_id, :role_id)
ON CONFLICT (user_id, role_id) DO NOTHING
EOT;

    /**
     * @var Guild[]
     */
    private $guilds;
    
    public function save(Guild $guild)
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD);
        $stmt->bindValue('id', $guild->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', (string) $guild->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('owner_id', $guild->getOwnerId()->get(), \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(GuildId $id) : ?Guild
    {
//         $stmt = $this->persistence->prepare(self::SD);
//         $stmt->execute();
        
//         $stmt = $this->persistence->prepare(self::SELECT_GUILD);
//         $stmt->bindValue('id', $id->get(), \PDO::PARAM_INT);
//         $stmt->execute();
//         $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
//         $guildId = Snowflake::create($row['id']);
        
        
//         $members = new GuildMemberCollection();
//         $roles = new GuildRoleCollection();
        
//         foreach (json_decode($row['roles'], true) as $role) {
//             $roles->add(GuildRole::create(Snowflake::create($role['id']), $role['name']));
//         }
        
//         foreach (json_decode($row['members'], true) as $member) {
//             $members->add(GuildMember::create($guildId, UserId::create($member['user_id']), $roles,'nickname'));
//         }

//         $guild = Guild::create(
//             $guildId,
//             $row['name'],
//             Snowflake::create(272341331328761888),
//             $roles,
//             $members);

//         return $guild;
    }
    
    
    
    public function getGuildMember(GuildId $guildId, UserId $memberId) : ?GuildMember
    {
        $stmt = $this->persistence->prepare(self::SELECT_GUILD_MEMBER);
        $stmt->bindValue('member_id', (string) $memberId, \PDO::PARAM_INT);
        $stmt->bindValue('guild_id', (string) $guildId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $userArray = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        
        return GuildMemberMapper::create($userArray);
    }
    
    private function saveRole(GuildRole $role, GuildId $guildId) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_ROLE);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('id', $role->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $role->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('color', $role->getColor()->getInteger(), \PDO::PARAM_INT);
        $stmt->bindValue('position', $role->getPosition(), \PDO::PARAM_INT);
        $stmt->bindValue('permissions', $role->getPermissions(), \PDO::PARAM_INT);
        $stmt->bindValue('mentionable', $role->isMentionable(), \PDO::PARAM_BOOL);
        $stmt->bindValue('hoist', $role->isHoisted(), \PDO::PARAM_BOOL);
        $stmt->execute();
    }
    
    private function saveChannel(GuildChannel $channel, GuildId $guildId)
    {
        $channel->getPermissionOverwrites()->toJson();
        if ($categoryId = $channel->getCategoryId()) {
            $categoryId = $categoryId->get();
        }
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_CHANNEL);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('id', $channel->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $channel->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('position', $channel->getPosition(), \PDO::PARAM_INT);
        $stmt->bindValue('type_id', $channel->getTypeId(), \PDO::PARAM_INT);
        $stmt->bindValue('permission_overwrite', $channel->getPermissionOverwrites()->toJson(), \PDO::PARAM_INT);
        $stmt->bindValue('category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($channel instanceof TextChannel && $topic = $channel->getTopic()) {
            $stmt = $this->persistence->prepare(self::INSERT_TEXT_CHANNEL);
            $stmt->bindValue('channel_id', $channel->getId()->get(), \PDO::PARAM_INT);
            $stmt->bindValue('topic', (string) $topic, \PDO::PARAM_STR);
            $stmt->execute();
        }
        
        if ($channel instanceof Voice) {
            $stmt = $this->persistence->prepare(self::INSERT_VOICE_CHANNEL);
            $stmt->bindValue('channel_id', $channel->getId()->get(), \PDO::PARAM_INT);
            $stmt->bindValue('bitrate', $channel->getBitrate(), \PDO::PARAM_INT);
            $stmt->bindValue('user_limit', $channel->getUserLimit(), \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

}
