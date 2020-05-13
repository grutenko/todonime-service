<?php
/**
 * Copyright (c) 2020. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Grutenko\Shikimori\Entity;


/**
 * @property mixed|null id
 * @property mixed|null name
 */
class Genre extends Entity
{
    /**
     * @return string
     */
    public function getUrl(): string
    {
        $filteredName = str_replace(' ', '-', $this->name);
        return "https://shikimori.one/animes/genre/{$this->id}-{$filteredName}";
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "[{$this->id}] {$this->name}\r\n";
    }
}