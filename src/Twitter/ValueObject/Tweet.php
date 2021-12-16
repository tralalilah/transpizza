<?php declare(strict_types=1);

namespace TransPizza\Twitter\ValueObject;

use Carbon\Carbon;

class Tweet implements \JsonSerializable
{

    public const TWITTER_URL = 'https://twitter.com';

    private int $tweetId;
    private int $authorId;
    private string $username;
    private string $text;
    private Carbon $createdAt;

    public static function fromTweetObjectAndUserObject(\stdClass $tweetObject, \stdClass $userObject): self
    {
        return new self(
            (int)$tweetObject->id,
            (int)$tweetObject->author_id,
            $userObject->username,
            $tweetObject->text,
            Carbon::parse($tweetObject->created_at)
        );
    }

    public static function fromApiObjectArray(\stdClass $object): self
    {
        if (count($object->data) !== 1){
            throw new \Exception('Invalid API object array');
        }

        $tweetData = $object->data[0];
        $userData = $object->includes->users[0];

        return self::fromTweetObjectAndUserObject($tweetData, $userData);
    }

    private function __construct(
        int $tweetId,
        int $authorId,
        string $username,
        string $text,
        Carbon $createdAt
    ){

        $this->tweetId = $tweetId;
        $this->authorId = $authorId;
        $this->username = $username;
        $this->text = $text;
        $this->createdAt = $createdAt;
    }

    public function id(): int
    {
        return $this->tweetId;
    }

    public function authorId(): int
    {
        return $this->authorId;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function url(): string
    {
        return self::TWITTER_URL . '/' . $this->username . '/status/' . $this->tweetId;
    }

    public function userProfileUrl(): string
    {
        return self::TWITTER_URL . '/' . $this->username;
    }

    public function toIso8601String(): string
    {
        return $this->createdAt->toIso8601String();
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->tweetId,
            'author_id' => $this->authorId,
            'username' => $this->username,
            'text' => $this->text,
            'created_at' => $this->toIso8601String(),
            'urls' => [
                'tweet' => $this->url(),
                'user' => $this->userProfileUrl()
            ]
        ];
    }
}
