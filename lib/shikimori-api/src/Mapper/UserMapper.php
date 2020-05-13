<?php


namespace Grutenko\Shikimori\Mapper;


use RuntimeException;

class UserMapper extends Mapper
{
    /**
     * @return array
     * @throws RuntimeException
     */
    public function current(): array
    {
        if(!$this->api->tokenIsSet()) {
            throw new RuntimeException('Этот метод может быть вызван только после упешной OAuth2.0 вторизации.');
        }

        return $this->api->fetch('/users/whoami');
    }

    /**
     * @param int $rateId
     * @param int $episode
     * @return array|null
     */
    public function bumpEpisode($rateId, $episode): ?array
    {
        if(!$this->api->tokenIsSet()) {
            throw new RuntimeException('Этот метод может быть вызван только после упешной OAuth2.0 вторизации.');
        }

        $rate = $this->getRate($rateId);
        if($rate == null) {
            return null;
        }

        if($rate['episodes'] >= $episode) {
            return $rate;
        }

        return $this->api->fetch(
            "v2/user_rates/{$rateId}",
            [
                'episodes' => $episode
            ],
            'PATCH'
        );
    }

    /**
     * @param int|string $id
     * @return array|null
     */
    public function getRate( $id ): ?array
    {
        if(!$this->api->tokenIsSet()) {
            throw new RuntimeException('Этот метод может быть вызван только после упешной OAuth2.0 вторизации.');
        }

        $rate = $this->api->fetch("v2/user_rates/{$id}");
        if( isset($rate['error']) ) {
            return null;
        }

        return $rate;
    }

    /**
     * @param array $params
     * @return array|null
     */
    public function createRate(array $params): ?array
    {
        $rate = $this->api->fetch('v2/user_rates', $params, 'POST');
        if( isset($rate['error']) ) {
            return null;
        }

        return $rate;
    }
}