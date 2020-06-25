<?php


namespace App\Lib\PartnerVideo;


class ScreenshotGenerator
{
    /**
     * @var string
     */
    public $src;

    /**
     * ScreenshotGenerator constructor.
     * @param string $src
     */
    public function __construct(string $src)
    {
        if( !file_exists($src) )
        {
            throw new \RuntimeException($src . 'not found.');
        }
        $this->src = $src;
    }

    /**
     * @param string $dst
     * @return array
     */
    public function generate(string $dst)
    {
        if( !file_exists($dst) )
        {
            throw new \RuntimeException($dst . 'not found.');
        }

        echo `ffmpeg -i {$this->src} -hide_banner -stats -vf "fps=1/20,scale=280:-1" {$dst}/screen-%d.png`;

        $duration = (int)`ffprobe -i {$this->src} -show_format -v quiet | sed -n 's/duration=//p'`;
        $randFrame = rand(0, $duration);
        echo `ffmpeg -i {$this->src} -hide_banner -stats -ss {$randFrame} -vframes 1 {$dst}/preview.png`;

        $screenshots = [];
        for($i = 0; $i < $duration; $i += 20)
        {
            $screenshots[] = [
                'path' => $dst . '/screen-'.($i/20+1). '.png',
                'start' => (int)($i + 1),
                'end' => (int)($i + 20 < $duration ? $i+20 : $duration)
            ];
        }

        return [
            'screenshots' => $screenshots,
            'preview' => $dst. '/preview.png'
        ];
    }
}