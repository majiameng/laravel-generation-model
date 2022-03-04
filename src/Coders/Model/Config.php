<?php

/**
 * Created by Cristian.
 * Date: 11/09/16 09:00 PM.
 */

namespace tinymeng\Laravel\Coders\Model;

use Illuminate\Support\Arr;
use tinymeng\Laravel\Meta\Blueprint;

class Config
{
    /**
     * @var array
     */
    protected $config;

    /**
     * ModelConfig constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @param \tinymeng\Laravel\Meta\Blueprint $blueprint
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(Blueprint $blueprint, $key, $default = null)
    {
        $priorityKeys = [
            "@connections.{$blueprint->connection()}.{$blueprint->table()}.$key",
            "@connections.{$blueprint->connection()}.{$blueprint->schema()}.$key",
            "@connections.{$blueprint->connection()}.$key",
            "{$blueprint->qualifiedTable()}.$key",
            "{$blueprint->schema()}.$key",
            "*.$key",
        ];

        foreach ($priorityKeys as $key) {
            $value = Arr::get($this->config, $key);

            if (!is_null($value)) {
                return $value;
            }
        }

        return $default;
    }
}
