document.addEventListener('DOMContentLoaded', function () {
    // Dynamische hoogte voor highlights-scroll
    function setHighlightsScrollHeight() {
        var highlightsScroll = document.querySelector('.highlights-scroll');
        var mainContent = document.querySelector('.maincontent');
        if (!highlightsScroll || !mainContent) return;

        var viewportHeight = window.innerHeight;
        var mainContentRect = mainContent.getBoundingClientRect();
        var availableHeight = viewportHeight - mainContentRect.top - 24; // 24px marge voor padding/bottom
        var mainContentHeight = mainContent.offsetHeight;
        var targetHeight = Math.max(availableHeight, mainContentHeight);
        targetHeight = Math.min(targetHeight, viewportHeight - 24);
        highlightsScroll.style.maxHeight = targetHeight + 'px';
        highlightsScroll.style.overflowY = 'auto';
    }
    setHighlightsScrollHeight();
    window.addEventListener('resize', setHighlightsScrollHeight);

    // Dynamische hoogte voor highlights-scroll
    function setHighlightsScrollHeight() {
        var highlightsScroll = document.querySelector('.highlights-scroll');
        var mainContent = document.querySelector('.maincontent');
        if (!highlightsScroll || !mainContent) return;

        // Bepaal beschikbare hoogte in viewport
        var viewportHeight = window.innerHeight;
        // Offset van bovenkant maincontent tot onderkant viewport
        var mainContentRect = mainContent.getBoundingClientRect();
        var availableHeight = viewportHeight - mainContentRect.top - 24; // 24px marge voor padding/bottom
        // Hoogte van maincontent zelf
        var mainContentHeight = mainContent.offsetHeight;
        // Kies de hoogste waarde die past
        var targetHeight = Math.max(availableHeight, mainContentHeight);
        // Maar niet meer dan de viewport
        targetHeight = Math.min(targetHeight, viewportHeight - 24);
        highlightsScroll.style.maxHeight = targetHeight + 'px';
        highlightsScroll.style.overflowY = 'auto';
    }
    setHighlightsScrollHeight();
    window.addEventListener('resize', setHighlightsScrollHeight);

    const tabList = document.querySelectorAll('#langSelBox li');
    const articles = document.querySelectorAll('article[id^="txt_"]');

    function select(el) {
        el.classList.add('selected');
        el.classList.remove('disabled');
    }

    function disable(el) {
        el.classList.remove('selected');
        el.classList.add('disabled');
    }

    function selectTab(lang) {
        tabList.forEach(tab => {
            if(tab.id === 'xt_' + lang) {
                select(tab);
            } else {
                disable(tab);
            }
        });
        articles.forEach(article => {
            if(article.id === 'txt_' + lang) {
                select(article);
            } else {
                disable(article);
            }
        });
    }

    tabList.forEach(tab => {
        tab.addEventListener('click', function () {
            selectTab(this.id.replace('xt_', ''));
        });
    });


    let mapOptions = {  
        defaultMap: "OSM",
        useMaps: ["AWMC", "OSM", "DARE", "ESRI"],
        extraMaps: {
            DARE: {
                name: 'Digital Atlas of the Roman Empire',
                url: "https://tiles.vici.org/imperium/{ldelim}z{rdelim}/{ldelim}x{rdelim}/{ldelim}y{rdelim}.png",
                attributions: '© <a href="http://dare.ht.lu.se/">Johan Åhlfeldt</a>',
                maxZoom: 11
            },
            ESRI: {
                name: 'Esri WorldImagery',
                url: "https://tiles.vici.org/world/{ldelim}z{rdelim}/{ldelim}y{rdelim}/{ldelim}x{rdelim}",
                attributions: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }
        },
        extraOverlays: {
            LIMESNL: {
                name: 'Limes NL',
                url: "https://tiles.vici.org/Limes/{ldelim}z{rdelim}/{ldelim}x{rdelim}/{ldelim}y{rdelim}.png",
                attributions: '© Olav Odé - CC BY',
                opacity: 0.8
            }
        },
        showFilter: true,
        highlights: 0,
        lang: window.sessionLanguage,
        viciToken: window.viciToken,
        center: { lat: window.lat, lng: window.lng },
        followFocus: true,
        focus: window.siteId,
        showScale: "metric"
    }

    if (window.viciBase) {
        mapOptions.baseUrl = window.viciBase;
    }
    mapObj = new ViciWidget('map', mapOptions);

    document.getElementById('myIcon')?.addEventListener('click', () => mapObj.panTo(window.siteId));



});