<link rel="stylesheet" href="/js/photoswipe/dist/photoswipe.css">
<link rel="stylesheet" href="/js/photoswipe-dynamic-caption-plugin/photoswipe-dynamic-caption-plugin.css">
<script type="module">
import PhotoSwipeLightbox from '/js/photoswipe/dist/photoswipe-lightbox.esm.js';
import PhotoSwipeDynamicCaption from '/js/photoswipe-dynamic-caption-plugin/dist/photoswipe-dynamic-caption-plugin.esm.min.js';

const lightbox = new PhotoSwipeLightbox({
  gallery: '#my-gallery',
  children: 'a',
  bgOpacity: 1.0,
  pswpModule: () => import('/js/photoswipe/dist/photoswipe.esm.js'),
});

const captionPlugin = new PhotoSwipeDynamicCaption(lightbox, {
    type: 'auto',
    captionContent: (slide) => {
        const el = slide.data.element.querySelector('img')
        let html = el.getAttribute('alt') + '<br>'
        html += el.getAttribute('data-caption-creator') + ', ' + el.getAttribute('data-caption-license') + '<br>'
        html += '[ <a href="' + el.getAttribute('data-caption-link') + '">{$more_info_label|default:"more info"}</a> ]'
        return html;
    }
});

lightbox.init();
</script>