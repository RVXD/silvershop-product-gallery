<%-- Product / variation image gallery: one large image with a thumbnail grid below it. Loops $AllImages (the
     primary Image followed by the sorted gallery images; a variation with no gallery of its own falls back to its
     product's images). No JavaScript — each thumbnail links to the full-size image. Drop this in place of the
     standard product image, e.g. <% include SilverShop\Includes\GalleryImages %>. Override it to restyle. --%>
<% if $AllImages %>
<style>
    .product-gallery__main { margin: 0 0 12px; }
    .product-gallery__main img { display: block; width: 100%; height: auto; }
    .product-gallery__grid { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 8px; }
    .product-gallery__grid .product-gallery__item { width: 72px; margin: 0; padding: 0; list-style: none; }
    .product-gallery__grid a { display: block; }
    .product-gallery__grid img { display: block; width: 100%; height: auto; aspect-ratio: 1 / 1; object-fit: cover; }
    .product-gallery__thumb { cursor: pointer; }
</style>
<div class="product-gallery">
    <% with $AllImages.First %>
        <div class="product-gallery__main">
            <img src="$FitMax(1000,1000).URL" alt="$Up.Title" />
        </div>
    <% end_with %>
    <% if $AllImages.Count > 1 %>
        <ul class="product-gallery__grid">
            <% loop $AllImages %>
                <li class="product-gallery__item">
                    <a class="product-gallery__thumb<% if $First %> is-active<% end_if %>" href="$URL" data-large="$FitMax(1000,1000).URL">
                        <img src="$Fill(240,240).URL" alt="$Up.Title" loading="lazy" />
                    </a>
                </li>
            <% end_loop %>
        </ul>
        <script>
        (function () {
            var script = document.currentScript;
            var gallery = script && script.closest('.product-gallery');
            if (!gallery) {
                return;
            }
            var main = gallery.querySelector('.product-gallery__main img');
            var thumbs = gallery.querySelectorAll('.product-gallery__thumb');
            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (main) {
                        main.src = thumb.getAttribute('data-large');
                    }
                    thumbs.forEach(function (other) {
                        other.classList.remove('is-active');
                    });
                    thumb.classList.add('is-active');
                });
            });
        })();
        </script>
    <% end_if %>
</div>
<% else %>
    <div class="silvershop-product__no-image"><%t SilverShop\Page\Product.NoImage "no image" %></div>
<% end_if %>
