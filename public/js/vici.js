
function ViciWidget(element, options) {

    function getGridSize(zoom) {
        if (zoom >= 13) return 0.125;
        if (zoom >= 10) return 0.25;
        if (zoom >= 7)  return 0.5;
        return 1.0;
    }

    function snap(val, step) {
        return Math.floor(val / step) * step;
    }
    function snapCeil(val, step) {
        return Math.ceil(val / step) * step;
    }

    function snapZoom(zoom) {
        return Math.floor(zoom + 0.5);
    }
    class FocusLocationControl extends  ol.control.Control {
        /**
         * @param {Object} [opt_options] Control options.
         */
        constructor(opt_options) {
            const options = opt_options || {};

            const button = document.createElement('button');
            button.innerHTML = '◎';

            const element = document.createElement('div');
            element.className = 'focus-location ol-unselectable ol-control';
            element.appendChild(button);

            super({
                element: element,
                target: options.target,
            });

            button.addEventListener('click', this.handleFocusLocation.bind(this), false);
        }

        handleFocusLocation() {
            navigator.geolocation.getCurrentPosition((position) => {
                this.getMap().getView().animate({
                    center: ol.proj.fromLonLat([position.coords.longitude, position.coords.latitude]),
                    duration: 1000
                });
            });

        }
    }

    // use the element as an anchor but don't override absolute positioning:
    if ($("#"+element).css("position")  !== "absolute") {
        document.getElementById(element).style.position = "relative";
    }

    let baseUrl = (options.baseUrl) ? options.baseUrl : "https://vici.org";

    let lang =  (options.lang) ? options.lang : (navigator.language || navigator.userLanguage).substring(0, 2) ;
    if ($.inArray(lang,["de", "en", "fr", "nl"]) < 0) lang = "en";
    let txt = {
        de: {
            selection: "Selektion",
            more: "weiter",
            show: "anzeigen",
            Featured: "Schaukasten",
            Adjust_view: "Ansicht &auml;ndern",
            touristic_vs_archeological: "Touristische oder arch&auml;ologische St&auml;tten",
            museums_etc: "Museen und andere zeitgen&ouml;ssische St&auml;tten",
            museums_and_archeological: "Sowohl zeitgen&ouml;ssische als r&ouml;mischen St&auml;tten",
            roman_only: "Nur arch&auml;ologische St&auml;tten",
            visibility_of_sites: "Nur sichtbare oder alle Objekte zeigen",
            visible_sites: "Nur sichtbare Objekte",
            visible_and_invisible: "Alle Objekte zeigen",
            Map_background: "Hintergrund",
            satellite: "Satellitbild",
            satellite_labels: "Satellitebild mit Labels",
            pelagios: "Digital Atlas of the Roman Empire",
            osm_contribs: "OpenStreetMap-Mitwirkende"
        },
        en: {
            selection: "Selection",
            more: "more",
            show: "show",
            Featured: "Featured",
            Adjust_view: 'View settings',
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
            Adjust_view: 'View settings',
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
        overlays: options.enableOverlays ? options.enableOverlays : [],
        moveHere: options.moveHere
    };
    if (options.center) {
        session.center.lat = options.center.lat;
        session.center.lng = options.center.lng;
    }
    if (options.focus) {
        session.selectedMarkerId = options.focus;
    }
    if (options.zoomlevel) {
        session.zoomlevel = options.zoomlevel;
    }
    if (options.filter) {
        session.filter = options.filter;
    }

    if (options.setUrl) {
        // url will override zoomlevel, center and selectedMarkerId
        let parts = decodeURIComponent(self.document.location.hash).substring(1).split("/");
        if (parts.length > 1) {
            session.moveHere = false;
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
        lang: (function(){
            let lang =  (options.lang) ? options.lang : (navigator.language.substring(0, 2) || navigator.userLanguage).substring(0, 2) ;
            if ($.inArray(lang,['de', 'en', 'fr', 'nl']) < 0) {
                lang = 'en';
            }
            return lang;
        }()),
        langReq: '',
        numHighlights : (function(){
            if (typeof options.highlightFunc === 'function') {
                return options.highlights;
            }
            if (document.getElementById(element).clientWidth < 800) {
                return 0;
            }
            let available = Math.floor((document.getElementById(element).clientHeight - 234) / 175);
            let requested = (options.highlights) ? options.highlights : 0;
            return (requested < available) ? requested : available;
        }()),
        perspectiveParam : (function() {
            if (options.perspective) {
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
    mapState.langReq = (mapState.lang === (navigator.language.substring(0, 2) || navigator.userLanguage).substring(0, 2)) ? '' : '&lang='+mapState.lang;


    //
    let defaultMaps = {
        OSM: {
            name: 'OpenStreetMap',
            url: "https://tiles.vici.org/osm/{z}/{x}/{y}.png",
            attributions: '© <a href="https://www.openstreetmap.org/copyright">' + txt["osm_contribs"] + '</a> CC BY-SA'
        },
        AWMC: {
            name: 'Ancient World Mapping Center',
            url: "https://tiles.vici.org/awmc/{z}/{x}/{y}.png",
            attributions: '© <a href="http://awmc.unc.edu/">Ancient World Mapping Center</a> CC BY 4.0',
            maxZoom: 13
        }
    };

    let mapLayers = [];

    $.each(options.useMaps, function(i, mapId){
        let mapData = (options.extraMaps && options.extraMaps[mapId]) ? options.extraMaps[mapId] : defaultMaps[mapId];
        mapLayers.push(new ol.layer.Tile({
            id: mapId,
            maxZoom: mapData.maxZoom ?  mapData.maxZoom : 18,
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

    const view = new ol.View({
        center: ol.proj.fromLonLat([session.center.lng, session.center.lat]),
        zoom: session.zoomlevel,
        enableRotation: false,
        maxZoom: 18
    })

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

            control.extend([new FocusLocationControl()]);

            if (options.showScale) {
                let scaleLineControl = new ol.control.ScaleLine();
                control.extend([scaleLineControl]);
                scaleLineControl.setUnits(options.showScale);
            }
            return control;
        })(),
        view: view
    });

    // see https://openlayers.org/en/latest/examples/geolocation.html
    const geolocation = new ol.Geolocation({
        // enableHighAccuracy must be set to true to have the heading value.
        trackingOptions: {
            enableHighAccuracy: true,
        },
        tracking: true,
        projection: view.getProjection()
    });

    const accuracyFeature = new ol.Feature();
    geolocation.on('change:accuracyGeometry', function () {
        accuracyFeature.setGeometry(geolocation.getAccuracyGeometry());
    });

    const positionFeature = new ol.Feature();
    positionFeature.setStyle(
        new ol.style.Style({
            image: new ol.style.Circle({
                radius: 6,
                fill: new ol.style.Fill({
                    color: '#3399CC',
                }),
                stroke: new ol.style.Stroke({
                    color: '#fff',
                    width: 2,
                }),
            }),
        })
    );

    geolocation.on('change:position', function () {
        const coordinates = geolocation.getPosition();
        positionFeature.setGeometry(coordinates ? new ol.geom.Point(coordinates) : null);
    });

    let geotracking = new ol.layer.Vector({
        source: new ol.source.Vector({
            features: [accuracyFeature, positionFeature],
        }),
    });

    // end: see https://openlayers.org/en/latest/examples/geolocation.html


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
    lineStyles['invisible'] = new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(255,255,255,0)',
            width: 0
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

    map.addOverlay(geotracking);

    // change mouse cursor when over marker or line
    map.on('pointermove', function(evt) {
        let pixel = map.getEventPixel(evt.originalEvent);
        let hit = map.hasFeatureAtPixel(pixel, { layerFilter: function(candidate) {
                return (candidate === vectorLayerMarkers || candidate === vectorLayerLines);
            }
        });
        document.getElementById(map.getTarget()).style.cursor = hit ? 'pointer' : '';
    });

    function selectIcon(marker, selected) {

        function isContemporary(kind) {
            return ((kind == 8) || (kind == 19))
        }

        marker.vici.display=false;
        // selected Icons are always shown:
        if (selected) {
            marker.vici.display=true;
            return  markerIcons[marker.vici.kind].selected;
        }

        // match the filter settings:
        let visibilityMatch = (session.filter.visibility === "anyVisibility") || (marker.vici.visible);
        let eraMatch =  (session.filter.era === "anyEra") ||
            ((session.filter.era === "contemporaryEra") && (isContemporary(marker.vici.kind))) ||
            ((session.filter.era === "historicalEra") && (!isContemporary(marker.vici.kind)));

        if (visibilityMatch && eraMatch) {
            marker.vici.display=true;
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

    function selectAndSetLineStyle(lineFeature) {
        let marker = vectorSourceMarkers.getFeatureById(lineFeature.vici.markerId);
        if(marker && marker.vici.display === false) {
            lineFeature.setStyle(lineStyles['invisible']);
        } else {
            lineFeature.setStyle(lineStyles[lineFeature.vici.kind]);
        }
    }

    function updateDataLayer() {

        function setFeatures(d) {

            function getOLLineGeometry(geometries) {
                for (let i in geometries) {
                    let geometry = geometries[i];
                    if (geometry.type === "LineString") {
                        let inputLine = geometry.coordinates;
                        let projectedLine = [];

                        for (let k in inputLine) {
                            projectedLine.push(ol.proj.fromLonLat([inputLine[k][0], inputLine[k][1]]));
                        }
                        return new ol.geom.LineString(projectedLine);

                    } else if (geometry.type === "MultiLineString") {
                        let inputLine = geometry.coordinates;
                        let projectedLine = [];

                        for (let k in inputLine) {
                            let singleLine = [];
                            for (let l in inputLine[k]) {
                                singleLine.push(ol.proj.fromLonLat([inputLine[k][l][0], inputLine[k][l][1]]));
                            }
                            projectedLine.push(singleLine);
                        }
                        return new ol.geom.MultiLineString(projectedLine);
                    }
                }
            }

            //add markers:
            for (let i in d.features) {
                let feature = d.features[i];

                // process Points as markers - we asume a feature always has a Point:
                if (!vectorSourceMarkers.getFeatureById(feature.id)) {

                    let marker;

                    if (feature.geometry.type === "Point") {
                        marker = new ol.Feature({
                            geometry: new ol.geom.Point(ol.proj.fromLonLat([feature.geometry.coordinates[0], feature.geometry.coordinates[1]]))
                        });
                    } else if (feature.geometry.type === "GeometryCollection") {
                        for (let j in feature.geometry.geometries) {
                            if (feature.geometry.geometries[j].type === "Point") {
                                marker = new ol.Feature({
                                    geometry: new ol.geom.Point(ol.proj.fromLonLat([feature.geometry.geometries[j].coordinates[0], feature.geometry.geometries[j].coordinates[1]]))
                                });
                                break;
                            }
                        }
                    }

                    if (marker) {
                        marker.setId(feature.id);
                        marker.vici = {
                            id: feature.id,
                            title: feature.properties.title,
                            html: feature.properties.summary,
                            kind: feature.properties.kind,
                            url: (function () {
                                if (mapState.perspectiveParam) {
                                    return feature.properties.url;
                                } else {
                                    return baseUrl + feature.properties.url;
                                }
                            })(),
                            picture: feature.properties.img,
                            zIndex: feature.properties.zindex,
                            identified: feature.properties.identified,
                            visible: (feature.properties.isvisible === 1),
                            zoomSmall: parseInt(feature.properties.zoomsmall),
                            zoomNormal: parseInt(feature.properties.zoomnormal)
                        };

                        let isSelected = (feature.id === session.selectedMarkerId);

                        marker.setStyle(new ol.style.Style({
                            zIndex: isSelected ? marker.vici.zIndex + 15 : marker.vici.zIndex,
                            image: selectIcon(marker, (isSelected))
                        }));

                        vectorSourceMarkers.addFeature(marker);
                    }

                }

                // process lines:
                if (feature.geometry.type === "GeometryCollection") {

                    let lineGeometry;

                    if (!vectorSourceLines.getFeatureById(feature.id)) {
                        // new line:
                        let lineFeature = new ol.Feature({
                            geometry: getOLLineGeometry(feature.geometry.geometries)
                        });

                        lineFeature.vici = {
                            expire: feature.properties.line.expire,
                            markerId: feature.id,
                            kind: feature.properties.line.kind
                        };

                        lineFeature.setId(feature.id);
                        selectAndSetLineStyle(lineFeature);
                        vectorSourceLines.addFeature(lineFeature);

                    } else if (vectorSourceLines.getFeatureById(feature.id).vici.expire <= session.zoomlevel) {
                        // existing line, update geometry:
                        let lineFeature = vectorSourceLines.getFeatureById(feature.id);
                        lineFeature.setGeometry(getOLLineGeometry(feature.geometry.geometries));
                        lineFeature.vici.expire = feature.properties.line.expire;
                    }

                }


            }

            if (!mapState.initialized) {
                if (session.selectedMarkerId && !options.followFocus) {
                    updateInfobox(vectorSourceMarkers.getFeatureById(session.selectedMarkerId));
                }
                mapState.initialized = true;
            }
        }

        let extent = map.getView().calculateExtent();
        let zoomlevel = map.getView().getZoom();
        let grid = getGridSize(zoomlevel);
        
        let rawSW = ol.proj.toLonLat([extent[0], extent[1]]);
        let rawNE = ol.proj.toLonLat([extent[2], extent[3]]);
        
        let snappedSW = [snap(rawSW[1], grid), snap(rawSW[0], grid)];
        let snappedNE = [snapCeil(rawNE[1], grid), snapCeil(rawNE[0], grid)];
        let snappedZoom = snapZoom(zoomlevel);
        
        $.ajax({
            url: baseUrl + "/geojson.php?bounds=" + snappedSW[0] + "," + snappedSW[1] + "," + snappedNE[0] + "," + snappedNE[1] + "&zoom=" + snappedZoom + mapState.modelParam + mapState.perspectiveParam + mapState.langReq + mapState.requireParam,
            dataType: 'json',
            headers: {
                'X-Vici-Token': options.viciToken
            },
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

        // update visibility of line, based on marker visibility:
        vectorSourceLines.forEachFeature(function(lineFeature) {
            selectAndSetLineStyle(lineFeature);
        });
    }

    function deselectMarker(marker) {
        marker.setStyle(new ol.style.Style({
            zIndex: marker.vici.zIndex,
            image: selectIcon(marker, false)
        }));
        session.selectedMarkerId = null;
        storeSession();
        
        if (typeof options.selectionFunc === 'function') {
            options.selectionFunc(null);
        }
        infobox.innerHTML = '';

        // update drawing of all lines to make sure connected line is hidden if required (not efficient...)
        vectorSourceLines.forEachFeature(function(lineFeature) {
            selectAndSetLineStyle(lineFeature);
        });
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

    function selectMarkerAndPan(id) {
        let marker = vectorSourceMarkers.getFeatureById(id);
        if (marker) {
            updateInfobox(marker);
            panTo(id);
        }
    }

    function getHighlights() {
        // loads data to the highlightsArray for given bounds and zoom

        function showHighlights(d) {
            let highlights = d.features;

            if (typeof options.highlightFunc === 'function') {
                options.highlightFunc(highlights);
            } else {
                let highlightText='';
                if (highlights.length > 0) {

                    highlightText += '<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img id="vici_high_close" src="'+baseUrl+'/images/close-button.png"/></div>';
                    highlightText += "<div style='font-size: 1.2em; font-weight:bold; margin-left:8px; padding-top:5px;'>"+txt["Featured"]+":</div>";

                    for (let i in highlights) {
                        let highlight = highlights[i];

                        highlightText += '<div style="padding:5px; min-height: 36px;" class="highclick" id="high_'+highlight.properties.id+'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" class="marker infomarker icon'+highlight.properties.kind+'" width="32" height="37"><div style="font-weight:bold">'+highlight.properties.title+'</div>';
                        if (highlight.properties.img) {
                            highlightText += '<div style="margin:2px 0px 0px 37px;position:relative;cursor: pointer"><img id="vici_high_image'+i+'" src="'+window.location.protocol+'//images.vici.org/crop/w220xh124'+highlight.properties.img+'">';
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
                    selectMarkerAndPan(id)
                });
            }
     
        }

        if (mapState.numHighlights > 0) {
            let extent = map.getView().calculateExtent();
            let zoomlevel = map.getView().getZoom();
            let grid = getGridSize(zoomlevel);
            
            let rawSW = ol.proj.toLonLat([extent[0], extent[1]]);
            let rawNE = ol.proj.toLonLat([extent[2], extent[3]]);
            
            let snappedSW = [snap(rawSW[1], grid), snap(rawSW[0], grid)];
            let snappedNE = [snapCeil(rawNE[1], grid), snapCeil(rawNE[0], grid)];
            let snappedZoom = snapZoom(zoomlevel);
            
            let bounds = snappedSW[0] + "," + snappedSW[1] + "," + snappedNE[0] + "," + snappedNE[1];

            let requrl = baseUrl + "/highlight.php?numeric" + mapState.perspectiveParam + "&bounds=" + bounds + "&zoom=" + snappedZoom + "&n=" + mapState.numHighlights + "&era=" + session.filter.era + "&visibility=" + session.filter.visibility + mapState.langReq;
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
    vicistyle += ".ol-scale-line{bottom: 2em !important;right: 5px !important; left: auto !important} "; 
    vicistyle += ".ol-attribution {background-color: rgba(255, 255, 255, 0.8) !important;} "; // make background visible
    vicistyle += ".ol-attribution ul {color: #333 !important; text-shadow: none !important;} "; // make text readable
    vicistyle += ".ol-attribution img {position: relative; top: 3px !important;} "; // lower the image by 3px
    vicistyle += ".ol-attribution {bottom: .5em !important; max-height: none !important;} "; // fix attribution position
    vicistyle += ".ol-attribution ul {max-width: 100% !important; overflow: visible !important; margin-bottom: 3px !important; margin-top: -3px !important;} "; // fix attribution text overflow and position
    vicistyle += ".ol-attribution li {display: inline-block !important; max-width: 100% !important;} "; // ensure attribution items are visible

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

        if(typeof options.selectionFunc === 'function') {
            options.selectionFunc(markerData);
        } else {
            contents += '<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img id="vici_sel_close" src="'+baseUrl+'/images/close-button.png"/></div>';
            contents += "<div style='font-size: 1.2em; font-weight:bold; margin-left:8px; padding-top:5px;'>" + txt["selection"] + ":</div>";
            contents += '<div style="padding:5px; min-height: 36px;"><a href="'+markerData.url+'"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" class="marker infomarker iconS'+markerData.kind+'" width="32" height="37"></a><div style="font-weight:bold">'+markerData.title+'</div>';
            if (markerData.picture) {
                contents+= '<div style="margin:2px 0 0 37px; position: relative"><a href="'+markerData.url+'"><img src="'+window.location.protocol+'//images.vici.org/crop/w220xh124'+markerData.picture+'" style="border:0"></a>';
                contents+= '<div style="position: absolute; bottom:3px; left:0; width:220px; background-color: rgba(0, 60, 136, 0.7)">'+markerData.html+' [&nbsp;<a href="'+markerData.url+'">' + txt["more"] + '</a>&nbsp;]</div>';
                contents+= '</div>';
            } else {
                contents+= '<div style="margin-left:37px">'+markerData.html+' [&nbsp;<a href="'+markerData.url+'">' + txt["more"] + '</a>&nbsp;]</div>';
            }
            contents += '</div>';
    
            infobox.innerHTML = contents;
        }

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

        let html = '<div style="position:relative"><div id="vicihandle" style="width:100%; font-size: 1.2em;font-weight:bold; margin:4px 8px; cursor: pointer;padding-right:14px">&#9776; ' + txt["Adjust_view"] + '</div><div id="viciform" style="display:none; margin:4px 8px;">';

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

    if (session.moveHere && 'geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            if (position.coords.longitude > -10.1 && position.coords.longitude < 49.1 && position.coords.latitude > 29.5 && position.coords.latitude < 57.7) {
                map.getView().animate({
                    center: ol.proj.fromLonLat([position.coords.longitude, position.coords.latitude]),
                    duration: 1500
                });
            }
        });
        session.moveHere = false;
    }

    // expose functions:
    return { 
        panTo: panTo,
        selectMarkerAndPan: selectMarkerAndPan 
    }

}
