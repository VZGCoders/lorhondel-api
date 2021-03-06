<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel;

/**
 * Represents a rate-limit given by Lorhondel.
 *
 * @author David Cole <david.cole1340@gmail.com>
 * @author Valithor Obsidion <valzargaming@gmail.com>
 */
class RateLimit
{
    /**
     * Whether the rate-limit is global.
     *
     * @var bool
     */
    protected $global;

    /**
     * Time in seconds of when to retry after.
     *
     * @var float
     */
    protected $retry_after;

    /**
     * Rate limit constructor.
     *
     * @param bool  $global
     * @param float $retry_after
     */
    public function __construct(bool $global, float $retry_after)
    {
        $this->global = $global;
        $this->retry_after = $retry_after;
    }

    /**
     * Gets the global parameter.
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->global;
    }

    /**
     * Gets the retry after parameter.
     *
     * @return float
     */
    public function getRetryAfter(): float
    {
        return $this->retry_after;
    }

    /**
     * Converts a rate-limit to a user-readable string.
     *
     * @return string
     */
    public function __toString()
    {
        return 'RATELIMIT '.($this->global ? 'Global' : 'Non-global').', retry after '.$this->retry_after.' s';
    }
}
