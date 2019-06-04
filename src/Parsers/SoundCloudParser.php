<?php

namespace Dusterio\LinkPreview\Parsers;

use Dusterio\LinkPreview\Contracts\LinkInterface;
use Dusterio\LinkPreview\Contracts\ReaderInterface;
use Dusterio\LinkPreview\Contracts\ParserInterface;
use Dusterio\LinkPreview\Contracts\PreviewInterface;
use Dusterio\LinkPreview\Readers\HttpReader;
use Dusterio\LinkPreview\Models\MediaPreview;

/**
 * Class SoundClourParser
 */
class SoundCloudParser extends BaseParser implements ParserInterface
{
    /**
     * Url validation pattern 
     */
    const PATTERN = '/((https:\/\/)|(http:\/\/)|(www.)|(m\.)|(\s))+(soundcloud.com\/)+[a-zA-Z0-9\-\.]+(\/)+[a-zA-Z0-9\-\.]+/';

    /**
     * @param ReaderInterface $reader
     * @param PreviewInterface $preview
     */
    public function __construct(ReaderInterface $reader = null, PreviewInterface $preview = null)
    {
        $this->setReader($reader ?: new HttpReader());
        $this->setPreview($preview ?: new MediaPreview());
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'soundcloud';
    }

    /**
     * @inheritdoc
     */
    public function canParseLink(LinkInterface $link)
    {
        return (preg_match(static::PATTERN, $link->getUrl()));
    }

    /**
     * @inheritdoc
     */
    public function parseLink(LinkInterface $link)
    {
        preg_match(static::PATTERN, $link->getUrl(), $matches);

        $width = $this->width;
        $height = $this->height;
        $this->getPreview()
            ->setEmbed(
                '<iframe width="'.$width.'" height="'.$height.'" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url='.urlencode($link->getUrl()).'&color=%230a2c5f&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>'
            );

        return $this;
    }
}
