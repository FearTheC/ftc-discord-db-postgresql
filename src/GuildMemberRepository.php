<?php
declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\GuildMemberRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildMember;
use FTC\Discord\Model\Collection\GuildMemberCollection;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Db\Postgresql\Mapper\GuildMemberMapper;

class GuildMemberRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const INSERT_GUILD_MEMBER = <<<'EOT'
INSERT INTO guilds_users  (guild_id, user_id, nickname, joined_date)
VALUES (:guild_id, :user_id, :nickname, :joined_date)
ON CONFLICT (guild_id, user_id) DO UPDATE (nickname)
EOT;
    
    const ADD_MEMBER_ROLE = "INSERT INTO members_roles VALUES (:user_id, :role_id)";
    
    const SELECT_GUILD_MEMBER = <<<'EOT'
SELECT members.id, members.roles, members.joined_date, members.nickname
FROM view_guilds_members members
WHERE members.id = :member_id
EOT;
    
    const SELECT_ALL_GUILD_MEMBER = <<<'EOT'
SELECT members.id, members.roles, members.joined_date, members.nickname
FROM view_guilds_members members
WHERE members.guild_id = :guild_id
EOT;
    
//     const SELECT_GUILD_MEMBER = <<<'EOT'
// SELECT members.id, members.roles, members.joined_date
// , jsonb_agg(x.roles->>'id') as roles_ids
// FROM view_guilds_members members, LATERAL (SELECT jsonb_array_elements(members.roles) as roles) x
// WHERE members.guild_id = :guild_id
// GROUP BY members.id, members.roles, members.joined_date;
// EOT;

    const SELECT_COUNT_BY_ROLE = <<<'EOT'
SELECT DISTINCT r.name, count(members.user_id) FROM guilds_users members
JOIN members_roles roles on roles.user_id = members.user_id
JOIN guilds_roles r ON r.id = roles.role_id AND r.guild_id = :guild_id AND r.name IN (%s)
GROUP BY (r.name)
EOT;
    
    const SELECT_ALL = <<<'EOT'
SELECT  members.roles->'id' FROM view_guilds_members members
WHERE guild_id = :guild_id
EOT;

    
    
    /**
     * @var GuildMember[]
     */
    private $members;

    public function save(GuildMember $member, GuildId $guildId)
    {
        $this->persistence->beginTransaction();
        
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_MEMBER);
        $stmt->bindValue('guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $stmt->bindValue('nickname', $member->getNickname(), \PDO::PARAM_STR);
        $stmt->bindValue('joined_date', $member->getJoinDate()->format('Y-m-d H:i'), \PDO::PARAM_STR);
        $stmt->execute();
        
        array_map(
            [$this, 'addRole'],
            $member->getRoles()->getIterator(),
            array_fill(0, $member->getRoles()->count(), $member)
            );
        
        $this->persistence->commit();
    }
    
    public function addRole(RoleId $roleId, GuildMember $member)
    {
        $stmt = $this->persistence->prepare(self::ADD_MEMBER_ROLE);
        $stmt->bindValue('user_id', $member->getId(), \PDO::PARAM_INT);
        $stmt->bindValue('role_id', $roleId->get(), \PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function remove(GuildMember $member)
    {
        
    }
    
    public function getById(UserId $memberId) : ?GuildMember
    {
        $stmt = $this->persistence->prepare(self::SELECT_GUILD_MEMBER);
        $stmt->bindValue('member_id', (string) $memberId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $userArray = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        
        return GuildMemberMapper::create($userArray);
    }
    
    public function getAll(GuildId $guildId) : GuildMemberCollection
    {
        $stmt = $this->persistence->prepare(self::SELECT_ALL_GUILD_MEMBER);
        $stmt->bindValue(':guild_id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->execute();
        
        $array =  $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $array = array_map([GuildMemberMapper::class, 'create'], $array);
        $guildMembers = new GuildMemberCollection(...$array);
        
        return $guildMembers;
    }
    
    public function countByRole(GuildId $guildId, $roleNames)
    {
        $query = sprintf(self::SELECT_COUNT_BY_ROLE, implode(', ', $roleNames));
        $stmt = $this->persistence->prepare($query);
        $stmt->bindParam(':guild_id', $guildId, \PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_NAMED);
        
        return $results;
    }
    
    private function fromArray($array)
    {
        $array = array_map([GuildMember::class, 'fromDb'], $array);
        return new GuildMemberCollection(...$array);
        
        return $collee;
    }
    
    public function findById(UserId $id) : GuildMember
    {
    }
    
    
}
