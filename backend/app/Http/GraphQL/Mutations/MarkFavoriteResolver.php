<?php

namespace App\Http\GraphQL\Mutations;

use App\Models\Account;
use App\Models\Favorite;
use App\Models\Timeline;
use App\Models\Tweet;
use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MarkFavoriteResolver
{

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param array $args The arguments that were passed into the field.
     * @param GraphQLContext|null $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     *
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        /** @var \App\Models\Account $account */
        $account = auth()->user();

        $favorite = null;

        /** @var Favorite $favorite */
        $favorite = $this->createFavorite($account, $args['tweet_id']);

        Tweet::find($args['tweet_id']);

        $this->addTweetToTimeline($account, $args['tweet_id']);

        $this->addTweetToFollowersTimeline($account, $args['tweet_id']);

        return !!$favorite;
    }

    protected function createFavorite(Account $account, $tweetId)
    {
        return Favorite::create([
            'account_id'  => $account->id,
            'tweet_id'    => $tweetId,
            'favorite_at' => Carbon::now()
        ]);
    }

    protected function addTweetToTimeline(Account $account, $tweetId)
    {
        return Timeline::create([
            'account_id' => $account->id,
            'tweet_id'   => $tweetId,
        ]);
    }

    protected function addTweetToFollowersTimeline(Account $account, $tweetId)
    {
        foreach ($account->followers as $follower) {
            Timeline::create([
                'account_id' => $follower->follower_account_id,
                'tweet_id'   => $tweetId,
            ]);
        }
    }
}