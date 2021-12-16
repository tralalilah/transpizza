<?php declare(strict_types=1);

namespace TransPizza\Twitter\Service;

use Carbon\Carbon;
use TransPizza\Twitter\TwitterApiV2Client;
use TransPizza\Twitter\ValueObject\TweetCollection;
use TransPizza\Twitter\ValueObject\Tweet;

class GetTweetRepliesService
{
    private TwitterApiV2Client $client;

    public function __construct(TwitterApiV2Client $client)
    {
        $this->client = $client;
    }

    public function getTweetReplies(int $tweetId, $endTime): TweetCollection
    {
        return $this->getPaginatedSearchResults($this->client->getTweet($tweetId), $endTime);
    }

    private function getPaginatedSearchResults(Tweet $tweet, Carbon $endTime): TweetCollection
    {
        $query = [
            'query' => 'conversation_id:' . $tweet->id(),
            'max_results' => 100,
            'start_time' => $tweet->toIso8601String(),
            'end_time' => $endTime->toIso8601String(),
            'expansions' => 'author_id',
            'tweet.fields' => 'created_at',
            'user.fields' => 'username',
        ];

        $next_token = '';
        $collection = TweetCollection::create();
        while(1) {
            $collection = $collection->merge($this->client->searchTweets($query, $next_token));
            if ($next_token === null) {
                break;
            }
        }
        return $collection;
    }
}
