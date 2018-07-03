<?php
namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\GuildRepository as RepositoryInterface;
use FTC\Discord\Model\Guild;
use FTC\Discord\Model\GuildMember;

class GuildRepository extends PostgresqlRepository implements RepositoryInterface
{
    
    const INSERT_GUILD = 'INSERT INTO guilds (id, name) ON CONFLICT DO UPDATE';
    const INSERT_GUILD_MEMBER = 'INSERT INTO guilds_users (id, user_id) ON CONFLICT DO UPDATE';
    const INSERT_GUILD_ROLE = 'INSERT INTO guilds_roles (role_id, id, name) ON CONFLICT DO UPDATE';

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
            array_fill(0, $guild->getMembers()->count(), $guild->getId()->get())
        );
        $this->persistence->commit();
    }
    
    public function getAll() : array
    {
        
    }
    
    public function findById(int $id) : ?Guild
    {
        
    }
    
    private function saveGuild(Guild $guild) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD);
        $stmt->bindParam('id', $guild->getId()->get(), \PDO::PARAM_INT);
        $stmt->bindParam('name', $guild->getName(), \PDO::PARAM_STR);
        $stmt->execute();
    }

    private function saveMember(GuildMember $member, Snowflake $guildId) : void
    {
        $stmt = $this->persistence->prepare(self::INSERT_GUILD_MEMBER);
        $stmt->bindParam('id', $guildId->get(), \PDO::PARAM_INT);
        $stmt->bindParam('user_id', $member->getId()->get(), \PDO::PARAM_INT);
        $stmt->execute();
    }

}
