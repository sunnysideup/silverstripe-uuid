<?php

namespace Sunnysideup\UUDI\Api;

class HashCreator
{
    private const CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int).
     *
     * @param int $length How many characters do we want?
     */
    public static function generate_hash(int $length = 64): string
    {
        $pieces = [];
        $max = mb_strlen(self::CHARS, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = self::CHARS[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    public static function generate_hash_simple(int $length = 64): string
    {
        return bin2hex(random_bytes($length));
    }
}
