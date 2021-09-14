<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Builders\MessageBuilder;
use Lorhondel\Http\Endpoint;
use Lorhondel\Parts\Channel\Channel;
use Lorhondel\Parts\Part;
use Lorhondel\Parts\Channel\Message;
use React\Promise\ExtendedPromiseInterface;

/**
 * A player is a general player that is not attached to a group.
 *

 * @property int    $health        Health.
 * @property int    $attack        Attack.
 * @property int    $defense       Defense.
 * @property int    $speed         Speed.
 *
 * @property \Discord\Parts\User    $user        Discord user
 */
class Player extends Part
{

    /**
     * @inheritdoc
     */
    protected $fillable = ['health', 'attack', 'defense', 'speed', 'user'];

    /**
     * Returns the avatar hash for the client.
     *
     * @return string The client avatar's hash.
     */
    protected function getAvatarHashAttribute(): string
    {
        return $this->attributes['avatar'];
    }

    /**
     * Returns a timestamp for when a player's account was created.
     *
     * @return float
     */
    public function createdTimestamp()
    {
        return \Lorhondel\getSnowflakeTimestamp($this->id);
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [
            'player_id' => $this->id,
        ];
    }

    /**
     * Returns a formatted mention.
     *
     * @return string A formatted mention.
     */
    public function __toString()
    {
        return "<@{$this->id}>";
    }
}
