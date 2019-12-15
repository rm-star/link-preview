<?php

namespace Dusterio\LinkPreview\Parsers;

use Dusterio\LinkPreview\Contracts\LinkInterface;
use Dusterio\LinkPreview\Contracts\PreviewInterface;
use Dusterio\LinkPreview\Contracts\ReaderInterface;
use Dusterio\LinkPreview\Contracts\ParserInterface;
use Dusterio\LinkPreview\Exceptions\ConnectionErrorException;
use Dusterio\LinkPreview\Models\Link;
use Dusterio\LinkPreview\Readers\HttpReader;
use Dusterio\LinkPreview\Models\MediaPreview;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ImgUrParser
 */
class ImgUrParser extends BaseParser implements ParserInterface
{
     /*Url validation pattern 
     */
    /* const PATTERN = '/(?>https?:)?\/\/(\w+\.)?imgur\.com\/(\S*)(\.[a-zA-Z]{3})/';*/
    const PATTERN = '/(?>https?:)?\/\/(?:\w+\.)?imgur\.com\/(\S*)/';


    /**
     * Smaller images will be ignored
     * @var int
     */
    private $imageMinimumWidth = 300;
    private $imageMinimumHeight = 300;

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
        return 'imgur';
    }


    /**
     * @param int $width
     * @param int $height
     */
    public function setMinimumImageDimension($width, $height)
    {
        $this->imageMinimumWidth = $width;
        $this->imageMinimumHeight = $height;
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
                                                                                                 
        $full_link_id = $matches[1];                                                             
        $link_id = explode( ".", $full_link_id);                                                 
                                                                                                 
                                                                                                 
        $width = $this->width;                                                                   
        $height = $this->height;                                                                 
        $this->getPreview()                                                                      
            ->setId($link_id[0])                                                                 
            ->setEmbed(                                                                          
'<blockquote class="imgur-embed-pub" lang="en" data-id="'.$this->getPreview()->getId().'"><a href="//imgur.com/'.$this->getPreview()->getId().'"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>'
            );                                                                                                                                                                      
                                                                                                                                                                                    
        return $this;
    }

    /**
     * @param LinkInterface $link
     * @return array
     */
    protected function parseImage(LinkInterface $link)
    {
        return [
            'cover' => $link->getEffectiveUrl(),
            'images' => [
                $link->getEffectiveUrl()
            ]
        ];
    }

}
