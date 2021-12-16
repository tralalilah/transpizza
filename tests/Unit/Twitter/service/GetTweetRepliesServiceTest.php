<?php declare(strict_types=1);

namespace Tests\Unit\Twitter\service;

use Carbon\Carbon;
use Tests\TestCase;
use TransPizza\Twitter\Service\GetTweetRepliesService;
use TransPizza\Twitter\TwitterApiV2Client;

class GetTweetRepliesServiceTest extends TestCase
{
    public function testGetRepliesForTweet(): void
    {
        $twitter = config('services.twitter');
        $service = new GetTweetRepliesService(new TwitterApiV2Client(
            $twitter['consumer_key'],
            $twitter['consumer_secret'],
            $twitter['access_token'],
            $twitter['access_token_secret'],
            $twitter['bearer_token']
        ));
        $results = $service->getTweetReplies(1471481137306480647, Carbon::parse('2021-12-16 09:00:00', 'America/Chicago'));
        $winners = $results->takeRandom(5);
        dump($winners->jsonSerialize());
        self::assertTrue(count($winners->toArray()) === 5);
    }
}
