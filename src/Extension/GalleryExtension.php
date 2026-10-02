<?php

declare(strict_types=1);

namespace SilverShop\ProductGallery\Extension;

use Colymba\BulkUpload\BulkUploader;
use SilverShop\Model\Variation\Variation;
use SilverShop\ProductGallery\Model\GalleryItem;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Adds a sortable image gallery to the decorated class. Applied to both {@see \SilverShop\Page\Product} and
 * {@see \SilverShop\Model\Variation\Variation}; the gallery items attach through {@see GalleryItem}'s polymorphic
 * Parent relation, so one relation definition serves both owners.
 *
 * @extends Extension<DataObject>
 * @method HasManyList<GalleryItem> GalleryItems()
 */
class GalleryExtension extends Extension
{
    private static array $has_many = [
        'GalleryItems' => GalleryItem::class . '.Parent',
    ];

    private static array $owns = [
        'GalleryItems',
    ];

    private static array $cascade_deletes = [
        'GalleryItems',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $owner = $this->getOwner();

        // A has_many needs the owner to exist before rows can be attached to it.
        if (!$owner->isInDB()) {
            $this->addGalleryField($fields, LiteralField::create(
                'GalleryItemsSaveFirst',
                '<p class="message notice">'
                . _t(self::class . '.SaveFirst', 'Save first, then add gallery images here.')
                . '</p>'
            ));
            return;
        }

        $config = GridFieldConfig_RecordEditor::create();
        // Drag-to-reorder the rows (writes GalleryItem.Sort).
        $config->addComponent(GridFieldOrderableRows::create('Sort'));
        // Multi-file drag-and-drop: each dropped file creates a GalleryItem with its Image set.
        $config->addComponent(new BulkUploader('Image'));

        $this->addGalleryField($fields, GridField::create(
            'GalleryItems',
            _t(self::class . '.GalleryImages', 'Gallery images'),
            $owner->GalleryItems(),
            $config
        ));
    }

    /**
     * All images for this owner: the primary Image (if set) followed by the sorted gallery images. A variation with
     * no gallery of its own falls back to its product's gallery.
     *
     * @return ArrayList<Image>
     */
    public function getAllImages(): ArrayList
    {
        $owner = $this->getOwner();
        $images = ArrayList::create();

        $primary = $owner->hasMethod('Image') ? $owner->Image() : null;
        if ($primary && $primary->exists()) {
            $images->push($primary);
        }

        foreach ($owner->GalleryItems() as $item) {
            $image = $item->Image();
            if ($image && $image->exists()) {
                $images->push($image);
            }
        }

        if ($images->count() === 0 && $owner instanceof Variation) {
            $product = $owner->Product();
            if ($product && $product->exists()) {
                return $product->getAllImages();
            }
        }

        return $images;
    }

    private function addGalleryField(FieldList $fields, FormField $field): void
    {
        // Product has a tabbed field list (place on the Images tab); Variation's is flat (just append).
        if ($fields->hasTabSet()) {
            $fields->addFieldToTab('Root.Images', $field);
        } else {
            $fields->push($field);
        }
    }
}
