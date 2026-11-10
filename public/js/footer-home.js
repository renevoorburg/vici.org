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

    let selectionHTML = `<h2>${window.selectionTitle}:</h2>`;
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

    // Scroll de highlights-scroll div naar boven
    if (highlightsScroll) {
        highlightsScroll.scrollTop = 0;
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
        selectionFunc: updateSelectionBox
    }

    if (window.viciBase) {
        mapOptions.baseUrl = window.viciBase;
    }
    mapObj = new ViciWidget('map', mapOptions);
});
