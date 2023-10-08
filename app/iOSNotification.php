<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Feedable;
use App\FeedItem;

class iOSNotification extends Model implements Feedable
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'summary', 'link','author',
    ];
    public function toFeedItem()
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->summary)
            ->updated($this->updated_at)
            ->link($this->link)
            ->author($this->author);
    }
    public static function getFeedItems()
    {
        return iOSNotification::all();
    }
}