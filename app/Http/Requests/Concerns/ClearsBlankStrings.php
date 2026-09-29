<?php

namespace App\Http\Requests\Concerns;

trait ClearsBlankStrings
{
    /**
     * @param  list<string>  $keys
     */
    protected function nullifyBlankStrings(array $keys): void
    {
        $payload = [];

        foreach ($keys as $key) {
            if ($this->exists($key) && $this->input($key) === '') {
                $payload[$key] = null;
            }
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }
}
