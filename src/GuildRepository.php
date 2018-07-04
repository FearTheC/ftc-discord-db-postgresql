<?php
namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\GuildRepository as RepositoryInterface;
use FTC\Discord\Model\Guild;
use FTC\Discord\Model\GuildMember;
use FTC\Discord\Model\ValueObject\Snowflake;
use FTC\Discord\Model\GuildRole;
use FTC\Discord\Model\Collection\GuildMemberCollection;
use FTC\Discord\Model\Collection\GuildRoleCollection;
use FTC\Discord\Model\User;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;

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

    const INSERT_GUILD = "INSERT INTO guilds VALUES (:id, :name, :owner_id) ON CONFLICT (id) DO UPDATE SET name = :name, owner_id = :owner_id";
    const INSERT_GUILD_MEMBER = 'INSERT INTO guilds_users VALUES (:id, :user_id, :nickname)  ON CONFLICT (guild_id, user_id) DO UPDATE SET nickname = :nickname';
    
    const INSERT_GUILD_ROLE = <<<'EOT'
INSERT INTO guilds_roles VALUES (:id, :guild_id, :name, :color, :position, :permissions, :mentionable, :hoist)
ON CONFLICT (id) DO UPDATE SET name = :name, color=:color, position=:position, permissions=:permissions, is_hoisted=:hoist, is_mentionable=:mentionable
EOT;

    /**
     * @var Guild[]
     */
    private $guilds;
    
    public function save(Guild $guild)
    {
        $this->persistence->beginTransaction();
        $this->saveGuild($guild);
        array_map(
            [$this, 'saveMember'],
            $guild->getMembers()->toArray(),
            array_fill(0, $guild->getMembers()->count(), $guild->getId())
        );
        array_map(
            [$this, 'saveRole'],
            $guild->getRoles()->toArray(),
            array_fill(0, $guild->getRoles()->count(), $guild->getId())
        );
        $this->persistence->commit();
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
    
    private function saveGuild(Guild $guild) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD);
        $stmt->bindValue('id', $guild->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', (string) $guild->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('owner_id', $guild->getOwnerId()->get(), \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    private function saveMember(GuildMember $member, Snowflake $guildId) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_MEMBER);
        $stmt->bindValue('id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('user_id', $member->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('nickname', $member->getNickname(), \PDO::PARAM_STR);
        $stmt->execute();
    }
    
    private function saveRole(GuildRole $role, Snowflake $guildId) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_ROLE);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('id', $role->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $role->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('color', $role->getColor()->getInteger(), \PDO::PARAM_INT);
        $stmt->bindValue('position', $role->getPosition(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $role->getName(), \PDO::PARAM_STR);
        $stmt->bindValue('permissions', $role->getPermissions(), \PDO::PARAM_INT);
        $stmt->bindValue('mentionable', $role->isMentionable(), \PDO::PARAM_BOOL);
        $stmt->bindValue('hoist', $role->isHoisted(), \PDO::PARAM_BOOL);
        $stmt->execute();
    }

}
