<?php

namespace Dusterio\LinkPreview\Models;

use Dusterio\LinkPreview\Contracts\PreviewInterface;
use Dusterio\LinkPreview\Traits\HasExportableFields;
use Dusterio\LinkPreview\Traits\HasImportableFields;

/**
 * Class MediaLink
 */
class MediaPreview implements PreviewInterface
{
    use HasExportableFields;
    use HasImportableFields;

    /**
     * @var string $embed Media embed code
     */
    private $embed;

    /**
     * @var string $video Url to video
     */
    private $video;

    /**
     * @var string $id Media identification code
     */
    private $id;

    /**
     * @var array
     */
    private $fields = [
        'embed',
        'id'
    ];
}
