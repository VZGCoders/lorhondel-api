<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\OAuth;

use Lorhondel\Parts\Part;
use Lorhondel\Parts\Permissions\Permission;
use Lorhondel\Parts\User\User;

/**
 * The OAuth2 application of the bot.
 *
 * @property string   $id          The client ID of the OAuth application.
 * @property string   $name        The name of the OAuth application.
 * @property string   $description The description of the OAuth application.
 * @property string   $icon        The icon hash of the OAuth application.
 * @property string   $invite_url  The invite URL to invite the bot to a guild.
 * @property string[] $rpc_origins An array of RPC origin URLs.
 * @property int      $flags       ?
 * @property User     $owner       The owner of the OAuth application.
 */
class Application extends Part
{
    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'name', 'description', 'icon', 'rpc_origins', 'flags', 'owner'];

	/**
     * Returns the fillable attributes.
     *
     * @return array
     */
    public static function getFillableAttributes($context = '')
	{
		$fillable = array();
		foreach (self::$fillable as $attr) {
			if (! $context || in_array($context, $attrContexts)) {
				$fillable[] = $attr;
			}
		}
		return $fillable;
	}

    /**
     * Returns the owner of the application.
     *
     * @return User       Owner of the application.
     * @throws \Exception
     */
    protected function getOwnerAttribute(): ?User
    {
        if (isset($this->attributes['owner'])) {
            return $this->factory->create(User::class, $this->attributes['owner'], true);
        }
        
        return null;
    }

    /**
     * Returns the invite URL for the application.
     *
     * @param Permission|int $permissions Permissions to set.
     *
     * @return string Invite URL.
     */
    public function getInviteURLAttribute($permissions = 0): string
    {
        if ($permissions instanceof Permission) {
            $permissions = $permissions->bitwise;
        }

        return "https://lorhondel.valzargaming.com/oauth2/authorize?client_id={$this->id}&scope=bot&permissions={$permissions}";
    }
}
