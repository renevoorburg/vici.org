

function editPage() {

    editorObj = CKEDITOR.replace( 'edit_full',
        {
            customConfig: '/ckeditor/config.js',
            height: "480",
            stylesSet : 'my_styles',
            extraPlugins : 'vicicite,viciquote',
            toolbar :
                [
                    [ 'Undo', 'Redo' ], [ 'Format'],
                    [ 'Bold', 'Italic', '-', 'NumberedList', 'BulletedList'], ['ViciQuote'], [ 'ViciCite' ],
                    [ 'Link', 'Unlink'] , [ 'Source']
                ]
        });

    CKEDITOR.stylesSet.add( 'my_styles',
        [
            // Block-level styles
            { name : 'Subheading 1', element : 'h2', styles : { 'font-size' : '1.4em', 'font-weight' : 'normal', 'border-bottom': '1px solid #AAA'} },
            { name : 'Subheading 2' , element : 'h3', styles : { 'font-weight' : 'bold' , 'font-size' : '1.2em' }}
        ]);
}

function MapWidget(canvas, positionField, lng, lat, markerType) {
    let baseUrl = window.location.hostname.match(/\.local$/) === ".local" ? window.location.protocol + "//vici.local" : window.location.protocol + "//vici.org";

    let defaultMaps = {
        OSM: {
            name: "OpenStreetMap",
            url: "https://static.vici.org/tiles/osm/{z}/{x}/{y}.png",
            attributions: "© <a href=\"https://www.openstreetmap.org/copyright\">Open Streetmap Contributors</a> CC BY-SA"
        },
        ESRI: {
            name: "Esri WorldImagery",
            url: "https://static.vici.org/tiles/world/{z}/{y}/{x}",
            attributions: "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community"
        }
    };

    let selectedMap = (session.mapId === "ESRI") ? "ESRI" : "OSM";

    let mapLayer = new ol.layer.Tile({
        source: new ol.source.XYZ(defaultMaps[selectedMap])
    })

    let markerIconStyleSets = [];
    for (let i = 1; i < 26; i++) {
        markerIconStyleSets[i] = {
            normal: new ol.style.Icon({
                crossOrigin: 'anonymous',
                src: baseUrl + '/images/markers_selection.png',
                offset: [i*32 - 32, 0],
                size: [32, 37],
                anchor: [16, 36],
                anchorXUnits: 'pixels',
                anchorYUnits: 'pixels'
            })
        }
    }

    var markerIcon = new ol.Feature({geometry: new ol.geom.Point(ol.proj.fromLonLat([lng, lat]))});
    setMarkerIcon(markerType);

    var vectorLayer = new ol.layer.Vector({
        source: new ol.source.Vector({features: [markerIcon],})
    });

    var map = new ol.Map({
        target: canvas,
        layers: [mapLayer, vectorLayer],
        view: new ol.View({
            center: ol.proj.fromLonLat([lng, lat]),
            zoom: 14
        })
    })

    var dragInteraction = new ol.interaction.Modify({
        features: new ol.Collection([markerIcon]),
        pixelTolerance: 40,
        style: null
    });
    map.addInteraction(dragInteraction);

    // show new coordinates in form:
    markerIcon.on('change',function(){
        document.getElementById(positionField).value = [
            ol.proj.toLonLat(this.getGeometry().getCoordinates())[1],
            ol.proj.toLonLat(this.getGeometry().getCoordinates())[0]
        ].join(', ');
    },markerIcon);

    // exports
    function moveMarker(str) {
        var coordsArr=str.split(",");
        if (coordsArr.length !== 2) return false;
        markerIcon.setGeometry(
            new ol.geom.Point(ol.proj.fromLonLat([Number(coordsArr[1]), Number(coordsArr[0])]))
        );
        map.getView().animate({
            center: markerIcon.getGeometry().getExtent(),
            duration: 400
        });
    }

    function setMarkerIcon(id) {
        markerIcon.setStyle(
            new ol.style.Style(
                { image: markerIconStyleSets[id].normal } )
        );
    }

    return {
        moveMarker:     moveMarker,
        setMarkerIcon:  setMarkerIcon
    }
}
