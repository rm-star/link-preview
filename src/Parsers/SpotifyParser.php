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
class SpotifyParser extends BaseParser implements ParserInterface
{
    /**
     * Url validation pattern 
     */
    const TRACK_PATTERN = '/(https:\/\/|http:\/\/)open.spotify.com\/track\/([0-9a-zA-Z]{22})/';
    const PLAYLIST_PATTERN = '/(https:\/\/|http:\/\/)open.spotify.com\/[0-9a-zA-z]+\/([0-9a-zA-z]+)\/playlist\/([0-9a-zA-Z]{22})/';

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
        return 'spotify';
    }

    /**
     * @inheritdoc
     */
    public function canParseLink(LinkInterface $link)
    {
        return (preg_match(static::TRACK_PATTERN, $link->getUrl()) || preg_match(static::PLAYLIST_PATTERN, $link->getUrl()));
    }

    /**
     * @inheritdoc
     */
    public function parseLink(LinkInterface $link)
    {
        preg_match(static::TRACK_PATTERN, $link->getUrl(), $matches);
	
	if ( isset($matches[2]) ){
	    $width = $this->width;
	    $height = $this->height;
            $this->getPreview()
                ->setId($matches[2])
                ->setEmbed(
                    '<iframe src="https://open.spotify.com/embed/track/'.$this->getPreview()->getId().'" width="'.$width.'" height="'.$height.'" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>'
                );
        } else {
            preg_match(static::PLAYLIST_PATTERN, $link->getUrl(), $matches);
	
	    $width = $this->width;
	    $height = $this->height;
            $this->getPreview()
                ->setId($matches[3])
                ->setEmbed(
                    '<iframe src="https://open.spotify.com/embed/user/'.$matches[2].'/playlist/'.$matches[3].'" width="'.$width.'" height="'.$height.'" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>'
                );
        }

        return $this;
    }
}
