<?php


namespace App\Twig;


use Twig\TwigFilter;

class Extension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('cdn', function($path) {
               return ($_ENV['CDN_URL'] ?: 'https://cdn.todonime.ru') . $path;
            })
        ];
    }
}