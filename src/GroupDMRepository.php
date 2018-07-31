<?php

declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql;

use FTC\Discord\Model\Channel\DMChannel\DMRepository as RepositoryInterface;
use FTC\Discord\Model\Aggregate\Guild;
use FTC\Discord\Model\Channel\DMChannel\DM;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;

class GroupDMRepository extends PostgresqlRepository implements RepositoryInterface
{

    const SELECT_DM = <<<'EOT'
SELECT id, owner_id, recipient_id, last_message_id from dm_channels
WHERE id = :id;
EOT;

    const DELETE_DM = <<<'EOT'
UPDATE dm_channels SET is_active = false WHERE id = :id
EOT;

    const INSERT_DM = <<<'EOT'
INSERT INTO dm_channels VALUES (:id, :owner_id, :recipient_id, :last_message_id)
ON CONFLICT (id) DO UPDATE SET last_message_id = :last_message_id
EOT;


    /**
     * @var Guild[]
     */
    private $guilds;
    
    public function save(DM $dm) : bool
    {
        throw new \Exception('NOT IMPLEMENTED');
    }
    
    public function delete(ChannelId $id) : bool
    {
        throw new \Exception('NOT IMPLEMENTED');
    }
    
}
