<?php declare(strict_types=1);

namespace FTC\Discord\Db\Postgresql\Mapper;

use FTC\Discord\Model\Aggregate\GuildChannel;
use FTC\Discord\Model\Channel\GuildChannel\TextChannel;
use FTC\Discord\Model\ValueObject\Name\ChannelName;
use FTC\Discord\Model\ValueObject\PermissionOverwrite;
use FTC\Discord\Model\Collection\PermissionOverwriteCollection;
use FTC\Discord\Model\ValueObject\Snowflake\ChannelId;
use FTC\Discord\Model\Channel\GuildChannel\Voice;
use FTC\Discord\Model\ValueObject\Snowflake\CategoryId;
use FTC\Discord\Model\ValueObject\Text\ChannelTopic;
use FTC\Discord\Model\ValueObject\Snowflake;
use FTC\Discord\Model\Channel\GuildChannel\Category;

class GuildChannelMapper
{
    
    const CHANNEL_CREATION_PROCESS = [
        0 => 'createTextChannel',
        2 => 'createVoiceChannel',
        4 => 'createCategory',
    ];
    
    public static function create(array $data) : GuildChannel
    {
        return self::{self::CHANNEL_CREATION_PROCESS[$data['type_id']]}($data);
    }
    
    private static function createTextChannel(array $data) : TextChannel
    {
        $permissionOverwrites = self::createPermissionsOverwrites($data['permission_overwrite']);
        
        return TextChannel::create(
            ChannelId::create($data['id']),
            ChannelName::create($data['name']),
            $data['position'],
            $permissionOverwrites,
            self::getCategoryId($data),
            self::getTopic($data)
        );
    }
    
    private static function createVoiceChannel(array $data) : Voice
    {
        
        return Voice::create(
            ChannelId::create($data['id']),
            ChannelName::create($data['name']),
            $data['position'],
            self::createPermissionsOverwrites($data['permission_overwrite']),
            self::getCategoryId($data),
            $data['bitrate'],
            $data['user_limit']
        );
    }
    
    private static function createCategory(array $data) : Category
    {
        return Category::create(
            ChannelId::create($data['id']),
            ChannelName::create($data['name']),
            $data['position'],
            self::createPermissionsOverwrites($data['permission_overwrite']),
            self::getCategoryId($data)
        );
    }
            
    
    private static function createPermissionsOverwrites($data)
    {
        $permissionOverwrites = array_map(function($permission) {
            return PermissionOverwrite::create(
                Snowflake::create($permission['subject_id']),
                $permission['allow'],
                $permission['deny']
            );
            }, json_decode($data, true));
        
        return new PermissionOverwriteCollection(...$permissionOverwrites);
    }
    
    private static function getTopic(array $data) : ?ChannelTopic
    {
        if ($data['topic']) {
            return ChannelTopic::create($data['topic']);
        }
        
        return null;
    }
    
    private static function getCategoryId(array $data) : ?CategoryId
    {
        if ($data['category_id']) {
            return CategoryId::create($data['category_id']);
        }
        
        return null;
    }
    
}
