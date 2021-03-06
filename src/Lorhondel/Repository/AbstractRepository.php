<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Factory\Factory;
use Lorhondel\Helpers\Collection;
use Lorhondel\Endpoint;
use Lorhondel\Http;
use Lorhondel\Parts\Part;
use React\Http\Browser;
use React\Promise\ExtendedPromiseInterface;

/**
 * Repositories provide a way to store and update parts on the Lorhondel server.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 * @author David Cole <david.cole1340@gmail.com>
 * @author Valithor Obsidion <valzargaming@gmail.com>
 */
 abstract class AbstractRepository extends Collection
{
    /**
     * The discriminator.
     *
     * @var string Discriminator.
     */
    protected $discrim = 'id';

    /**
     * The HTTP client.
     *
     * @var Http Client.
     */
    protected $http;
    
    /**
     * The Browser client.
     *
     * @var Browser Client.
     */
    protected $browser;

    /**
     * The parts factory.
     *
     * @var Factory Parts factory.
     */
    protected $factory;

    /**
     * Endpoints for interacting with the Lorhondel servers.
     *
     * @var array Endpoints.
     */
    protected $endpoints = [];

    /**
     * Variables that are related to the repository.
     *
     * @var array Variables.
     */
    protected $vars = [];

    /**
     * AbstractRepository constructor.
     *
     * @param Http    $http    The HTTP client.
     * @param Factory $factory The parts factory.
     * @param array   $vars    An array of variables used for the endpoint.
     */
    public function __construct(Http $http, Factory $factory, array $vars = [])
    {
        $this->http = $http;
        $this->factory = $factory;
        $this->vars = $vars;
        $this->browser = $factory->lorhondel->browser;

        parent::__construct([], $this->discrim, $this->class);
    }

    /**
     * Freshens the repository collection.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function freshen()
    {
        if (! isset($this->endpoints['all'])) {
            return \React\Promise\reject(new \Exception('You cannot freshen this repository.'));
        }

        $endpoint = new Endpoint($this->endpoints['all']);
        $endpoint->bindAssoc($this->vars);

        return $this->http->get($endpoint)->then(function ($response) {
            $this->fill([]);

            foreach ($response as $value) {
                $value = array_merge($this->vars, (array) $value);
                $part = $this->factory->create($this->class, $value, true);

                $this->push($part);
            }

            return $this;
        });
    }

    /**
     * Builds a new, empty part.
     *
     * @param array $attributes The attributes for the new part.
     * @param bool  $created
     *
     * @return Part       The new part.
     * @throws \Exception
     */
    public function create(array $attributes = [], bool $created = false): Part
    {
        $attributes = array_merge($attributes, $this->vars);

        return $this->factory->create($this->class, $attributes, $created);
    }

    /**
     * Attempts to save a part to the Lorhondel servers.
     *
     * @param Part $part The part to save.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function save(Part $part)
    {
        if ($part->created) {
            if (! isset($this->endpoints['update'])) {
                return \React\Promise\reject(new \Exception('You cannot update this part.'));
            }

            $method = 'patch';
            $endpoint = new Endpoint($this->endpoints['update']);
            $endpoint->bindAssoc(array_merge($part->getRepositoryAttributes(), $this->vars));
            $attributes = $part->getUpdatableAttributes();
        } else {
            if (! isset($this->endpoints['create'])) {
                return \React\Promise\reject(new \Exception('You cannot create this part.'));
            }

            $method = 'post';
            $endpoint = new Endpoint($this->endpoints['create']);
            $endpoint->bindAssoc(array_merge($part->getRepositoryAttributes(), $this->vars));
            $attributes = $part->getCreatableAttributes();
        }

        return $this->http->{$method}($endpoint, $attributes)->then(function ($response) use (&$part) {
            $part->fill((array) $response);
            $part->created = true;
            $part->deleted = false;

            $this->push($part);

            return $part;
        });
    }

    /**
     * Attempts to delete a part on the Lorhondel servers.
     *
     * @param Part|snowflake $part The part to delete.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function delete($part): ExtendedPromiseInterface
    {
        if (! ($part instanceof Part)) {
            $part = $this->factory->part($this->class, [$this->discrim => $part], true);
        }

        if (! $part->created) {
            return \React\Promise\reject(new \Exception('You cannot delete a non-existant part.'));
        }

        if (! isset($this->endpoints['delete'])) {
            return \React\Promise\reject(new \Exception('You cannot delete this part.'));
        }

        $endpoint = new Endpoint($this->endpoints['delete']);
        $endpoint->bindAssoc(array_merge($part->getRepositoryAttributes(), $this->vars));

        return $this->http->delete($endpoint)->then(function ($response) use (&$part) {
            $part->created = false;

            return $part;
        });
    }

    /**
     * Returns a part with fresh values.
     *
     * @param Part $part The part to get fresh values.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function fresh(Part $part): ExtendedPromiseInterface
    {
        if (! $part->created) {
            return \React\Promise\reject(new \Exception('You cannot get a non-existant part.'));
        }

        if (! isset($this->endpoints['get'])) {
            return \React\Promise\reject(new \Exception('You cannot get this part.'));
        }

        $endpoint = new Endpoint($this->endpoints['get']);
        $endpoint->bindAssoc(array_merge($part->getRepositoryAttributes(), $this->vars));

        return $this->http->get($endpoint)->then(function ($response) use (&$part) {
            $part->fill((array) $response);

            return $part;
        });
    }

    /**
     * Gets a part from the repository or Lorhondel servers.
     *
     * @param string $id    The ID to search for.
     * @param bool   $fresh Whether we should skip checking the cache.
     *
     * @return ExtendedPromiseInterface
     * @throws \Exception
     */
    public function fetch(string $id, bool $fresh = false): ExtendedPromiseInterface
    {
        if (! $fresh && $part = $this->get($this->discrim, $id)) {
            return \React\Promise\resolve($part);
        }

        if (! isset($this->endpoints['get'])) {
            return \React\Promise\resolve(new \Exception('You cannot get this part.'));
        }

        $part = $this->factory->create($this->class, [$this->discrim => $id]);
        $endpoint = new Endpoint($this->endpoints['get']);
        $endpoint->bindAssoc(array_merge($part->getRepositoryAttributes(), $this->vars));

        return $this->http->get($endpoint)->then(function ($response) {
            $part = $this->factory->create($this->class, array_merge($this->vars, (array) $response), true);
            $this->push($part);

            return $part;
        });
    }

    /**
     * Handles debug calls from var_dump and similar functions.
     *
     * @return array An array of attributes.
     */
    public function __debugInfo(): array
    {
        return $this->jsonSerialize();
    }
}
