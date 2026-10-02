<?php

declare(strict_types=1);

namespace SilverShop\ProductGallery\Model;

use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;

/**
 * A single image in a product (or variation) gallery: an image plus a caption and a sort position, attached to its
 * owner through a polymorphic has_one so one class serves both {@see \SilverShop\Page\Product} and
 * {@see \SilverShop\Model\Variation\Variation}.
 *
 * @property string $Title
 * @property int    $Sort
 * @property int    $ParentID
 * @property string $ParentClass
 * @property int    $ImageID
 * @method DataObject Parent()
 * @method Image Image()
 */
class GalleryItem extends DataObject
{
    private static string $table_name = 'SilverShop_GalleryItem';

    private static string $singular_name = 'Gallery Image';

    private static string $plural_name = 'Gallery Images';

    private static string $default_sort = 'Sort ASC';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        // Polymorphic: a Product or a Variation.
        'Parent' => DataObject::class,
        'Image' => Image::class,
    ];

    private static array $owns = [
        'Image',
    ];

    public function summaryFields(): array
    {
        return [
            'Image.StripThumbnail' => _t(self::class . '.ImageColumn', 'Image'),
            'Title' => _t(self::class . '.CaptionColumn', 'Caption'),
        ];
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID', 'ParentClass']);
        $fields->dataFieldByName('Title')?->setTitle(_t(self::class . '.Caption', 'Caption (alt text)'));

        return $fields;
    }
}
