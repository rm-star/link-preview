<?php

namespace Dusterio\LinkPreview\Parsers;

use Dusterio\LinkPreview\Contracts\LinkInterface;
use Dusterio\LinkPreview\Contracts\PreviewInterface;
use Dusterio\LinkPreview\Contracts\ReaderInterface;
use Dusterio\LinkPreview\Contracts\ParserInterface;
use Dusterio\LinkPreview\Exceptions\ConnectionErrorException;
use Dusterio\LinkPreview\Models\Link;
use Dusterio\LinkPreview\Readers\HttpReader;
use Dusterio\LinkPreview\Models\HtmlPreview;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ImgUrParser
 */
class ImgUrParser extends BaseParser implements ParserInterface
{
     /*Url validation pattern 
     */
    const PATTERN = '/(?>https?:)?\/\/(?:\w+\.)?imgur\.com\/(\S*)/';


    /**
     * Supported HTML tags
     *
     * @var array
     */
    private $tags = [
        'cover' => [
            ['selector' => 'meta[property="twitter:image"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:image"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="image"]', 'attribute' => 'content'],
        ],

        'title' => [
            ['selector' => 'meta[property="twitter:title"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:title"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="name"]', 'attribute' => 'content'],
            ['selector' => 'title']
        ],

        'description' => [
            ['selector' => 'meta[property="twitter:description"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:description"]', 'attribute' => 'content'],
            ['selector' => 'meta[itemprop="description"]', 'attribute' => 'content'],
            ['selector' => 'meta[name="description"]', 'attribute' => 'content'],
        ],

        'video' => [
            ['selector' => 'meta[property="twitter:player:stream"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:video"]', 'attribute' => 'content'],
        ],

        'videoType' => [
            ['selector' => 'meta[property="twitter:player:stream:content_type"]', 'attribute' => 'content'],
            ['selector' => 'meta[property="og:video:type"]', 'attribute' => 'content'],
        ],
    ];

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
        $this->setPreview($preview ?: new HtmlPreview());
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
        $link = $this->readLink($link);

        if (!$link->isUp()) throw new ConnectionErrorException();

        if ($link->isHtml()) {
            $this->getPreview()->update($this->parseHtml($link));
        } else if ($link->isImage()) {
            $this->getPreview()->update($this->parseImage($link));
        }

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

    /**
     * Extract required data from html source
     * @param LinkInterface $link
     * @return array
     */
    protected function parseHtml(LinkInterface $link)
    {
        $images = [];

        try {
            $parser = new Crawler();
	    $parser->addHtmlContent($link->getContent());

            // Parse all known tags
            foreach($this->tags as $tag => $selectors) {
                foreach($selectors as $selector) {
                    if ($parser->filter($selector['selector'])->count() > 0) {
                        if (isset($selector['attribute'])) {
                            ${$tag} = $parser->filter($selector['selector'])->first()->attr($selector['attribute']);
                        } else {
                            ${$tag} = $parser->filter($selector['selector'])->first()->text();
                        }

                        break;
                    }
                }

                // Default is empty string
                if (!isset(${$tag})) ${$tag} = '';
            }

            // Parse all images on this page
            foreach($parser->filter('img') as $image) {
                if (!$image->hasAttribute('src')) continue;
                if (filter_var($image->getAttribute('src'), FILTER_VALIDATE_URL) === false) continue;

                // This is not bulletproof, actual image maybe bigger than tags
                if ($image->hasAttribute('width') && $image->getAttribute('width') < $this->imageMinimumWidth) continue;
                if ($image->hasAttribute('height') && $image->getAttribute('height') < $this->imageMinimumHeight) continue;

                $images[] = $image->getAttribute('src');
            }
        } catch (\InvalidArgumentException $e) {
            // Ignore exceptions
        }

        $images = array_unique($images);

        preg_match(static::PATTERN, $link->getUrl(), $matches);                                             
                                                                                                            
        $full_link_id = $matches[1];                                                                        
        $link_id = explode( ".", $full_link_id);                                                            
        $link_id = str_replace('gallery/', 'a/', $link_id[0]);                                                            
                                                                                                            
                                                                                                            
        $width = $this->width;                                                                              
        $height = $this->height;                                                                            
                                                                                                                                                                                    
$cover = '<blockquote class="imgur-embed-pub" lang="en" data-id="'.$link_id.'"><a href="//imgur.com/'.$link_id.'"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>';
#$cover = '<blockquote class="imgur-embed-pub" lang="en" data-id="'.$full_link_id.'"><a href="//imgur.com/'.$full_link_id.'"></a></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>';
                                                                                                                                                                                    
        return compact('cover', 'title', 'description', 'images', 'video', 'videoType');
    }
}
