<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Exceptions\FileNotFoundException;
use Lorhondel\Endpoint;
use Lorhondel\Parts\OAuth\Application;
use Lorhondel\Parts\Part;
use Lorhondel\Repository\AccountRepository;
use Lorhondel\Repository\PlayerRepository;
use Lorhondel\Repository\PetRepository;
use Lorhondel\Repository\PartyRepository;
use Lorhondel\Repository\BattleRepository;
use Lorhondel\Repository\VoteRepository;
use React\Promise\ExtendedPromiseInterface;

/**
 * The client is the main interface for the client. Most calls on the main class are forwarded here.
 *
 * @property User|null                $user             The Discord user instance of the bot.
 * @property PlayerRepository         $players
 * @property PartyRepository          $parties
 * @property BattleRepository         $battles
 * @property BattleRepository         $votes
 */
class Client extends Part
{
    /**
     * @inheritdoc
     */
    protected static $fillable = ['user'];

    /**
     * @inheritdoc
     */
    protected $repositories = [
        'accounts' => AccountRepository::class,
        'battles' => BattleRepository::class,
        //'enemies' => EnemyRepository::class,
        //'npcs' => NPCRepository::class,
        'parties' => PartyRepository::class,
        'pets' => PetRepository::class,
        'players' => PlayerRepository::class,
        'votes' => VoteRepository::class,
    ];

    /**
     * Runs any extra construction tasks.
     */
    public function afterConstruct(): void
    {
        $this->application = $this->factory->create(Application::class, [], true);

        $this->http->get(Endpoint::APPLICATION_CURRENT)->done(function ($response) {
            $this->application->fill((array) $response);
        });
    }

    /**
     * Gets the Player attribute.
     *
     * @return Player
     */
    protected function getPlayerAttribute()
    {
        return $this->factory->create(Player::class, $this->attributes, true);
    }
    
    /**
     * Saves the client instance.
     *
     * @return ExtendedPromiseInterface
     */
    public function save(): ExtendedPromiseInterface
    {
        return $this->http->patch(Endpoint::PLAYER_CURRENT, $this->getUpdatableAttributes());
    }

    /**
     * @inheritdoc
     */
    public function getUpdatableAttributes($discord = null): array
    {
        if (isset($this->attributes['discord_id'])) {
            $attributes['discord_id'] = $this->discord->users->offsetGet($attributes['discord_id']);
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [];
    }
}
