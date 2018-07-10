<?php
declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Aggregate\GuildMemberRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\GuildMember;
use FTC\Discord\Model\Collection\GuildMemberCollection;
use FTC\Discord\Model\ValueObject\Snowflake\UserId;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;

class GuildMemberRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const INSERT_GUILD_MEMBER = "INSERT INTO guilds_users VALUES (:guild_id, :user_id, :nickname, :joined_date)";
    
    const ADD_MEMBER_ROLE = "INSERT INTO members_roles VALUES (:user_id, :role_id)";
    
    const SELECT_COUNT_BY_ROLE = <<<'EOT'
SELECT DISTINCT r.name, count(members.user_id) FROM guilds_users members
JOIN members_roles roles on roles.user_id = members.user_id
JOIN guilds_roles r ON r.id = roles.role_id AND r.guild_id = :guild_id AND r.name IN (%s)
GROUP BY (r.name)
EOT;
    
    
    /**
     * @var GuildMember[]
     */
    private $members;
    
    public function getGuildMember(GuildId $guildId, UserId $memberId) : ?GuildMember
    {
        $stmt = $this->persistence->prepare(self::SELECT_GUILD_MEMBER);
        $stmt->bindValue('member_id', (string) $memberId, \PDO::PARAM_INT);
        $stmt->bindValue('guild_id', (string) $guildId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $userArray = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        
        return GuildMemberMapper::create($userArray);
    }
    
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
    
    public function getAll() : GuildMemberCollection
    {
        $stmt = $this->persistence->prepare(self::GET_ALL_QUERY);
        $stmt->execute();
        $array =  $stmt->fetchAll(\PDO::FETCH_ASSOC);   
        $coll = $this->fromArray($array, $coll);
        
        return $coll;
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
