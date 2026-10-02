# SilverShop Product Gallery

Sortable image galleries for [SilverShop](https://github.com/silvershop/silvershop-core) **products** and
**variations**. Core gives each product and each variation a single `has_one Image`; this adds an ordered set of
additional images on top of it.

**Backend only.** The module ships the data model, the CMS admin (drag-to-sort + per-image caption), and template
accessors — **no JavaScript or CSS**. You render the gallery in your own theme however you like (slider, lightbox,
zoom, grid, …).

## Requirements

- `silvershop/core` ^6 (SilverStripe CMS 6)
- PHP ^8.3
- `symbiote/silverstripe-gridfieldextensions` ^5 (drag-to-sort; pulled in by core)
- `colymba/gridfield-bulk-editing-tools` ^5.1 (multi-file bulk upload)

## Installation

```bash
composer require silvershop/product-gallery
```

Then run `dev/build?flush=1`.

## How it works

- A `GalleryItem` DataObject holds one `Image`, a `Title` (used as the caption / alt text) and a `Sort` position. It
  attaches to its owner through a **polymorphic `Parent` has_one**, so the same class serves both products and
  variations.
- A `GalleryExtension` is applied to `SilverShop\Page\Product` and `SilverShop\Model\Variation\Variation`, adding a
  `GalleryItems` has_many edited in a GridField with:
  - **drag-to-reorder** rows (`GridFieldOrderableRows`, writes `Sort`),
  - **multi-file bulk upload** — drag a batch of images onto the grid and each becomes a `GalleryItem`
    (`colymba` `BulkUploader`); edit captions / reorder afterwards,
  - add/edit/delete single records with a caption.
  - On a **product** the grid is on the **Images** tab; on a **variation** it's in its **edit form** (open the
    variation row to manage its images).
- Core's existing single `Image` stays as the **primary / featured** image; the gallery is additional.

## Rendering (your theme)

Each owner gets:

- `$GalleryItems` — the sorted gallery items (each has `$Image` and `$Title`).
- `$AllImages` — an `ArrayList` of `Image` objects: the primary `Image` (if set) followed by the gallery images. A
  variation with no gallery of its own falls back to its product's gallery.

The accessors return plain `Image` objects, so you size them yourself. A bare example:

```ss
<%-- Primary + gallery, as a simple list of images --%>
<% loop $AllImages %>
    <img src="$Fill(800,800).URL" alt="$Owner.Title">
<% end_loop %>

<%-- Or loop the items for per-image captions --%>
<% loop $GalleryItems %>
    <figure>
        <img src="$Image.Fill(800,800).URL" alt="$Title">
        <% if $Title %><figcaption>$Title</figcaption><% end_if %>
    </figure>
<% end_loop %>
```

For a variation's images on the product page, loop `$SelectedVariation.AllImages` (or whichever variation your theme
has selected).

## License

BSD-3-Clause.
