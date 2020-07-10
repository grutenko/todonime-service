<?php


namespace App\Action\Video;


use App\Action\Action;
use App\Helper\ResponseHelper;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SuggestVideo extends Action
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();

        $user = $request->getAttribute('user');
        $kind = ($user == null || !isset($user['kind']))
            ? 'sub'
            : $user['kind'];

        $collection = $this->mongodb->todonime->videos;

        $videos = $collection->find([
            'anime_id' => (int)$params['anime_id'],
            'episode' => (int)$params['episode']
        ])->toArray();

        if($videos == null && count($videos) == 0) {
            return ResponseHelper::notFound($response);
        }

        $kinds = array_column($videos, 'kind');

        if( !in_array($kind, $kinds) ) {
            $kind = array_intersect(['dub', 'sub', 'org'], array_unique($kinds))[0];
        }

        $videos = array_filter($videos, function($video) use($kind) {
            return $video['kind'] == $kind;
        });

        usort($videos, function($v1, $v2) {
            $rate = [
                'completed' => 1,
                'project_id' => 1,
                'lang_ru' => 1
            ];
            $sum = function($v) use ($rate) {
                $s = 0;
                if($v['completed']) {
                    $s += $rate['completed'];
                }
                if($v['project_id']) {
                    $s += $rate['project_id'];
                }
                if($v['language'] == 'russian' || $v['language'] == 'ru') {
                    $s += $rate['lang_ru'];
                }

                return $s;
            };

            return $sum($v2) - $sum($v1);
        });

        $selfHostedVideos = array_values(array_filter($videos, function($video) {
            return $video['domain'] == 'embed.todonime.ru';
        }));

        if(count($selfHostedVideos) > 0)
        {
            return ResponseHelper::success($response, ['video_id' => $selfHostedVideos[0]['_id']->__toString()]);
        }

        return ResponseHelper::success($response, ['video_id' => $videos[0]['_id']->__toString()]);
    }
}