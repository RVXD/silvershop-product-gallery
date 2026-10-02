<?php

declare(strict_types=1);

namespace SilverShop\ProductGallery\Tests;

use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverShop\ProductGallery\Extension\GalleryExtension;
use SilverShop\ProductGallery\Model\GalleryItem;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Model\List\ArrayList;

class GalleryTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testExtensionAppliedToProductAndVariation(): void
    {
        $this->assertTrue(
            Product::has_extension(GalleryExtension::class),
            'Product should have the gallery extension'
        );
        $this->assertTrue(
            Variation::has_extension(GalleryExtension::class),
            'Variation should have the gallery extension'
        );
    }

    public function testGalleryItemsAreSortedAndParentIsPolymorphic(): void
    {
        $product = Product::create(['Title' => 'Gallery Product', 'URLSegment' => 'gallery-product']);
        $product->write();

        $second = GalleryItem::create(['Title' => 'Second', 'Sort' => 2]);
        $second->ParentID = $product->ID;
        $second->ParentClass = Product::class;
        $second->write();

        $first = GalleryItem::create(['Title' => 'First', 'Sort' => 1]);
        $first->ParentID = $product->ID;
        $first->ParentClass = Product::class;
        $first->write();

        $items = $product->GalleryItems();
        $this->assertCount(2, $items, 'Both gallery items belong to the product');
        $this->assertSame('First', $items->first()->Title, 'Gallery items come back in Sort ASC order');
        $this->assertInstanceOf(
            Product::class,
            $first->Parent(),
            'The polymorphic Parent resolves back to the owning Product'
        );
    }

    public function testGetAllImagesReturnsArrayListAndVariationFallsBackWithoutError(): void
    {
        $product = Product::create(['Title' => 'P', 'URLSegment' => 'p']);
        $product->write();

        $variation = Variation::create(['InternalItemID' => 'V1']);
        $variation->ProductID = $product->ID;
        $variation->write();

        // No real image files in the test, so the lists are empty — we assert the contract (type) and that the
        // variation→product fallback branch runs without error when the variation has no gallery of its own.
        $this->assertInstanceOf(ArrayList::class, $product->getAllImages());
        $this->assertInstanceOf(ArrayList::class, $variation->getAllImages());
    }
}
