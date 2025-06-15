var mapObj;

function trl(key) {
    if (window.viciTranslations && window.viciTranslations[key]) {
        return window.viciTranslations[key];
    }
    return key;
}

function iconHtml(icon_type) {    
    const iconTitle = trl(`markerdef.${icon_type}`);
    return `<div class="marker-icon" data-icon-type="${icon_type}" title="${iconTitle}"></div>`
}

function updateSelectionBox(feature) {
    const selectionBox = document.getElementById('selectionbox-content');
    if (feature === null || feature === undefined) {
        selectionBox.innerHTML = '';
        return;
    }

    const title = feature.title || 'Onbekende locatie';
    const summary = feature.html || '';
    const id = feature.id || '';
    const hasImage = feature.picture && feature.picture !== '';

    let selectionHTML = '';
    if (hasImage) {
        const imageUrl = `https://images.vici.org/crop/w220xh124${feature.picture}`;
        selectionHTML = `
                <div class="relative">
                    <img src="${imageUrl}" loading="lazy" class="highlight-image" alt="${title}" />
                    <div class="absolute selection-overlay">
                        <div class="selection-overlay-content">
                            <div>
                                <div class="font-semibold">${title}</div>
                                <p>${summary} ${id ? `[&nbsp;<a href="/vici/${id}" class="underline">${trl('more')}</a>&nbsp;]` : ''}</p>
                            </div>
                            <div class="centered-flex">
                                ${iconHtml(feature.kind || 1)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
    } else {
        selectionHTML = `
                <div class="selection-no-image">
                    <div class="selection-overlay-content">
                        <div>
                            <div class="font-semibold">${title}</div>
                            <p>${summary} ${id ? `[<a href="/vici/${id}" class="underline">${trl('more')}</a>]` : ''}</p>
                        </div>
                        <div class="centered-flex">
                            ${iconHtml(feature.kind || 1)}
                        </div>
                    </div>
                </div>
            `;
    }
    selectionBox.innerHTML = selectionHTML;

    // Scroll de highlights-scroll div naar boven
    const highlightsScroll = document.querySelector('.highlights-scroll');
    if (highlightsScroll) {
        highlightsScroll.scrollTop = 0;
    }
}

function updateHighlightsBox(highlights) {
    const highlightsContainer = document.getElementById('highlights-items');

    if (!highlights || highlights.length === 0) {
        highlightsContainer.innerHTML = '<div class="empty-message"></div>';
        return;
    }

    let highlightHTML = '';
    for (let i = 0; i < highlights.length; i++) {
        const highlight = highlights[i];
        const title = highlight.properties.title || 'Onbekende locatie';
        const summary = highlight.properties.summary || '';
        const imageUrl = highlight.properties.img
            ? `https://images.vici.org/crop/w220xh124${highlight.properties.img}`
            : 'https://images.vici.org/crop/w220xh124/uploads/no_image.png';
        const id = highlight.properties.id || '';

        highlightHTML += `
            <div class="highlight-box">
                <img src="${imageUrl}" class="highlight-image" alt="${title}" />
                <div class="highlight-info">
                    <div class="highlight-content">
                        <div class="font-semibold">${title}</div>
                        <div>
                            ${summary}
                            ${id ? `<a href="javascript:void(0)" onclick="mapObj.selectMarkerAndPan('${id}'); return false;" class="underline">[${trl('show on map')}]</a>` : ''}</div>
                    </div>
                    <div class="centered-flex">
                        ${iconHtml(highlight.properties.kind || 1)}
                    </div>
                </div>
            </div>`;

    }
    highlightsContainer.innerHTML = highlightHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    mapObj = new ViciWidget('map',
        {
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

            viciToken: '20E2ADF5AB',
            showFilter: true,
            highlights: 6,
            lang: "nl",
            setUrl: true,
            showScale: "metric",
            moveHere: true,
            highlightFunc: updateHighlightsBox,
            selectionFunc: updateSelectionBox
        }
    );
});
