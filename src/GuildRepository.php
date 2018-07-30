<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;


use FTC\Discord\Model\Aggregate\GuildRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\Guild;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use FTC\Discord\Model\ValueObject\DomainName;
use FTC\Discord\Db\Postgresql\Mapper\GuildMapper;
use FTC\Discord\Model\Collection\GuildCollection;

class GuildRepository extends PostgresqlRepository implements RepositoryInterface
{

    const SELECT_GUILD = <<<'EOT'
SELECT * from guilds_aggregates
WHERE id = :id;
EOT;

    const SELECT_GUILD_BY_DOMAIN_NAME = <<<'EOT'
SELECT id, name, owner_id, joined_date, domain, members_ids, roles_ids, channels_ids FROM guilds_aggregates
WHERE domain = :domain_name
EOT;

    const SELECT_ALL_GUILDS = <<<'EOT'
SELECT * 
FROM guilds_aggregates guilds
EOT;

    const INSERT_GUILD = <<<'EOT'
INSERT INTO guilds VALUES (:id, :name, :owner_id)
ON CONFLICT (id) DO UPDATE SET name = :name, owner_id = :owner_id
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
    
    public function getAll() : GuildCollection
    {
        $stmt = $this->persistence->prepare(self::SELECT_ALL_GUILDS);
        $stmt->execute();
        
        $array =  $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $array = array_map([GuildMapper::class, 'create'], $array);
        $guilds = new GuildCollection(...$array);
        
        return $guilds;
    }
    
    public function findByDomainName(DomainName $domainName) : ?Guild
    {
        $stmt = $this->persistence->prepare(self::SELECT_GUILD_BY_DOMAIN_NAME);
        $stmt->bindValue('domain_name', (string) $domainName, \PDO::PARAM_STR);
        $stmt->execute();
        
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return GuildMapper::create($data);
    }
    
    public function findById(GuildId $id) : ?Guild
    {
    }



}
