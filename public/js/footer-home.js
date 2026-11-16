var mapObj;

function trl(key) {
    if (window.viciTranslations && window.viciTranslations[key]) {
        return window.viciTranslations[key];
    }
    return key;
}

function esc(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function iconHtml(icon_type) {    
    const iconTitle = trl(`markerdef.${icon_type}`);
    return `<div class="marker-icon" data-icon-type="${icon_type}" title="${esc(iconTitle)}"></div>`
}

function updateSelectionBox(feature) {
    const selectionBox = document.getElementById('selectionbox-content');
    const highlightsScroll = document.querySelector('.highlights-scroll');
    if (feature === null || feature === undefined) {
        selectionBox.innerHTML = '';
        if (highlightsScroll) {
            highlightsScroll.classList.remove('has-selection');
        }
        return;
    }
    if (highlightsScroll) {
        highlightsScroll.classList.add('has-selection');
    }

    const title = esc(feature.title || 'Onbekende locatie');
    const summary = esc(feature.html || '');
    const id = String(feature.id || '');
    const hasImage = feature.picture && feature.picture !== '';
    const siteLink = `/vici/${esc(id)}`;
    const moreLink = id ? `<a href="${siteLink}" class="underline">[&nbsp;${esc(trl('more'))}&nbsp;]</a>` : '';
    const iconPart = id
        ? `<a href="${siteLink}" class="selection-link">${iconHtml(feature.kind || 1)}</a>`
        : iconHtml(feature.kind || 1);

    const closeLabel = esc(trl('Close'));
    const closeButton = `<button type="button" id="selection-close" class="selection-close" title="${closeLabel}" aria-label="${closeLabel}"><svg viewBox="0 0 18 18" width="18" height="18" aria-hidden="true"><circle cx="9" cy="9" r="8.5" fill="rgba(0,0,0,0.5)"/><path d="M5.5 5.5l7 7M12.5 5.5l-7 7" stroke="#fff" stroke-width="1.6" stroke-linecap="round" fill="none"/></svg></button>`;
    let selectionHTML = `<div class="selection-header"><h2>${window.selectionTitle}:</h2>${closeButton}</div>`;
    if (hasImage) {
        const imageUrl = `https://images.vici.org/crop/w440xh248${esc(feature.picture)}`;
        const imagePart = id
            ? `<a href="${siteLink}" class="selection-link"><img src="${imageUrl}" loading="lazy" class="highlight-image" alt="${title}" /></a>`
            : `<img src="${imageUrl}" loading="lazy" class="highlight-image" alt="${title}" />`;
        selectionHTML += `<div class="relative">${imagePart}
                    <div class="absolute selection-overlay">
                        <div class="selection-overlay-content">
                            <div>
                                <div class="font-semibold">${title} ${moreLink}</div>
                                <p class="selection-summary">${summary}</p>
                            </div>
                            <div class="centered-flex">
                                ${iconPart}
                            </div>
                        </div>
                    </div>
                </div>`;
    } else {
        selectionHTML += `
                <div class="selection-no-image">
                    <div class="selection-overlay-content">
                        <div>
                            <div class="font-semibold">${title} ${moreLink}</div>
                            <p>${summary}</p>
                        </div>
                        <div class="centered-flex">
                            ${iconPart}
                        </div>
                    </div>
                </div>`;
    }
    selectionBox.innerHTML = selectionHTML;

    const closeBtn = document.getElementById('selection-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            mapObj.deselectSelection();
        });
    }

    // Scroll de highlights-scroll div naar boven
    if (highlightsScroll) {
        highlightsScroll.scrollTop = 0;
    }
}

async function openSlideshow(bounds, zoom, filter, lang) {
    try {
        let url = `${window.viciBase || ''}/images.php?bounds=${bounds}&zoom=${zoom}`;
        if (filter) {
            url += `&era=${encodeURIComponent(filter.era)}&visibility=${encodeURIComponent(filter.visibility)}`;
        }
        if (lang) {
            url += `&lang=${encodeURIComponent(lang)}`;
        }
        const res = await fetch(url, {
            headers: { 'X-Vici-Token': window.viciToken }
        });
        if (!res.ok) {
            throw new Error(`images.php returned ${res.status}`);
        }
        const data = await res.json();
        if (!Array.isArray(data.images) || data.images.length === 0) {
            return;
        }

        const [{ default: PhotoSwipeLightbox }, { default: PhotoSwipeDynamicCaption }] = await Promise.all([
            import('/js/photoswipe/dist/photoswipe-lightbox.esm.js'),
            import('/js/photoswipe-dynamic-caption-plugin/dist/photoswipe-dynamic-caption-plugin.esm.min.js')
        ]);

        const items = data.images
            .filter(im => im.path)
            .map(im => {
                const hasDims = im.w > 0 && im.h > 0;
                return {
                    src: `https://images.vici.org/auto${im.path}`,
                    width: hasDims ? im.w : 1600,
                    height: hasDims ? im.h : 1200,
                    imageId: im.id,
                    siteId: im.siteId,
                    siteTitle: im.siteTitle || '',
                    kind: im.kind || 1,
                    imgTitle: im.title || '',
                    creator: im.creator || '',
                    license: im.license || ''
                };
            });
        if (items.length === 0) {
            return;
        }

        const lightbox = new PhotoSwipeLightbox({
            dataSource: items,
            bgOpacity: 1.0,
            pswpModule: () => import('/js/photoswipe/dist/photoswipe.esm.js')
        });

        if (data.truncated) {
            const notice = trl('Only %s randomly selected photos are shown').replace('%s', String(items.length));
            lightbox.on('uiRegister', function() {
                lightbox.pswp.ui.registerElement({
                    name: 'truncated-notice',
                    ariaLabel: notice,
                    order: 9,
                    isButton: false,
                    appendTo: 'root',
                    html: esc(notice)
                });
            });
        }

        new PhotoSwipeDynamicCaption(lightbox, {
            type: 'auto',
            captionContent: (slide) => {
                const d = slide.data;
                let html = '';
                if (d.siteId) {
                    html += `<div class="slideshow-site"><span class="marker-icon" data-icon-type="${d.kind}"></span> <a href="/vici/${esc(String(d.siteId))}">${esc(d.siteTitle)}</a></div>`;
                }
                if (d.imgTitle) {
                    html += esc(d.imgTitle) + '<br>';
                }
                const credits = [d.creator, d.license].filter(Boolean).join(', ');
                if (credits) {
                    html += esc(credits) + '<br>';
                }
                if (d.imageId) {
                    html += `[ <a href="/image/${esc(String(d.imageId))}">${esc(trl('more info'))}</a> ]`;
                }
                return html;
            }
        });

        lightbox.init();
        lightbox.loadAndOpen(0);
    } catch (e) {
        console.error('Slideshow failed:', e);
    }
}

function updateHighlightsBox(highlights) {
    const highlightsContainer = document.getElementById('highlights-items');
    const highlightsScroll = document.querySelector('.highlights-scroll');

    if (!highlights || highlights.length === 0) {
        highlightsContainer.innerHTML = '<div class="empty-message"></div>';
        if (highlightsScroll) {
            highlightsScroll.classList.remove('has-highlights');
        }
        return;
    }
    if (highlightsScroll) {
        highlightsScroll.classList.add('has-highlights');
    }

    let highlightHTML = '';
    for (let i = 0; i < highlights.length; i++) {
        const highlight = highlights[i];
        const title = esc(highlight.properties.title || 'Onbekende locatie');
        const summary = esc(highlight.properties.summary || '');
        const img = highlight.properties.img;
        const id = String(highlight.properties.id || '');
        const imageTag = img
            ? `<img src="https://images.vici.org/crop/w440xh248${esc(img)}" class="highlight-image highlight-map-link" data-marker-id="${esc(id)}" alt="${title}" />`
            : '';

        highlightHTML += `
            <div class="highlight-box">
                ${imageTag}
                <div class="highlight-info">
                    <div class="highlight-content">
                        <div class="font-semibold">${title}</div>
                        <div>
                            ${summary}
                            ${id ? `<button type="button" class="show-on-map-btn" data-marker-id="${esc(id)}">${esc(trl('show on map'))}</button>` : ''}</div>
                    </div>
                    <div class="centered-flex">
                        ${iconHtml(highlight.properties.kind || 1)}
                    </div>
                </div>
            </div>`;

    }
    highlightsContainer.innerHTML = highlightHTML;

    const showOnMapButtons = highlightsContainer.querySelectorAll('.show-on-map-btn, .highlight-map-link');
    showOnMapButtons.forEach(btn => {
        const showOnMap = function() {
            // in the stacked layout the map may be scrolled out of view
            document.getElementById('map')?.scrollIntoView({ behavior: 'smooth' });
            mapObj.selectMarkerAndPan(this.dataset.markerId);
        };
        btn.addEventListener('click', showOnMap);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    let mapOptions = {
        defaultMap: "OSM",
        useMaps: ["AWMC", "OSM", "DARE", "ESRI"],
        extraMaps: {
            DARE: {
                name: 'Digital Atlas of the Roman Empire',
                url: "https://tiles.vici.org/imperium/{z}/{x}/{y}.png",
                attributions: '© <a href="http://dare.ht.lu.se/">Johan Åhlfeldt</a>',
                maxZoom: 11
            },
            ESRI: {
                name: 'Esri WorldImagery',
                url: "https://tiles.vici.org/world/{z}/{y}/{x}",
                attributions: 'Tiles © Esri &mdash; and the GIS User Community'
            }
        },
        extraOverlays: {
            LIMESNL: {
                name: 'Limes NL',
                url: "https://tiles.vici.org/Limes/{z}/{x}/{y}.png",
                attributions: '© Olav Odé - CC BY',
                opacity: 0.8
            }
        },

        viciToken: window.viciToken,
        showFilter: true,
        highlights: 6,
        lang: window.sessionLanguage,
        setUrl: true,
        showScale: "metric",
        showUserGeolocation: true,
        panToUserGeolocation: true,
        highlightFunc: updateHighlightsBox,
        selectionFunc: updateSelectionBox,
        slideshowFunc: openSlideshow
    }

    if (window.viciBase) {
        mapOptions.baseUrl = window.viciBase;
    }
    mapObj = new ViciWidget('map', mapOptions);
});
