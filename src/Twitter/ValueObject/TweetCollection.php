<?php declare(strict_types=1);

namespace TransPizza\Twitter\ValueObject;

use JsonSerializable;
use Ramsey\Collection\Collection;
use TransPizza\Twitter\ValueObject\Tweet;

class TweetCollection implements JsonSerializable
{
    private Collection $collection;

    public static function create(): self
    {
        return new self([]);
    }

    private function __construct(array $array)
    {
        $this->collection = new Collection(Tweet::class, $array);
    }

    public function add(Tweet $tweet): void
    {
        $this->collection->add($tweet);
    }

    public function merge(TweetCollection $tweetCollection): TweetCollection
    {
        $return = new self([]);
        $return->collection = $this->collection->merge($tweetCollection->collection);
        return $return;
    }

    public function takeRandom(int $count): TweetCollection
    {
        if ($count > count($this->collection)) {
            throw new \Exception('Cannot pick ' . $count . ' items from collection with only ' . count($this->collection) .' members');
        }

        return $this->getByKeys(array_rand($this->collection->toArray(), $count));
    }

    private function getByKeys(array $keys): TweetCollection
    {
        return new self(array_intersect_key($this->toArray(), array_flip($keys)));
    }

    public function toArray(): array
    {
        return $this->collection->toArray();
    }

    public function jsonSerialize(): array
    {
        return $this->collection->map(fn (Tweet $tweet) => $tweet->jsonSerialize())->toArray();
    }
}
