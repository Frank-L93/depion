<?php

namespace App;

interface Feedable
{
    /**
     * @return array|\Spatie\Feed\FeedItem
     */
    public function toFeedItem();
}
