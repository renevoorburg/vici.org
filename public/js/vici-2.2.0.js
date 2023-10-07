/**
 Copyright 2013-18, René Voorburg, rene@digitopia.nl

 This file is part of the Vici.org source.

 Vici.org source is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Vici.org  source is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
 */

/**

 2.0.0: 2018-05-24
 - complete rewrite using OpenLayers 4 ; no Google deps
 - functionally similar to 1.x, slightly altered call API

 2.0.1: 2018-05-25
 - followFocus prevents 'selected'-box to be shown, even when initializing
 - assures that the required (focus=) point are loaded
 - cleaned up url params for ajax requests
 - panTo iso reload when selected marker is clicked and mode is followFocus

 2.1.0: 2018-5-27
 - option 'setUrl' uses a #URL to store zoomlevel, center and selected marker

 2.1.1: 2018-5-28
 - option 'enableOverlays' sets session.overlays

 2.1.2: 2018-05-29
 - overlay visibility now has direct impact

 2.2.0: 2018-05-30
 - option < showScale: "unit" > added
 - let iso var

 */

function ViciWidget(element, options) {

    let baseUrl = window.location.hostname.match(/\.local$/) === '.local' ? window.location.protocol + '//vici.local' : window.location.protocol + '//vici.org';

    let lang =  (options.lang) ? options.lang : (navigator.language || navigator.userLanguage).substring(0, 2) ;
    if ($.inArray(lang,['de', 'en', 'fr', 'nl']) < 0) lang = 'en';
    let txt = {
        de: {
            selection: 'Selektion',
            more: 'weiter',
            show: 'anzeigen',
            Featured: 'Schaukasten',
            Adjust_view: 'Ansicht &auml;ndern',
            touristic_vs_archeological: 'Touristische oder arch&auml;ologische St&auml;tten',
            museums_etc: 'Museen und andere zeitgen&ouml;ssische St&auml;tten',
            museums_and_archeological: 'Sowohl zeitgen&ouml;ssische als r&ouml;mischen St&auml;tten',
            roman_only: 'Nur arch&auml;ologische St&auml;tten',
            visibility_of_sites: 'Nur sichtbare oder alle Objekte zeigen',
            visible_sites: 'Nur sichtbare Objekte',
            visible_and_invisible: 'Alle Objekte zeigen',
            Map_background: 'Hintergrund',
            satellite: 'Satellitbild',
            satellite_labels: 'Satellitebild mit Labels',
            pelagios: 'Digital Atlas of the Roman Empire',
            osm_contribs: 'OpenStreetMap-Mitwirkende'
        },
        en: {
            selection: 'Selection',
            more: 'more',
            show: 'show',
            Featured: 'Featured',
            Adjust_view: 'Adjust view settings',
            touristic_vs_archeological: 'Show tourist or archeological sites',
            museums_etc: 'Museums and other contemporary sites',
            museums_and_archeological: 'Both contemporary and Roman sites',
            roman_only: 'Ancient Roman only',
            visibility_of_sites: 'Visibility of sites to display',
            visible_sites: 'Visible sites',
            visible_and_invisible: 'Both visible and invisible sites',
            Map_background: 'Map background',
            satellite: 'Satellite',
            satellite_labels: 'Satellite with labels',
            pelagios: 'Digital Atlas of the Roman Empire',
            osm_contribs: 'OpenStreetMap contributors'
        },
        fr: {
            selection: 'Selection',
            more: 'plus',
            show: 'exposer',
            Featured: 'Pr&eacute;sent&eacute;',
            Adjust_view: 'Adjust view settings',
            touristic_vs_archeological: 'Show tourist or archeological sites',
            museums_etc: 'Museums and other contemporary sites',
            museums_and_archeological: 'Both contemporary and Roman sites',
            roman_only: 'Ancient Roman only',
            visibility_of_sites: 'Visibility of sites to display',
            visible_sites: 'Visible sites',
            visible_and_invisible: 'Both visible and invisible sites',
            Map_background: 'Map background',
            satellite: 'Satellite',
            satellite_labels: 'Satellite with labels',
            pelagios: 'Digital Atlas of the Roman Empire',
            osm_contribs: 'les contributeurs d’OpenStreetMap'
        },
        nl: {
            selection: 'Geselecteerd',
            more: 'meer',
            show: 'toon',
            Featured: 'Uitgelicht',
            Adjust_view: 'Wat te tonen',
            touristic_vs_archeological: 'Toeristisch of archeologisch',
            museums_etc: 'Musea en andere hedendaagse plekken',
            museums_and_archeological: 'Hedendaagse &eacute;n archeologische plekken',
            roman_only: 'Enkel Romeinse archeologische locaties',
            visibility_of_sites: 'Zichtbaarheid',
            visible_sites: 'Enkel zichtbare plaatsen of objecten',
            visible_and_invisible: 'Zichtbare en niet-zichtbare objecten',
            Map_background: 'Achtergrondkaart',
            satellite: 'Satelliet',
            satellite_labels: 'Satelliet met hedendaagse aanduidingen',
            pelagios: 'Digital Atlas of the Roman Empire',
            osm_contribs: 'OpenStreetMap-auteurs'
        }
    }[lang];

    if (!options.useMaps) {
        options.useMaps = ["OSM", "AWMC"];
    }

    let session = (sessionStorage.getItem('session'))  ? JSON.parse(sessionStorage.getItem('session')) : {
        selectedMarkerId: null,
        zoomlevel: options.zoomlevel ? options.zoomlevel : 13,
        mapId: options.defaultMap ? options.defaultMap : options.useMaps[0],
        center: { lat: 41.895, lng: 12.485},
        filter: { visibility: "anyVisibility", era: "anyEra" },
        overlays: options.enableOverlays ? options.enableOverlays : []
    };
    if (options.center) {
        session.center.lat = options.center.lat;
        session.center.lng = options.center.lng;
    }
    if (options.focus) {
        session.selectedMarkerId = options.focus;
    }
    if (options.setUrl) {
        // url will override zoomlevel, center and selectedMarkerId
        let parts = decodeURIComponent(self.document.location.hash).substring(1).split("/");
        if (parts.length > 1) {
            let center = parts[1].split(",");
            session.zoomlevel = Number(parts[0]);
            session.center.lat = Number(center[0]);
            session.center.lng  = Number(center[1]);
            if (parts.length === 3) {
                session.selectedMarkerId = Number(parts[2]);
            }
        }
    }


    // mapState bundles parameters related to the state of the map that should not be stored in session storage:
    let mapState = {
        initialized : false,
        highlights: [],
        lang: (function(){
            let lang =  (options.lang) ? options.lang : (navigator.language.substring(0, 2) || navigator.userLanguage).substring(0, 2) ;
            if ($.inArray(lang,['de', 'en', 'fr', 'nl']) < 0) {
                lang = 'en';
            }
            return lang;
        }()),
        langReq: '',
        numHighlights : (function(){
            let available = Math.floor((document.getElementById(element).clientHeight - 234) / 175);
            let requested = (options.highlights) ? options.highlights : 0;
            return (requested < available) ? requested : available;
        }()),
        perspectiveParam : (function() {
            if (options.perspective && ($.inArray(options.perspective,['pleiades', 'romaq', 'livius']) > -1 )) {
                return "&perspective=" + options.perspective;
            }
            return '';
        })(),
        modelParam : (function() {
            if (options.model && (options.model === "flat")) {
                return "&flat"
            } else {
                return "";
            }
        })(),
        requireParam : (options.focus) ? '&require=' + options.focus : ''
    };
    mapState.langReq = (mapState.lang == (navigator.language.substring(0, 2) || navigator.userLanguage).substring(0, 2)) ? '' : '&lang='+mapState.lang;


    //
    let defaultMaps = {
        OSM: {
            name: 'OpenStreetMap',
            url: "https://static.vici.org/tiles/osm/{z}/{x}/{y}.png",
            attributions: '© <a href="https://www.openstreetmap.org/copyright">' + txt["osm_contribs"] + '</a> CC BY-SA'
        },
        AWMC: {
            name: 'Ancient World Mapping Center',
            url: "https://static.vici.org/tiles/awmc/{z}/{x}/{y}.png",
            attributions: '© <a href="http://awmc.unc.edu/">Ancient World Mapping Center</a> CC BY 4.0'
        }
    };

    let mapLayers = [];

    $.each(options.useMaps, function(i, mapId){
        let mapData = (options.extraMaps && options.extraMaps[mapId]) ? options.extraMaps[mapId] : defaultMaps[mapId];
        mapLayers.push(new ol.layer.Tile({
            id: mapId,
            maxZoom: mapData.maxZoom ?  mapData.maxZoom : 22,
            name: mapData.name,
            source: new ol.source.XYZ({
                url: mapData.url,
                attributions: mapData.attributions
            })
        }));
    });

    let overlays = [];
    if (options.extraOverlays) {
        for (var property in options.extraOverlays) {
            if (options.extraOverlays.hasOwnProperty(property)) {
                overlays.push(new ol.layer.Tile({
                    id: property,
                    name: options.extraOverlays[property].name,
                    opacity: (options.extraOverlays[property].opacity) ? options.extraOverlays[property].opacity : 1.0,
                    source: new ol.source.XYZ({
                        url: options.extraOverlays[property].url,
                        attributions: options.extraOverlays[property].attributions
                    })
                }));
            }
        }
    }

    let map = new ol.Map({
        layers: mapLayers,
        overlays: overlays,
        target: element,
        controls: (function(){
            let control = ol.control.defaults({
                    attributionOptions: {
                        collapsible: false
                    }
                });

            if (options.showScale) {
                let scaleLineControl = new ol.control.ScaleLine();
                control.extend([scaleLineControl]);
                scaleLineControl.setUnits(options.showScale);
            }
            return control;
        })(),
        view: new ol.View({
            center: ol.proj.fromLonLat([session.center.lng, session.center.lat]),
            zoom: session.zoomlevel,
            maxZoom: 18
        })
    });

    // show selected map & check zoomlevel:
    if (options.useMaps.indexOf(session.mapId) < 0 ) {
        session.mapId = options.useMaps[0];
    }
    $.each(mapLayers, function(id, mapLayer){
        let selected = (session.mapId === mapLayer.get('id'));
        mapLayer.setVisible(selected);

        // correct zoomlevel in zoomed in too much:
        if (selected && mapLayer.get('maxZoom') < session.zoomlevel) {
            map.getView().setZoom(mapLayer.get('maxZoom'));
            session.zoomlevel = mapLayer.get('maxZoom');
            storeSession();
        }
    });

    // show enabled overlays:
    function setOverlayVisibility() {
        $.each(overlays, function(id, overlay){
            overlay.setVisible(session.overlays.indexOf(overlay.get('id')) > -1);
            overlay.changed();
        });
    }
    setOverlayVisibility();

    let lineStyles = [];
    lineStyles['aqueduct'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#0cd3f7',
            width: 3
        })
    });
    lineStyles['canal'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#1111FF',
            width: 3
        })
    });
    lineStyles['road'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#FF0000',
            width: 3
        })
    });
    lineStyles['wall'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#C4A548',
            width: 3
        })
    });
    lineStyles['other'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#f8f525',
            width: 2
        })
    });

    let markerIcons = [];
    for (let i = 1; i < 26; i++) {
        markerIcons[i] = {
            normal: new ol.style.Icon({
                crossOrigin: 'anonymous',
                src: baseUrl + '/images/markers_selection.png',
                offset: [i*32 - 32, 0],
                size: [32, 37],
                anchor: [16, 36],
                anchorXUnits: 'pixels',
                anchorYUnits: 'pixels'
            }),
            uncertain: new ol.style.Icon({
                crossOrigin: 'anonymous',
                src: baseUrl + '/images/markers_selection.png',
                offset: [i*32 - 32, 0],
                size: [32, 37],
                anchor: [16, 36],
                anchorXUnits: 'pixels',
                anchorYUnits: 'pixels',
                opacity: 0.6
            }),
            selected: new ol.style.Icon({
                crossOrigin: 'anonymous',
                src: baseUrl + '/images/markers_selection.png',
                offset: [i*32 - 32, 37],
                size: [32, 37],
                anchor: [16, 36],
                anchorXUnits: 'pixels',
                anchorYUnits: 'pixels',
            }),
            small: new ol.style.Icon({
                crossOrigin: 'anonymous',
                src: baseUrl + '/images/markers_minimal.png',
                offset: [i*12 - 12, 0],
                size: [12, 12]
            })
        }
    }

    let vectorSourceLines = new ol.source.Vector();
    let vectorSourceMarkers = new ol.source.Vector();

    let vectorLayerLines = new ol.layer.Vector({
        source: vectorSourceLines
    });
    let vectorLayerMarkers = new ol.layer.Vector({
        source: vectorSourceMarkers
    });

    map.addOverlay(vectorLayerLines);
    map.addOverlay(vectorLayerMarkers);


    function selectIcon(marker, selected) {

        function isContemporary(kind) {
            return ((kind == 8) || (kind == 19))
        }

        // selected Icons are always shown:
        if (selected) {
            return  markerIcons[marker.vici.kind].selected;
        }

        // match the filter settings:
        let visibilityMatch = (session.filter.visibility == "anyVisibility") || (marker.vici.visible);
        let eraMatch =  (session.filter.era == "anyEra") ||
            ((session.filter.era == "contemporaryEra") && (isContemporary(marker.vici.kind))) ||
            ((session.filter.era == "historicalEra") && (!isContemporary(marker.vici.kind)));

        if (visibilityMatch && eraMatch) {

            // return appropriate icon for zoomlevel:
            let zoomlevel = map.getView().getZoom();
            if (zoomlevel >= marker.vici.zoomSmall) {
                if (zoomlevel >= marker.vici.zoomNormal) {
                    if (marker.vici.identified) {
                        return markerIcons[marker.vici.kind].normal;
                    } else {
                        return markerIcons[marker.vici.kind].uncertain;
                    }
                } else {
                    return markerIcons[marker.vici.kind].small
                }
            }

        }
    }

    function updateDataLayer() {

        function setFeatures(d) {

            //add markers:
            for (let i in d.features) {
                let feature = d.features[i];
                if (!vectorSourceMarkers.getFeatureById(feature.properties.id)) {
                    let marker = new ol.Feature({
                        geometry: new ol.geom.Point(ol.proj.fromLonLat([feature.geometry.coordinates[0], feature.geometry.coordinates[1]]))
                    });

                    marker.setId(feature.properties.id);

                    marker.vici = {
                        id:     feature.properties.id,
                        title:  feature.properties.title,
                        html:   feature.properties.summary,
                        kind:   feature.properties.kind,
                        url:    (function() {
                            if (mapState.perspectiveParam) {
                                return feature.properties.url;
                            } else {
                                return baseUrl + feature.properties.url;
                            }
                        })(),
                        picture: feature.properties.img,
                        zIndex: feature.properties.zindex,
                        identified: feature.properties.identified,
                        visible: (feature.properties.isvisible == "1"),
                        zoomSmall: parseInt(feature.properties.zoomsmall),
                        zoomNormal: parseInt(feature.properties.zoomnormal)
                    };

                    let isSelected = (feature.properties.id === session.selectedMarkerId);

                    marker.setStyle(new ol.style.Style({
                        zIndex: isSelected ? marker.vici.zIndex + 15 : marker.vici.zIndex,
                        image: selectIcon(marker, (isSelected))
                    }));

                    vectorSourceMarkers.addFeature(marker);
                }
            }

            if (!mapState.initialized) {
                if (session.selectedMarkerId && !options.followFocus) {
                    updateInfobox(vectorSourceMarkers.getFeatureById(session.selectedMarkerId));
                }
                mapState.initialized = true;
            }

            //add lines:
            for (let i in d.lines) {
                let feature = d.lines[i];
                if (!vectorSourceLines.getFeatureById(feature.id)) {
                    // a new lineString feature to add:

                    let lineCoordinates=[];
                    for (let j in feature.points) {
                        lineCoordinates.push(ol.proj.fromLonLat([feature.points[j][1], feature.points[j][0]]));
                    }
                    let line = new ol.Feature({
                        geometry: new ol.geom.LineString(lineCoordinates)
                    });

                    line.vici = {
                        expire: feature.expire,
                        markerId : feature.marker
                    };

                    line.setId(feature.id);

                    line.setStyle(lineStyles[feature.kind]);

                    vectorSourceLines.addFeature(line);
                } else if (vectorSourceLines.getFeatureById(feature.id).vici.expire <= zoomlevel) {
                    // improved resolution of lineString:

                    let line = vectorSourceLines.getFeatureById(feature.id);
                    let lineCoordinates=[];

                    for (let j in feature.points) {
                        lineCoordinates.push(ol.proj.fromLonLat([feature.points[j][1], feature.points[j][0]]));
                    }
                    line.setGeometry(new ol.geom.LineString(lineCoordinates));
                    line.vici.expire = feature.expire;
                }
            }

        }

        let extent = map.getView().calculateExtent();
        let zoomlevel = map.getView().getZoom();
        let SW = ol.proj.toLonLat([extent[0], extent[1]]);
        let NE = ol.proj.toLonLat([extent[2], extent[3]]);

        $.ajax({
            url: baseUrl + "/data.php?numeric"+mapState.modelParam+mapState.perspectiveParam+"&bounds=" + SW[1] + "," + SW[0] + "," + NE[1] + "," + NE[0] + "&zoom=" + zoomlevel + mapState.langReq + mapState.requireParam,
            dataType: 'json',
            success: setFeatures
        });
    }

    map.on('click', function(evt) {
        let feature = map.forEachFeatureAtPixel(evt.pixel,
            function(feature, layer) {
                return feature;
            });

        if (feature) {
            if (feature.vici.markerId) {
                // a line was clicked:
                let marker = vectorSourceMarkers.getFeatureById(feature.vici.markerId);
                updateInfobox(marker);
            } else {
                // a marker was clicked:
                updateInfobox(feature);
            }
        }
        getHighlights();
    });


    map.on("moveend", function() {

        if (hasPrefbox()) {
            $('#viciform').hide(100);
        }

        let zoomlevel = map.getView().getZoom();
        let extent = map.getView().calculateExtent();
        let center = ol.proj.toLonLat(map.getView().getCenter());

        session.center.lat = center[1];
        session.center.lng = center[0];

        if (session.selectedMarkerId && vectorSourceMarkers.getFeatureById(session.selectedMarkerId) && !options.followFocus) {
            if (! ol.extent.containsExtent(extent, vectorSourceMarkers.getFeatureById(session.selectedMarkerId).getGeometry().getExtent())) {
                deselectMarker(vectorSourceMarkers.getFeatureById(session.selectedMarkerId));
            }
        }

        if (zoomlevel !== session.zoomlevel) {
            //we're zoomed in or out
            session.zoomlevel = zoomlevel;

            redrawMarkers();
        }
        updateDataLayer();
        getHighlights();
        storeSession();
    });

    function redrawMarkers() {
        vectorSourceMarkers.forEachFeature(function(marker) {
            marker.setStyle(new ol.style.Style({
                zIndex: marker.vici.id === session.selectedMarkerId ? marker.vici.zIndex + 15 : marker.vici.zIndex,
                image: selectIcon(marker, marker.vici.id === session.selectedMarkerId)
            }));
        });
    }

    function deselectMarker(marker) {
        marker.setStyle(new ol.style.Style({
            zIndex: marker.vici.zIndex,
            image: selectIcon(marker, false)
        }));
        session.selectedMarkerId = null;
        storeSession();

        infobox.innerHTML = '';
    }

    function selectMap(mapId) {
        $.each(mapLayers, function(id, mapLayer){
            let selected = (mapId === mapLayer.get('id'));
            mapLayer.setVisible(selected);

            if (selected && mapLayer.get('maxZoom') < session.zoomlevel) {
                map.getView().setZoom(mapLayer.get('maxZoom'));
                session.zoomlevel = mapLayer.get('maxZoom');
            }
        });
        session.mapId = mapId;
        storeSession();
    }


    function getHighlights() {
        // loads data to the highlightsArray for given bounds and zoom

        function showHighlights(d) {
            highlights = d.features;

            let highlightText='';
            if (highlights.length > 0) {

                highlightText += '<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img id="vici_high_close" src="'+baseUrl+'/images/close-button.png"/></div>';
                highlightText += "<div style='font-size: 1.2em; font-weight:bold; margin-left:8px; padding-top:5px;'>"+txt["Featured"]+":</div>";

                for (let i in highlights) {
                    let highlight = highlights[i];

                    highlightText += '<div style="padding:5px; min-height: 36px;" class="highclick" id="high_'+highlight.properties.id+'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" class="marker infomarker icon'+highlight.properties.kind+'" width="32" height="37"><div style="font-weight:bold">'+highlight.properties.title+'</div>';
                    if (highlight.properties.img) {
                        highlightText += '<div style="margin:2px 0px 0px 37px;position:relative;cursor: pointer"><img id="vici_high_image'+i+'" src="'+window.location.protocol+'//static.vici.org/cache/220x124-2'+highlight.properties.img+'">';
                        highlightText += '<div style="position: absolute; bottom:3px; left:0px; width:220px; background-color: rgba(0, 60, 136, 0.7);">'+highlight.properties.summary+' [&nbsp;<a class="vici_box" href="#" id="vici_high_link'+i+'">'+txt["show"]+'</a>&nbsp;]</div>';
                        highlightText += '</div>';
                    } else {
                        highlightText += '<div style="margin-left:37px">'+highlight.properties.summary+' [&nbsp;<a class="vici_box" href="#" id="vici_high_link'+i+'">'+txt["show"]+'</a>&nbsp;]</div>';
                    }
                    highlightText += '</div>'
                }
            }

            highlightsbox.innerHTML = highlightText;

            $('#vici_high_close').click(function(){
                highlightsbox.innerHTML = '';
            });

            $('.highclick').click(function(){
                let string = this.id;
                let id = string.substr(5, string.length-5);
                let marker = vectorSourceMarkers.getFeatureById(id);

                updateInfobox(marker);
                panTo(id);
            });
        }

        if (mapState.numHighlights > 0) {
            let extent = map.getView().calculateExtent();
            let zoomlevel = map.getView().getZoom();
            let SW = ol.proj.toLonLat([extent[0], extent[1]]);
            let NE = ol.proj.toLonLat([extent[2], extent[3]]);
            let bounds = SW[1] + "," + SW[0] + "," + NE[1] + "," + NE[0];

            let requrl = baseUrl + "/highlight.php?numeric" + mapState.perspectiveParam + "&bounds=" + bounds + "&zoom=" + zoomlevel + "&n=" + mapState.numHighlights + "&era=" + session.filter.era + "&visibility=" + session.filter.visibility + mapState.langReq;
            if (session.selectedMarkerId && vectorSourceMarkers.getFeatureById(session.selectedMarkerId)) {
                let extent = ol.proj.toLonLat(vectorSourceMarkers.getFeatureById(session.selectedMarkerId).getGeometry().getExtent());
                let lat = extent[1];
                let lng = extent[0];

                requrl += "&focus=" + lat + "," + lng;
                requrl += "&exclude=" + session.selectedMarkerId;
            }

            $.ajax({
                url: requrl,
                dataType: 'json',
                success: showHighlights
            });
        }
    }

    function storeSession() {
        sessionStorage.setItem('session', JSON.stringify(session));
        if (options.setUrl) {
            let url = '#' + session.zoomlevel + '/' + session.center.lat + ',' + session.center.lng;
            if (session.selectedMarkerId) {
                url += '/' + session.selectedMarkerId;
            }
            window.history.replaceState(null, 'Vici', url);
        }
    }




    ///////

    // add css
    let vicistyle = "";
    vicistyle += "#" + element + " img {border:0} ";
    vicistyle += "#" + element + " #displaybox a:link {text-decoration:underline;color:#fff} ";
    vicistyle += "#" + element + " #displaybox a:visited {text-decoration:underline;color:#fff} ";
    vicistyle += "#" + element + " #displaybox a:hover{text-decoration:underline;color:#fff} ";
    vicistyle += "#" + element + " #displaybox a:active{text-decoration:underline;color:#fff} ";
    vicistyle += ".infomarker{float:left;vertical-align:text-top;margin-top:-2px;margin-right:5px;cursor:pointer} ";
    vicistyle += ".marker{width:32px;height:37px;display:inline-block} ";
    for (let i = 1; i < 26; i++) {
        vicistyle += ".icon"+i+"{background:url("+baseUrl+"/images/markers_selection.png) " + (32 - 32*i) + "px 0} ";
        vicistyle += ".iconS"+i+"{background:url("+baseUrl+"/images/markers_selection.png) " + (32 - 32*i) + "px -37px} ";
    }
    vicistyle += ".ol-rotate{top: 85px !important; left: .5em !important; right: auto !important} "; // reposition ol rotate button
    vicistyle += ".ol-scale-line{bottom: 22px !important;right: 5px !important; left: auto !important} "; // reposition ol rotate button

    // create css
    let css = document.createElement("style");
    css.type = "text/css";
    if (css.styleSheet) {
        css.styleSheet.cssText = vicistyle;
    } else {
        css.appendChild(document.createTextNode(vicistyle));
    }
    document.getElementsByTagName('head')[0].appendChild(css);

    let displaybox = document.createElement('div');
    displaybox.style.position = 'absolute';
    displaybox.style.top = '6px';
    displaybox.style.right = '6px';
    displaybox.style.height = '0';
    displaybox.style.width = '270px';
    displaybox.style.color = '#fff';
    displaybox.style.fontFamily = 'Helvetica';
    displaybox.style.fontSize = '12px';
    displaybox.style.lineHeight = '120%';
    displaybox.style.zIndex = '1';
    displaybox.id = 'displaybox';

    $('#' + element).append(displaybox);

    let infobox = document.createElement('div');
    infobox.style.width = '270px';
    infobox.style.backgroundColor = 'rgba(0, 60, 136, 0.7)';
    infobox.style.borderRadius = '2px';
    infobox.style.marginBottom = '1px';
    infobox.id = 'infobox';

    let highlightsbox = document.createElement('div');
    highlightsbox.style.width = '270px';
    highlightsbox.style.backgroundColor = 'rgba(0, 60, 136, 0.7)';
    highlightsbox.style.borderRadius = '2px';
    highlightsbox.id = 'highlightsbox';

    $('#displaybox').append(infobox);
    $('#displaybox').append(highlightsbox);


    // updates the display of selected marker
    function updateInfobox(feature) {

        let markerData = feature.vici;
        let contents = '';

        if (mapState.initialized) {
            if ((feature.getId() === session.selectedMarkerId) && options.followFocus) {
                panTo(markerData.id);
                return;
            }
            if ((feature.getId() === session.selectedMarkerId) || options.followFocus) {
                window.location.href = markerData.url;
                return;
            }
        }

        if (session.selectedMarkerId && vectorSourceMarkers.getFeatureById(session.selectedMarkerId)) {

            let previouslySelectedMarker = vectorSourceMarkers.getFeatureById(session.selectedMarkerId);

            previouslySelectedMarker.setStyle(new ol.style.Style({
                zIndex: previouslySelectedMarker.vici.zIndex,
                image: selectIcon(previouslySelectedMarker, false)
            }));
        }

        session.selectedMarkerId = feature.getId();

        feature.setStyle(new ol.style.Style({
            zIndex: feature.getStyle().getZIndex() +15,
            image: selectIcon(feature, true)
        }));

        contents += '<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img id="vici_sel_close" src="'+baseUrl+'/images/close-button.png"/></div>';
        contents += "<div style='font-size: 1.2em; font-weight:bold; margin-left:8px; padding-top:5px;'>" + txt["selection"] + ":</div>";
        contents += '<div style="padding:5px; min-height: 36px;"><a href="'+markerData.url+'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" class="marker infomarker iconS'+markerData.kind+'" width="32" height="37"></a><div style="font-weight:bold">'+markerData.title+'</div>';
        if (markerData.picture) {
            contents+= '<div style="margin:2px 0 0 37px; position: relative"><a href="'+markerData.url+'"><img src="'+window.location.protocol+'//static.vici.org/cache/220x124-2'+markerData.picture+'" style="border:0"></a>';
            contents+= '<div style="position: absolute; bottom:3px; left:0; width:220px; background-color: rgba(0, 60, 136, 0.7)">'+markerData.html+' [&nbsp;<a href="'+markerData.url+'">' + txt["more"] + '</a>&nbsp;]</div>';
            contents+= '</div>';
        } else {
            contents+= '<div style="margin-left:37px">'+markerData.html+' [&nbsp;<a href="'+markerData.url+'">' + txt["more"] + '</a>&nbsp;]</div>';
        }
        contents += '</div>';

        infobox.innerHTML = contents;

        $('#vici_sel_close').click(function(){
            deselectMarker(vectorSourceMarkers.getFeatureById(session.selectedMarkerId));
        });

        storeSession();
    }

    function hasPrefbox() {
        return (options.showFilter || options.useMaps.length > 1 || overlays.length > 0)
    }

    // show the preference box:
    if (hasPrefbox()) {

        let html = '<div style="position:relative"><div id="vicihandle" style="width:100%; font-size: 1.2em;font-weight:bold; margin:4px 8px; cursor: pointer;padding-right:14px"><img style="vertical-align:bottom;width:18px;height:18px;background:url(' + baseUrl + '/images/icons-18-white.png) -792px 0;" src="' + baseUrl + '/images/trans.gif" width="1" height="1" />' + txt["Adjust_view"] + '</div><div id="viciform" style="display:none; margin:4px 8px;">';

        let selectorHtml = '';
        if (options.showFilter) {
            selectorHtml = '<div style="margin-top:8px"><strong>' + txt["touristic_vs_archeological"] + ':</strong></div><input type="radio" name="era" value="contemporaryEra" id="contemporaryEra"><label for="contemporaryEra"> ' + txt["museums_etc"] + '</label><br/><input type="radio" name="era" value="anyEra" id="anyEra"><label for="anyEra"> ' + txt["museums_and_archeological"] + '</label><br/><input type="radio" name="era" value="historicalEra" id="historicalEra"><label for="historicalEra"> ' + txt["roman_only"] + '</label><br/><div style="margin-top:8px"><strong>' + txt["visibility_of_sites"] + ':</strong></div><input type="radio" name="visibility" value="onlyVisible" id="onlyVisible"><label for="onlyVisible"> ' + txt["visible_sites"] + '</label><br/><input type="radio" name="visibility" value="anyVisibility" id="anyVisibility"><label for="anyVisibility"> ' + txt["visible_and_invisible"] + '</label><br/>';
        }

        let mapsHtml = '';
        if (options.useMaps.length > 1) {
            mapsHtml = '<div style="margin-top:8px"><strong>' + txt["Map_background"] + ':</strong></div>';
            $.each(mapLayers, function (id, mapLayer) {
                let mapId = mapLayer.get('id');
                let checked = (mapId === session.mapId) ? ' checked' : '';
                mapsHtml += '<input type="radio" name="map" value="' + mapId + '" id="' + mapId + '"' + checked + '><label for="' + mapId + '"> ' + mapLayer.get('name') + '</label><br/>'
            });
        }

        let overlaysHtml = '';
        if (overlays.length > 0) {
            overlaysHtml = '<div style="margin-top:8px"><strong> Overlays:</strong></div>';
            $.each(overlays, function (id, overlay) {
                let overlayId = overlay.get('id');
                let checked = (session.overlays.indexOf(overlayId) > -1) ? ' checked' : '';
                overlaysHtml += '<input type="checkbox" name="overlay" value="' + overlayId + '" id="' + overlayId + '"' + checked + '><label for="' + overlayId + '"> ' + overlay.get('name') + '</label><br/>'
            });
        }

        html += selectorHtml + mapsHtml + overlaysHtml + '</div></div>';

        // create html for the prefbox
        let prefbox = document.createElement('div');
        prefbox.style.position = 'absolute';
        prefbox.style.bottom = '0px';
        prefbox.style.left = '12px';
        prefbox.style.color = '#fff';
        prefbox.style.fontFamily = 'Helvetica';
        prefbox.style.fontSize = '12px';
        prefbox.style.lineHeight = '120%';
        prefbox.style.backgroundColor = 'rgba(0, 60, 136, 0.7)';
        prefbox.style.borderTopLeftRadius = '2px';
        prefbox.style.borderTopRightRadius = '2px';
        prefbox.style.zIndex = '1';

        prefbox.innerHTML = html;

        $('#' + element).append(prefbox);

        $('#' + session.filter.visibility).prop('checked',true);
        $('#' + session.filter.era).prop('checked',true);

        // attach eventlisteners:
        $('#vicihandle').click(function() {
            $('#viciform').toggle(100);
        });

        $("input[name=map]").change(function () {
            selectMap($("input[name='map']:checked").val());
        });

        $("input[name=visibility]").change(function () {
            session.filter.visibility = $("input[name='visibility']:checked").val();
            redrawMarkers();
            getHighlights();
            sessionStorage.setItem('session', JSON.stringify(session));
        });

        $("input[name=era]").change(function () {
            session.filter.era = $("input[name='era']:checked").val();
            redrawMarkers();
            getHighlights();
            sessionStorage.setItem('session', JSON.stringify(session));
        });

        $("input[name=overlay]").change(function () {
            let i = session.overlays.indexOf(this.id);
            if (i > -1) {
                session.overlays.splice(i, 1);
            } else {
                session.overlays.push(this.id);
            }
            setOverlayVisibility();
            sessionStorage.setItem('session', JSON.stringify(session));
        });

    } // end show prefbox


    let panTo = function (markerid) {
        let marker = vectorSourceMarkers.getFeatureById(markerid);
        let zoomlevel = map.getView().getZoom();

        map.getView().animate({
            center: marker.getGeometry().getExtent(),
            zoom: (marker.vici.zoomNormal > zoomlevel) ? marker.vici.zoomNormal : zoomlevel,
            duration: 1500
        });
    };

    // expose functions:
    return { panTo: panTo }

}
