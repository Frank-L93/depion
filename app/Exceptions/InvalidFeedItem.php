<?php

namespace App\Exceptions;

class InvalidFeedItem
{



    public static function missingField(\App\FeedItem $param, string $requiredField)
    {
        throw new \InvalidArgumentException("The feed item is missing the required field:
{
$requiredField
}

");
    }

    public static function notAFeedItem(\Spatie\Feed\FeedItem|array $feedItem)
    {
        throw new \InvalidArgumentException("The feed item is not a feed item");
    }

    public static function notFeedable(mixed $feedable)
    {
        throw new \InvalidArgumentException("The feedable is not feedable");
    }
}
