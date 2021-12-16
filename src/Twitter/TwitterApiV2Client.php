<?php declare(strict_types=1);

namespace TransPizza\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use TransPizza\Twitter\ValueObject\TweetCollection;
use TransPizza\Twitter\ValueObject\Tweet;

class TwitterApiV2Client
{
    private TwitterOAuth $connection;

    public function __construct(
        string $consumerKey,
        string $consumerSecret,
        string $accessToken,
        string $accessTokenSecret,
        string $bearerToken
    )
    {
        $connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        $connection->setBearer($bearerToken);
        $connection->setApiVersion('2');
        $this->connection = $connection;
    }

    public function quoteTweet(int $tweetId, string $text): Tweet
    {
        $params = [
            'quote_tweet_id' => $tweetId,
            'text' => $text
        ];
        $result = $this->connection->post('tweets', $params);
        return $this->getTweet($result->data['id']);
    }

    public function getTweet(int $tweetId): Tweet
    {
        $params = [
            'ids' => $tweetId,
            'expansions' => 'author_id',
            'tweet.fields' => 'created_at',
            'user.fields' => 'username',
        ];
        $result = $this->connection->get('tweets', $params);
        return Tweet::fromApiObjectArray($result);
    }

    public function searchTweets(array $query, string &$nextToken = '') :TweetCollection
    {
        $tokenArray = $nextToken !== '' ? ['next_token' => $nextToken] : [];

        $results = $this->connection->get('tweets/search/recent', $query + $tokenArray);
        if(property_exists($results, 'errors')) {
            dd($results->errors);
        }
        $return = $this->parseArrayResults($results);
        $nextToken = property_exists($results->meta, 'next_token') ? $results->meta->next_token : null;
        return $return;
    }

    private function parseArrayResults(\stdClass $results): TweetCollection
    {
        $users = $this->parseUsersToArray($results->includes->users);
        $collection = TweetCollection::create();
        foreach ($results->data as $result) {
            $collection->add(Tweet::fromTweetObjectAndUserObject($result, $users[$result->author_id]));
        }
        return $collection;
    }

    private function parseUsersToArray(array $users): array
    {
        $return = [];
        foreach($users as $user) {
            $return[$user->id] = $user;
        }
        return $return;
    }

}
