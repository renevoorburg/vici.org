/*"use strict";*/

//function prototypes:

if (!Function.prototype.debounce) {
    Function.prototype.debounce = function(threshold, execAsap) {
        var func = this, // reference to original function
            timeout; // handle to setTimeout async task (detection period)
        // return the new debounced function which executes the original function 
        // only once until the detection period expires
        return function debounced () {
            var obj = this, // reference to original context object
                args = arguments; // arguments at execution time
            // this is the detection function. it will be executed if/when the 
            // threshold expires
            function delayed () {
                // if we're executing at the end of the detection period
                if (!execAsap)
                    func.apply(obj, args); // execute now
                // clear timeout handle
                timeout = null;
            };
            // stop any current detection period
            if (timeout)
                clearTimeout(timeout);
            // otherwise, if we're not already waiting and we're executing at the 
            // beginning of the waiting period
            else if (execAsap)
                func.apply(obj, args); // execute now
            // reset the waiting period
            timeout = setTimeout(delayed, threshold || 100);
        };
    }
}

if (!String.prototype.format) {     // javascript printf, uses {0}, {1} etc.
    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) { 
            return typeof args[number] != 'undefined' ? args[number] : match;
        });
    };
}

if (!Array.prototype.indexOf) {     // need this for IE7
    Array.prototype.indexOf = function(obj, start) {
        for (var i = (start || 0), j = this.length; i < j; i++) {
            if (this[i] === obj) { return i; }
        }
        return -1;
    }
}

// generic functions:
function inArray(elem, arr, i ) {
	return arr == null ? -1 : arr.indexOf(elem, i);
}

function stopPropagation(e) {
    if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
}

function getEventCoords(e) {
    var posx = 0;
	var posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) 	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) 	{
		posx = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
	return {x: posx, y: posy};
}

function setInnerHTML(id, html) {
    var elem = document.getElementById(id);
    elem.innerHTML = html;   
}

function getInnerHTML(id) {
    var elem = document.getElementById(id);
    return elem.innerHTML;   
}

function ajax(request) {
    var xr;
    if (window.XMLHttpRequest) { xr = new XMLHttpRequest(); } else if (window.ActiveXObject) { xr = new ActiveXObject("Microsoft.XMLHTTP"); }
    xr.onreadystatechange = function() {	
        if (xr.readyState == 4) {
            if (xr.status == 200 || xr.status == 304) {
                //response = eval( "(" + xr.responseText + ")" ); 
                var response = JSON.parse(xr.responseText);
                request.success(response);
            }
        }
    }
    xr.open("GET",request.url,true);
    xr.send(null);
}

// global vars object:
var vici = {
    lang: function() {
        var lang = (window.navigator.userLanguage || window.navigator.language).substring(0, 2);
        if (inArray(lang,['de', 'en', 'fr', 'nl']) < 0) lang = 'en';
        return lang;    
    }(),    
    map:            null,
    here:           null,
    markerArr:      [],
    polylineArr:    [],
    areaLoadedArr:  [], 
    popupsArr:      [],
    selectedMarkerId: null,
    selectedImage:  null,
    auraMarker:     null,
    showLabels:     false,
    showAllMarkers: true,
    gpsInitialized: false,
    locationAware:  false,
    trackLocation:  true
}

var strings = function(lang) {
    var babel = {
        de: {
            AltLang: 'Alternative Sprache',
            Back: 'Zurück',
            All: 'Alles',
            CreatedBy: 'Bild von ',
            Close: 'Schließen',
            Footer: "Hinzugefügt von {0} am {1}. Zuletzt bearbeitet von {2} am {3}. Creative Commons Attribution-ShareAlike 3.0",
            MoreImages: 'Weitere Bilder', 
            more: 'weiter',
            show: 'anzeigen',
            Visible: 'Sichtbar'
        },
        en: {
            AltLang: 'In an other language',
            Back: 'Back',
            All: 'All',
            Close: 'Close',
            CreatedBy: 'By ',
            Footer: "Added by {0} on {1}. Last edited by {2} on {3}. Creative Commons Attribution-ShareAlike 3.0",
            MoreImages: 'More images', 
            more: 'more',
            show: 'show',
            Visible: 'Visible'
        },
        fr: { 
            AltLang: 'Autre langue',
            Back: 'Retour',
            All: 'Tout',
            Close: 'Fermer',
            CreatedBy: 'Image par ',
            Footer: "Ajouté par {0} le {1}. Dernière modification par {2} le {3}. Creative Commons Attribution-ShareAlike 3.0",
            MoreImages: 'More images', 
            more: 'plus',
            show: 'exposer',
            Visible: 'Visible'
        },
        nl: {
            AltLang: 'In een alternatieve taal',
            Back: 'Terug',
            All: 'Alles',
            Close: 'Sluiten',
            CreatedBy: 'Afbeelding door ',
            Footer: "Toegevoegd door {0} op {1}. Laatste bewerking door {2} op {3}. Creative Commons Attribution-ShareAlike 3.0",
            MoreImages: 'Meer afbeeldingen',  
            more: 'meer',
            show: 'tonen',
            Visible: 'Zichtbaar'
        }
    }
    return babel[lang];
}(vici.lang);

var markerIcons = [];

var base=''; // base='http://vici.org';

// specific functions:

// sets the proper image for each marker:
function updateMarkerImages(markerArr) {
    for (var i in markerArr) {
        markerArr[i].selectImage(); 
    }
}

function trackLocation(track) {
    if (track) {
        document.getElementById('btnfocus').className = "bbarbtn btnfocuson";
        vici.trackLocation = true;
        vici.map.setCenter(vici.here.position);
    }
    if (!track) {
        document.getElementById('btnfocus').className = "bbarbtn btnfocusoff";
        vici.trackLocation = false;
    }
}

// updates the show all / visible buttons and updates display of markers:
function displayAllMarkers(showAll) {
    if (showAll && ! vici.showAllMarkers) {
        document.getElementById('btnall').className = "bbarbtn btnfont btnallon";
        document.getElementById('btnvisual').className = "bbarbtn btnfont btnvisualoff";
        vici.showAllMarkers = true;
        updateMarkerImages(vici.markerArr);
    }
    if (!showAll && vici.showAllMarkers) {
        document.getElementById('btnall').className = "bbarbtn btnfont btnalloff";
        document.getElementById('btnvisual').className = "bbarbtn btnfont btnvisualon";
        vici.showAllMarkers = false;
        updateMarkerImages(vici.markerArr);
    }
}


function displayLabels(showLabels) {
    if (showLabels) {
        var mapOptions = { mapTypeId: google.maps.MapTypeId.HYBRID };
        document.getElementById('btnlabels').className = "bbarbtn btnlabelson";
    } else {
        var mapOptions = { mapTypeId: google.maps.MapTypeId.SATELLITE };
        document.getElementById('btnlabels').className = "bbarbtn btnlabelsoff";
    }
    vici.map.setOptions(mapOptions);
    vici.showLabels = showLabels;
}


function removePopup() {
    if (vici.selectedImage) {
        popup.style.display = 'none';
        vici.selectedImage = null; // bookkeeping
        
        // detach close function listener from page:
        document.getElementById('page').onclick = null;
    }
}

function togglePopup(id, x, y) {
    var content, popup;
    
    popup = document.getElementById('popup');
    if (popup.style.display == 'block' && id == vici.selectedImage) {
        removePopup();
    } else {    
        // fill and show popup:
        popup = document.getElementById('popup');
        popup.innerHTML = vici.popupsArr[id];
        popup.style.top = y+'px';
        popup.style.display = 'block';
        vici.selectedImage = id; // bookkeeping
        
        // attach close function listener from page:
        document.getElementById('page').onclick = function (e) {
            stopPropagation(e);
            if (vici.selectedImage) {
                document.getElementById('popup').style.display = 'none';
                vici.selectedImage = null; 
            }
        }
    }
}

function showPage(blurb) {
    var page;
    
    // display full content, just loaded as JSON d:
    function displayFullContent(d) {
        
        // formats text for the copyright popups:
        function imgdataToPoptext(img) {
            var poptext = '';
            if (img.title) {poptext += img.title+'<br>';}
            if (img.description) {poptext += img.description+'<br>';}
            if (img.ownwork=='1') {
                poptext += strings.CreatedBy+img.uploader+' - '+img.license+'<br>';
            } else {
                if (img.attribution) {
                    poptext += img.attribution;
                } else {
                    poptext += strings.CreatedBy+img.creator;
                }
                if (img.license != 'Unknown') {
                    poptext += ' - '+img.license;
                }  
            }
            return '<p>'+poptext+'</p>';
        } // imgdataToPoptext
    
        // show non-default images:    
        if (d.properties.images[0]) {
            var firstOther = true;
            var content = '';
            var data = '';
            
            // show images and store popupdata:
            for (var i in d.properties.images) {
                var img = d.properties.images[i];
                
                if (img.isdefault != '1') {   // default image is already shown, so skip adding it
                    if (firstOther) {
                        content += '<h2>'+strings.MoreImages+'</h2>'
                        firstOther = false;
                    }
                    content += '<img id="img'+i+'" class="popable" src="http://static.vici.org/cache/260x0-3'+img.path+'">';
                }
                // data += '<div id="popup'+i+'">'+imgdataToPoptext(img)+'</div>';
                vici.popupsArr[i] = '<div id="popup'+i+'">'+imgdataToPoptext(img)+'</div>';
            }
            setInnerHTML('moreimages', content);
            
            // attach eventlisteners:
            for (var i in d.properties.images) {
                document.getElementById('img'+i).onclick = (function(id) { 
                    return function(event) {
                        stopPropagation(event);
                        var coords = getEventCoords(event);
                        togglePopup(id, coords.x, coords.y);
                    }  
                })(i);
            }  
        }  
         
        setInnerHTML('copyright', strings.Footer.format(d.properties.creatorName, d.properties.createDate, d.properties.editorName, d.properties.editDate));
        // display full text if available
        if (d.properties.text.length > vici.markerArr[vici.selectedMarkerId].html.length) {
            setInnerHTML('text', d.properties.text);
        }   
        // display alternative lang text if it is more substantial::
        if(getInnerHTML('text').length < d.properties.altText.length ) {
            setInnerHTML('otherlang','<h2>'+strings.AltLang+':</h2>'+d.properties.altText);
            document.getElementById("otherlang").style.display="block";
        }
        
    } // displayFullContent
    
    // clean old data:
    vici.popupsArr = [];
    document.getElementById("otherlang").style.display="none";
    setInnerHTML('moreimages', '');

    if(blurb) {
        // display about blub:
        setInnerHTML('ttitle', 'Vici.org mobile');
        setInnerHTML('defaultimage', '<img src="/images/vici_org.png" width="126" height="72">'); 
        setInnerHTML('text', '<p>Vici.org mobile</p><p>V 2.0 beta 2 - René Voorburg 2013 - rene@digitopia.nl</p><p>Visit http://vici.org/</p>');
        setInnerHTML('copyright', '');

    } else {   
        // display selected item using loaded info:
        setInnerHTML('ttitle', vici.markerArr[vici.selectedMarkerId].title);
        setInnerHTML('text', '<p>'+vici.markerArr[vici.selectedMarkerId].html+'</p>');
    
        if (vici.markerArr[vici.selectedMarkerId].picture) {
            setInnerHTML('defaultimage', '<img id="img0" class="popable" src="http://static.vici.org/cache/260x146-2'+vici.markerArr[vici.selectedMarkerId].picture+'" width="260" height="146">'); 
        } else {
            setInnerHTML('defaultimage', '');
        }

        // now get extended info:
        ajax({
            url: base+"/object.php?id="+vici.markerArr[vici.selectedMarkerId].id,
            dataType: 'json',
            success: displayFullContent
        });
    }
    
    // basic content loaded, ready to show:
    document.getElementById("page").style.display="block";
    document.getElementById("mapcanvas").style.display="none"; // Android z-index bug

    
} // showPage

function closePage() {
    removePopup();
    document.getElementById("mapcanvas").style.display="block"; // was set to "none" due to Android bug
    document.getElementById("page").style.display="none";
    google.maps.event.trigger(vici.map, 'resize');
} // closePage

function closeMessage() {
    // hide infobox:   
    document.getElementById("infobox").innerHTML = '';;
    
    // restore marker:
    vici.markerArr[vici.selectedMarkerId].restoreState();
    vici.selectedMarkerId = null;
    
    // remove marker aura:
    vici.auraMarker.setVisible(false);
}

function removeHiddenMarkers(map) {
    var mapBounds = map.getBounds();

    for (i in vici.markerArr) {
        if (!mapBounds.contains(vici.markerArr[i])) { 
            vici.markerArr[i].setMap(null);
        }
    }
}

// loads the vici markers and lines from server, initiates the data:
function loadData(map) {


    // proces items in a way that gives the browser some 'breath' 
    // from: http://tobyho.com/2011/11/03/delaying-repeatedly/
    function processItems(items, processItem, delay){
        delay = delay || 10;
        var queue = items.slice(0) ;                             
        function processNextBatch(){
            var nextItem,
                startTime = +new Date;
            while(startTime + 100 >= +new Date){
                nextItem = queue.shift();
                if (!nextItem) return;
                processItem(nextItem);
            }
            setTimeout(processNextBatch, delay);
        }
        processNextBatch();
    }

    // return a normalized object representation of an area, 
    // used to create virtual 'tiles' to prevent loading of data with each minor move of the map
    function normalizeArea(map) {
        var normalized = {
            latSW: null,
            lngSW: null,
            latNE: null,
            lngNE: null,
            yMin: null,
            xMin: null,
            yMax: null,
            xMax: null,
            zoom: map.getZoom()
        };        
        var latSW = map.getBounds().getSouthWest().lat();
        var lngSW = map.getBounds().getSouthWest().lng();
        var latNE = map.getBounds().getNorthEast().lat();
        var lngNE = map.getBounds().getNorthEast().lng();
        var dLat =  latNE - latSW;
        var dLng =  lngNE - lngSW;
            
        normalized.yMin = Math.floor(latSW/dLat);
        normalized.xMin = Math.floor(lngSW/dLng);
        normalized.yMax = Math.ceil(latNE/dLat);
        normalized.xMax = Math.ceil(lngNE/dLng);
        normalized.latSW = normalized.yMin * dLat;
        normalized.lngSW = normalized.xMin * dLng;
        normalized.latNE = normalized.yMax * dLat;
        normalized.lngNE = normalized.xMax * dLng;
    
        return normalized;
    } // normalizeArea

    function selectMarker(id) {
        var marker = vici.markerArr[id];
        
        // select marker:
        vici.selectedMarkerId = id;
        marker.raiseState(); 
        
        // add aura:
        vici.auraMarker.position = marker.position;
        vici.auraMarker.setVisible(true);
        vici.auraMarker.setIcon(vici.auraMarker.icon);    
    }

    function markerClicked(marker, center) {
        var content,
            summary,
            infobox;
        
        // turn off previously selected markers:
        if (vici.selectedMarkerId && (vici.selectedMarkerId != marker.id)) {
            // deselect current marker first:
            vici.markerArr[vici.selectedMarkerId].restoreState();
        }
        
        if (marker.id ==  vici.selectedMarkerId ) {
            showPage();
        } else {

            // select marker:         
            selectMarker(marker.id);     
        
            content  = '<div id="infoclose"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" width="18" height="18"></div>';
            content += '<div style="padding:4px 5px 8px 5px">';
            content += '<img id="itopage" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw==" class="marker infomarker icon'+marker.kind+'" width="32" height="37">';
            content += '<div>'+marker.title+'</div>';
        
            summary = (marker.title==marker.html) ? '' : marker.html;
        
            if (marker.picture) {
                content += '<div style="margin:0 0 0 38px;position:relative;height:146px">';
                content += '<img  src="http://static.vici.org/cache/260x146-2'+marker.picture+'" width="260" height="146">';
                content += '<div style="position: absolute; bottom:0px; left:0px; width:260px;padding:4px 0 4px 0;background-image: url('+base+'/images/black_50.png);font-size:0.9em">'+summary+' [&nbsp;<a href="#" id="atopage">'+strings.more+'</a>&nbsp;]</div>';
                content += '</div>';
            } else {
                content += '<div style="margin-left:38px;font-size:0.9em">'+summary+' [&nbsp;<a href="#" id="atopage">'+strings.more+'</a>&nbsp;]</div>';
            }
        
            infobox = document.getElementById("infobox");
            infobox.innerHTML = content;
        
            new FastButton(document.getElementById('infoclose'), function() {
                closeMessage();
            });
            new FastButton(document.getElementById('atopage'), function() {
                showPage();
            });
            new FastButton(document.getElementById('itopage'), function() {
                showPage();
            });
        
        }
        
        if (center) {
            vici.map.panTo(marker.position);
        }  
        
    }

    function setFeatures(d) {
        createMarkers(d);
        createLines(d);
    }

    function createMarkers (d) {
        var restoreState = function() {
            if (this.state_high) {
                this.state_high = false;
                this.zIndex = this.zIndex-20;
                this.selectImage();
            }
        }
        var raiseState = function() {
            if (!this.state_high) {
                this.state_high = true;
                this.zIndex = this.zIndex+20;
                this.icon = this.image_normal;
                this.setIcon(this.icon);
            }
        }
        var selectImage = function () {
            var zoomlevel = map.getZoom();
            
            // determine visibility:
            var itemSelected = this.state_high;
            var itemOnMap = (this.zoom_small <= zoomlevel);
            var itemVisibilityMatch = vici.showAllMarkers || this.objectvisible;                 
                               
            this.visible = itemSelected ||  (itemOnMap && itemVisibilityMatch);
                               
            // determine icon:
            if ((zoomlevel >= this.zoom_normal) || this.state_high) {
                this.icon = this.image_normal;
            } else {
                this.icon=this.image_small;
            }
            this.setIcon(this.icon);
        }
        
        function plotMarker(feature) {
            if (!vici.markerArr[feature.properties.id]) {
                // this a 'new'  marker, so 'load' it:  
                var marker = new google.maps.Marker({
                    id: feature.properties.id,
                    url: base+feature.properties.url,
                    position: new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]),
                    map: map,
                    title: feature.properties.title,
                    html: feature.properties.summary,
                    objectvisible: (feature.properties.isvisible=="1"),
                    zoom_small: parseInt(feature.properties.zoomsmall),
                    zoom_normal: parseInt(feature.properties.zoomnormal),
                    image_small: markerIcons[feature.properties.kind].small,
                    image_normal: markerIcons[feature.properties.kind].normal,
                    state_high: false,
                    icon: null,
                    kind:  feature.properties.kind,
                    picture: feature.properties.img,
                    zIndex: feature.properties.zindex,
                    selectImage: selectImage,
                    restoreState: restoreState,
                    raiseState: raiseState
                });
                marker.selectImage();
                google.maps.event.addListener(marker, 'click', function() {markerClicked(this, false)});
                vici.markerArr[marker.id]=marker;
            }
        }
        
        
        processItems(d.features, plotMarker, 10);
        
        
    } // end createMarkers  
    
    
    function createLines(d) {
        
        function plotLine(line) {
            var zoomlevel = vici.map.getZoom();
            if (!vici.polylineArr[line.id] || (vici.polylineArr[line.id] && vici.polylineArr[line.id].expire <= zoomlevel)) {
                var lineCoordinates=[];
                for (var j in line.points) {
                    lineCoordinates.push(new google.maps.LatLng(line.points[j][0], line.points[j][1]));   			
                }
                var polylineOptions = {
                    path: lineCoordinates,
                    markerId : line.marker,
                    strokeColor: "#FF0000",
                    strokeOpacity: 1.0,
                    strokeWeight: 3,
                    nominalWeight: 3,
                    expire : line.expire,
                    growZoom: 16
                };
                switch (line.kind) {
                    case 'aqueduct':
                        polylineOptions.strokeColor = "#0000FF";
                        polylineOptions.strokeWeight = 2;
                        polylineOptions.nominalWeight = 2;
                        break;
                    case 'canal':
                        polylineOptions.strokeColor = "#1111FF";
                        polylineOptions.strokeWeight = 2;
                        polylineOptions.nominalWeight = 3;
                        polylineOptions.growZoom = 15;
                        break;
                    case 'road':
                        break;
                    case 'wall':
                        polylineOptions.strokeColor = "#C4A548";
                        break;
                    case 'other':
                        polylineOptions.strokeOpacity = 0.8;
                        break;
                };
                if (vici.polylineArr[line.id]) {
                    // update polyline:
                    vici.polylineArr[line.id].setOptions(polylineOptions);   
                } else {
                    // create new polyline:
                    var polyline = new google.maps.Polyline(polylineOptions);
                    polyline.setMap(map); 
                    google.maps.event.addListener(polyline, 'click', function() { markerClicked(vici.markerArr[this.markerId], true)});
                    vici.polylineArr[line.id] = polyline; 	
                }
            }
        } // plotLine
        
        processItems(d.lines, plotLine, 10);
    
    } // end createLines
      
    // load data from normalized area:
    var area = normalizeArea(map);    
    if (!vici.areaLoadedArr[area.zoom, area.xMin+'_'+area.yMin]) {    
        ajax({
            url: base+"/data.php?numeric&bounds="+area.latSW+","+area.lngSW+","+area.latNE+","+area.lngNE+"&zoom="+area.zoom+"&lang="+vici.lang,
            dataType: 'json',
            success: setFeatures
        });
        vici.areaLoadedArr[area.zoom, area.xMin+'_'+area.yMin] = true;
    }
}   

function initialize() {

    // attach eventlisteners:
    new FastButton(document.getElementById('btnfocus'), function() { if (vici.gpsInitialized) trackLocation(!vici.trackLocation); });
    new FastButton(document.getElementById('btnall'), function() { displayAllMarkers(true)});
    new FastButton(document.getElementById('btnvisual'), function() { displayAllMarkers(false)});
    new FastButton(document.getElementById('btnlabels'), function() { displayLabels(!vici.showLabels)});
    new FastButton(document.getElementById('btninfo'), function() { showPage(true); });
    new FastButton(document.getElementById('backtxt'), function() { closePage(); });

    // internationalization:
    document.getElementById('backtxt').innerHTML = strings.Back;
    document.getElementById('btnall').innerHTML = strings.All;
    document.getElementById('btnvisual').innerHTML = strings.Visible;

    var errorGettingPosition = function() {
       // alert('error');
    };
    
    var createHereIcon = function (position) {
        vici.here = new google.maps.Marker({
            position: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
            map: vici.map,
            icon: new google.maps.MarkerImage(base+'/images/mobile.png', new google.maps.Size(15, 15), new google.maps.Point(81, 104), new google.maps.Point(8, 8))
        });
        vici.here.setIcon(vici.here.icon);
    }

    // create map:
	var mapOptions = {
		center: new google.maps.LatLng(50.84,5.69),
    	zoom: 12,
    	minZoom: 4,
        streetViewControl: false,
        panControl: false,
        zoomControl: false,
        mapTypeControl: false,
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };
    vici.map = new google.maps.Map(document.getElementById("mapcanvas"), mapOptions);
    
    if (navigator.geolocation) {
        vici.locationAware = true;
    	
    	// get position:
    	navigator.geolocation.getCurrentPosition(function(position){
    	    vici.gpsInitialized = true;
    	    createHereIcon(position);
    	    trackLocation(vici.trackLocation); 
    	}, errorGettingPosition, {'enableHighAccuracy':true,'timeout':10000,'maximumAge':20000});
    	
    	// set watch position function:
    	var watch_id = navigator.geolocation.watchPosition (
    		function(position) {
        		if (vici.gpsInitialized) {
        			vici.here.position = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        			vici.here.setIcon(vici.here.icon);			
        		} else {
        		    vici.gpsInitialized = true;
        			createHereIcon(position);
        			trackLocation(vici.trackLocation);
        		};
        		if (vici.trackLocation) {
        		    vici.map.setCenter(vici.here.position);
        		}
        	}, errorGettingPosition, {'enableHighAccuracy':true,'timeout':30000,'maximumAge':27000} 
        );    
    } else {
		vici.locationAware = false;
    } // if (navigator.geolocation)
    
    for (var i=1; i<26; i++) {
        markerIcons[i] =  {
            normal: new google.maps.MarkerImage(base+'/images/markers.png', new google.maps.Size(32, 37), new google.maps.Point(i*32-32, 0), new google.maps.Point(16, 37)),
            small:  new google.maps.MarkerImage (base+'/images/markers_minimal.png', new google.maps.Size(12, 12), new google.maps.Point(i*12-12, 0), new google.maps.Point(6, 6))
        }
    }
    
    
    // draw mapdata:
    google.maps.event.addListener(vici.map, 'bounds_changed', (function() { 
        
        loadData(vici.map);
        
        // test if selected marker still visible:
        if (vici.selectedMarkerId) {
            var mapBounds = this.getBounds();
            if (!mapBounds.contains(vici.markerArr[vici.selectedMarkerId].position)) { closeMessage();}
        };
        
                removeHiddenMarkers(vici.map);
        
    }).debounce(300));
  
    // update display of mapdata:
    google.maps.event.addListener(vici.map, 'zoom_changed', (function() { 
        var zoomlevel = vici.map.getZoom();
        
        updateMarkerImages(vici.markerArr);
        
        // update line size:
        for (var i in vici.polylineArr) {
            var exponent =  (1 + zoomlevel - vici.polylineArr[i].growZoom);
            if (exponent < 0) { exponent = 0; };
            var weight = vici.polylineArr[i].nominalWeight * Math.pow(2, exponent);
            vici.polylineArr[i].strokeWeight=weight;
            vici.polylineArr[i].setOptions(vici.polylineArr[i]);
        };
        
    }).debounce(300));
    
     // disable tracklocation if map dragged:
    google.maps.event.addListener(vici.map, 'dragend', (function() {
        if (vici.gpsInitialized) {
            trackLocation(false);
        }
	}).debounce(300));
	
	// keep centred if location tracked and orientation changes:
	document.addEventListener("orientationchange", function(){
	    if (vici.trackLocation) vici.map.setCenter(vici.here.position);
	});
	
	
	// prepare aura marker (marks selected markers):
	vici.auraMarker =  new google.maps.Marker({
        position: new google.maps.LatLng(),
        map: vici.map,
        visible: false,
        icon: new google.maps.MarkerImage(base+'/images/markers.png', new google.maps.Size(32, 37), new google.maps.Point(928, 0), new google.maps.Point(16, 37)),
        zIndex: 15
    });
	
} // initialize()

//alert('rev f');
google.maps.event.addDomListener(window, 'load', initialize);
