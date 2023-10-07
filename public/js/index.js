

Function.prototype.debounce = function (threshold, execAsap) {
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

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+escape(value)+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return unescape(c.substring(nameEQ.length,c.length));
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function isVisible(obj) {
    if (obj == document) return true
    if (!obj) return false
    if (!obj.parentNode) return false
    if (obj.style) {
        if (obj.style.display == 'none') return false
        if (obj.style.visibility == 'hidden') return false
    }
    
    //Try the computed style in a standard way
    if (window.getComputedStyle) {
        var style = window.getComputedStyle(obj, "")
        if (style.display == 'none') return false
        if (style.visibility == 'hidden') return false
    }
    
    //Or get the computed style using IE's silly proprietary way
    var style = obj.currentStyle
    if (style) {
        if (style['display'] == 'none') return false
        if (style['visibility'] == 'hidden') return false
    }
    return isVisible(obj.parentNode)
};

function setOnlyVisible(onlyVisibleSelected) {
	var buttonAll = document.getElementById("radioAll");
	var buttonVisual = document.getElementById("radioVisual");
	if (onlyVisibleSelected && !onlyVisible) {
	    createCookie("onlyVisible",  '1', null);
		onlyVisible=true;
		buttonAll.checked=false;
		buttonVisual.checked=true;
		
		if (clickedArray.length > 0 ) {
    		if (!clickedArray[0].objectvisible) {
    			clearMarkerSelection();
    			this.state_high=false;
    		};
    	};
			
		for (var i in markerArr) {
    		if (!markerArr[i].objectvisible) {
    				markerArr[i].setVisible(false);
    		}; 
    	}
	} else if (!onlyVisibleSelected && onlyVisible)  {
	    createCookie("onlyVisible",  '0', null);
		onlyVisible=false;
		buttonAll.checked=true;
		buttonVisual.checked=false;
		
		for (var i in markerArr) {
    		if (!markerArr[i].objectvisible) {
    				markerArr[i].setVisible(true);
    		}; 
    	};
	};
};

function toggleFocus() {
	keepFocus=!keepFocus;
	var button = document.getElementById("focus");
	if (keepFocus) { 
		map.setCenter(hereArray[0].position);
		button.src='/images/btn_focus_active.png'; 
	} else {
		button.src='/images/btn_focus_normal.png'; 
	};
};

function setBackground(option) {
   switch(option) {
    case '0':
        map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
        document.getElementById('checkLabel').selectedIndex=0;
        document.getElementById("pelagiosCC").style.display="none";
        eraseCookie('labels');
        break;
    case '1':
        map.setMapTypeId(google.maps.MapTypeId.HYBRID);
        document.getElementById('checkLabel').selectedIndex=1;
        document.getElementById("pelagiosCC").style.display="none";
        createCookie("labels",  '1', 30);
        break;
    case '2':
        if (map.getZoom()<11) {
            map.setMapTypeId('imperium');
        } else {
            map.setMapTypeId(google.maps.MapTypeId.HYBRID);
        }
        document.getElementById('checkLabel').selectedIndex=2;
        document.getElementById("pelagiosCC").style.display="inline";
        createCookie("labels",  '2', 30);
        break;
    default:
        map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
        document.getElementById('checkLabel').selectedIndex=0;
        document.getElementById("pelagiosCC").style.display="none";
        eraseCookie('labels');
        break;
    };
};

function updateInfobox(selectedMarker, highlightsArray) 
// updates the display of selected and highlighted markers in the righthand infobox
{
    var contents='';
    if (selectedMarker) {
        var id=selectedMarker.id;
        contents+='<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img src="/images/close-button.png" onclick="clearMarkerSelection()"/></div>';
        contents+= "<div style='font-size: 1.2em;font-weight:bold; margin-left:8px; margin-top:4px;'>"+lng_selected+":</div>";
        contents+= '<div class="highlightbox"><a href="'+selectedMarker.url+'"><img class="marker" src="'+selectedMarker.image_url+'" /></a><div class="title">'+selectedMarker.title+'</div>';
        
        
        if (selectedMarker.picture) {
            contents+= '<div style="margin:2px 0px 0px 37px; position: relative"><a href="'+selectedMarker.url+'"><img src="http://static.vici.org/cache/220x124-2'+selectedMarker.picture+'"></a>';
            contents+= '<div style="position: absolute; bottom:3px; left:0px; right:0px; background-image: url(/images/black_50.png);">'+selectedMarker.html+' [&nbsp;<a href="'+selectedMarker.url+'">'+lng_more+'</a>&nbsp;]</div>';
            contents+= '</div>';
        } else {
            contents+= '<div class="sum">'+selectedMarker.html+' [&nbsp;<a href="'+selectedMarker.url+'">'+lng_more+'</a>&nbsp;]</div>';
        }
        contents+= '</div>';
    } else {
        var id=0;
    };
    var highlightText='';
    if (highlightsArray.length>0) 
    // there are highlights
    {
        for (var i = 0; i < highlightsArray.length; i++) 
        {   
            var highlight = highlightsArray[i];
            if (highlight.properties.id!=id) 
            // display highlight if not a selected marker
            {
                highlightText+='<div class="highlightbox">';
                highlightText+='<img class="marker" src="/images/'+highlight.properties.kind+'.png" onclick="setMarkerSelection(markerArr['+highlight.properties.id+'], true);" />';
                highlightText+='<div class="title" style="margin-top:0px">'+highlight.properties.title+'</div>';
                
                if (highlight.properties.img) {
                    highlightText+='<div style="margin:2px 0px 0px 37px;position: relative"><img src="http://static.vici.org/cache/220x124-2'+highlight.properties.img+'" onclick="setMarkerSelection(markerArr['+highlight.properties.id+'], true);">';
                    highlightText+='<div style="position: absolute; bottom:3px; left:0px; right:0px; background-image: url(/images/black_50.png);">'+highlight.properties.summary+' [&nbsp;<a href="#" onclick="setMarkerSelection(markerArr['+highlight.properties.id+'], true);">'+lng_show+'</a>&nbsp;]</div>';
                    highlightText+= '</div>';
                } else {
                    highlightText+='<div class="sum">'+highlight.properties.summary+' [&nbsp;<a href="#" onclick="setMarkerSelection(markerArr['+highlight.properties.id+'], true);">'+lng_show+'</a>&nbsp;]</div>';
                }
                highlightText+='</div>'
            }
        }	
    }
    if (highlightText) {
        if (contents) 
        // add separator
        {
            contents+='<div style="width:100%; height:1px; background-color:#666;margin-top:2px"></div>';
        };
        contents+='<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img src="/images/close-button.png" onclick="clearHighlights()"/></div>';
        contents+="<div style='font-size: 1.2em;font-weight:bold; margin-left:8px; margin-top:4px;'>"+lng_featured+":</div>"+highlightText;
    }; 
    document.getElementById('rightcol').innerHTML=contents;
}

function setMarkerSelection(marker, center) 
// deals with display of the 'selected' setMarkerSelection
{   
    if (clickedArray.length >0 ) 
    // restore state of previously clicked marker:
    {
        if (clickedArray[0].id==marker.id) {
            window.location.href=marker.url
        };    
    
        var zoomlevel=map.getZoom();
        clickedArray[0].restoreState(map.getZoom());
        clickedArray.pop();
    };
    
    updateInfobox(marker, highlightsArray);
    
    // track currently clicked marker:
    clickedArray.push(marker);
    marker.raiseState();
    createCookie("focus",  marker.id, 30);

    // add a glow marker
    var glowLatLng = marker.position;
    if (glowArray.length > 0) {
        glowArray[0].position = glowLatLng;
        glowArray[0].setVisible(true);
        glowArray[0].setIcon(glowArray[0].icon);
    } else {
        var glowImage= new google.maps.MarkerImage ('/images/glow.png', new google.maps.Size(32, 37), null, new google.maps.Point(16, 37), null);
        glowMarker =new google.maps.Marker({
            position: glowLatLng,
            map: map,
            icon: glowImage,
            zIndex: 15
        });
        google.maps.event.addListener(glowMarker, 'click', function() 
            // clicks on the glowmarker should display the glowing items fulltext page
            {
                window.location.href=clickedArray[0].url

            }
        );
        glowMarker.setIcon(glowMarker.icon);
        glowArray.push(glowMarker);
    }; 
    
    if (center) {
        var zoomlevel=map.getZoom();
        map.panTo(marker.position);
        if (zoomlevel<marker.zoom_normal) { map.setZoom(marker.zoom_normal); };
    }  	
}

function clearMarkerSelection() 
// unselects any selected marker and updates display accordingly
{
	if (glowArray.length > 0) {
		glowArray[0].setVisible(false);
	};
	if (clickedArray.length > 0 ) {
        clickedArray[0].restoreState(map.getZoom());
    	clickedArray.pop();
    };
    eraseCookie('focus');
    eraseCookie('minzoom');
    
    updateInfobox(null, highlightsArray);  
}

function setImages(map, images) 
// currently not used
{
	for (var i = 0; i < images.length; i++) {
		var image = images[i];
		var boundaries = new google.maps.LatLngBounds(new google.maps.LatLng(image.sw[0],image.sw[1]), new google.maps.LatLng(image.ne[0],image.ne[1]));
		var imageOverlay = new google.maps.GroundOverlay(image.url, boundaries);
		imageOverlay.setMap(map);  
	}
}

function clearHighlights()
{
    highlightsArray.length = 0;
    updateInfobox(clickedArray[0], highlightsArray);
};

function getHighlights(bounds, zoom) 
// loads data to the highlightsArray for given bounds and zoom
{
    if (window.XMLHttpRequest) { jsonHighlight=new XMLHttpRequest(); }	
	else if (window.ActiveXObject) { jsonHighlight=new ActiveXObject("Microsoft.XMLHTTP"); };
	jsonHighlight.onreadystatechange=function() 
	{
		if(jsonHighlight.readyState==4) {
		    if (jsonHighlight.status == 200 || jsonHighlight.status == 304) {
  			    //highlights = eval( "(" + jsonHighlight.responseText + ")" ); 
  			    highlightsArray = eval( "(" + jsonHighlight.responseText + ")" ).features; 
  			    updateInfobox(clickedArray[0], highlightsArray);
  			}
  		}
	}
	jsonHighlight.open("GET","/highlight.php?bounds="+bounds+"&zoom="+zoom+lang_urlsuffix,true);
	jsonHighlight.send(null);
}

function getData(map) {

    function setFeatures(d) {
        createMarkers(d);
        createLines(d);
    }

    function createMarkers(d) {
        var zoomlevel=map.getZoom();
        for (var i in d.features) {
            var feature = d.features[i];
            if (!markerArr[feature.properties.id]) {
                /* this a 'new'  marker, so 'load' it: */
                var imageUrl='/images/'+feature.properties.kind+'.png';
                var image= new google.maps.MarkerImage (imageUrl, new google.maps.Size(32, 37), null, new google.maps.Point(16, 37), null);
                var smallImage = new google.maps.MarkerImage ('/images/'+feature.properties.kind+'_minimal.png', new google.maps.Size(12, 16), null, new google.maps.Point(6, 6), null);
            
                var myLatLng = new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]);
                var marker = new google.maps.Marker({
                    id: feature.properties.id,
                    url: feature.properties.url,
                    position: myLatLng,
                    map: map,
                    title: feature.properties.title,
                    html: feature.properties.summary,
                    objectvisible: (feature.properties.isvisible=="1"),
                    zoom_small: parseInt(feature.properties.zoomsmall),
                    zoom_normal: parseInt(feature.properties.zoomnormal),
                    image_small: smallImage,
                    image_normal: image,
                    state_high: false,
                    icon: image,
                    image_url: imageUrl,
                    picture: feature.properties.img,
                    zIndex: feature.properties.zindex,
                    selectImage: function(zoomlevel) {
                        if (! this.state_high) {
                            if (zoomlevel < this.zoom_small) {
                                this.icon = this.image_small;
                                this.visible = false;
                            } else if (zoomlevel >= this.zoom_normal) {
                                this.icon = this.image_normal;
                                this.visible = true;
                            } else {
                                this.icon = this.image_small;
                                this.visible = true;
                            }; 
                        } else {
                            this.icon=this.image_normal;
                            this.visible = true;
                        }; 
                        if (!this.objectvisible && onlyVisible) { this.visible = false };
                        this.setIcon(this.icon);
                    },
                    restoreState: function(zoomlevel) {
                        this.state_high=false;
                        this.zIndex=this.zIndex-10;
                        this.selectImage(zoomlevel); 
                        //this.setIcon(this.icon);
                    },
                    raiseState: function() {
                        this.state_high=true;
                        this.zIndex=this.zIndex+10;
                        this.icon=this.image_normal;
                        this.setIcon(this.icon);
                    }
                });
                marker.selectImage(zoomlevel);
                google.maps.event.addListener(marker, 'click', function() {setMarkerSelection(this, false)});
                markerArr[marker.id]=marker;
            }
        }
        if (high_icon && markerArr[high_icon]) {
            setMarkerSelection(markerArr[high_icon], false);
            high_icon=false;
        }
    } // createMarkers

    function createLines(d) {
        for (var i in d.lines) {
            var line = d.lines[i];
            if (!polylineArr[line.id] || (polylineArr[line.id] && polylineArr[line.id].expire <= zoomlevel)) {
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
                if (polylineArr[line.id]) {
                    // update polyline:
                    polylineArr[line.id].setOptions(polylineOptions);   
                } else {
                    // create new polyline:
                    var polyline = new google.maps.Polyline(polylineOptions);
                    polyline.setMap(map); 
                    google.maps.event.addListener(polyline, 'click', function() {setMarkerSelection(markerArr[this.markerId], true)});
                    polylineArr[line.id] = polyline; 	
                }
            }
        }
    } // end createLines

    var latSW = Math.floor(map.getBounds().getSouthWest().lat());
    var lngSW = Math.floor(map.getBounds().getSouthWest().lng());
    var latNE = Math.ceil(map.getBounds().getNorthEast().lat());
    var lngNE = Math.ceil(map.getBounds().getNorthEast().lng());
    var zoomlevel = map.getZoom();

    if (!pointsLoadedArray[zoomlevel+','+latSW+','+lngSW+','+latNE+','+lngNE]) {
        var jsonPoints;
        if (window.XMLHttpRequest) { jsonPoints=new XMLHttpRequest(); }	
        else if (window.ActiveXObject) { jsonPoints=new ActiveXObject("Microsoft.XMLHTTP"); };
        jsonPoints.onreadystatechange=function() {	
            if (jsonPoints.readyState==4) {
                if (jsonPoints.status == 200 || jsonPoints.status == 304) {
                    response = eval( "(" + jsonPoints.responseText + ")" ); 
                    setFeatures(response);
                    pointsLoadedArray[zoomlevel+','+latSW+','+lngSW+','+latNE+','+lngNE]=true;
                }
            }
        }
        jsonPoints.open("GET","/data.php?bounds="+latSW+","+lngSW+","+latNE+","+lngNE+"&zoom="+zoomlevel+lang_urlsuffix,true);
        jsonPoints.send(null);
	}
}

function errorGettingPosition(err) 
{
	if(err.code==1) {
		locationAware=false;
	}
}

function initialize() 
{
    // read stored position and zoomlevel
    var storedLatlngStr=readCookie("center");
    var storedZoom=parseInt(readCookie("zoom"));
    var requiredZoom=parseInt(readCookie("minzoom"));
    var requiredZoom=parseInt(readCookie("minzoom"));
    high_icon=parseInt(readCookie("focus"));

    onlyVisible=(parseInt(readCookie("onlyVisible"))==1);
    if (onlyVisible) {
        document.getElementById("radioAll").checked=false;
        document.getElementById("radioVisual").checked=true;
    } else {
        document.getElementById("radioAll").checked=true;
        document.getElementById("radioVisual").checked=false;
    }
    
    // where is the initial center of the map: (but if we have a focus cookie this will be the center in a later setFeatures call)
    if (storedLatlngStr) {
        var storedLatlngArr=storedLatlngStr.replace('(', '').replace(')', '').split(",", 2);
        var latlng = new google.maps.LatLng(parseFloat(storedLatlngArr[0]),parseFloat(storedLatlngArr[1]));
    } else {
	    var latlng = new google.maps.LatLng(50.84,5.69);
	};
	
	// what initial zoomlevel: 
    if (!storedZoom) { storedZoom=12; };
    if (requiredZoom && (storedZoom < requiredZoom) ) { 
        storedZoom=requiredZoom; 
    };
	
	// create map
	var mapOptions = {
		center: latlng,
    	zoom: storedZoom,
    	minZoom: 4,
        streetViewControl: true,
        mapTypeControl: false,
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };
    
    // global vars:
    map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
    mapImperium = new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
          return "http://pelagios.dme.ait.ac.at/tilesets/imperium/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
        },
      tileSize: new google.maps.Size(256, 256),
      isPng: true,
      minZoom: 4,
      maxZoom: 11,
      name: "Imperium",
      alt: "Imperium Romanum"
      });
      
      map.mapTypes.set('imperium', mapImperium);
      //map.setMapTypeId('imperium');
    
    setBackground(readCookie("labels"));

    markerArr = new Array();
    pointsLoadedArray = new Array();
    polylineArr = new Array();
	hereArray = new Array();
	glowArray = new Array();
	clickedArray = new Array();
	highlightsArray=new Array();
    
    keepFocus=false;
    locationAware=true;
    
    var zoomlevel = mapOptions.zoom;
    google.maps.event.addListenerOnce(map, 'tilesloaded', (function () 
    { 
        getData(this);
    }));
	google.maps.event.addListener(map, 'bounds_changed', (function () 
	{
	    // get new markers;
	    getData(map);
	    
	    // write position and zoom to cookies to be able to restore current map
	    var mapCenter = new google.maps.LatLng;
	    mapCenter = this.getCenter();
	    createCookie("center",  mapCenter.toUrlValue(), 30);
	    createCookie("zoom", map.getZoom(), 30);
	   
	    // deal with changed area:
        var mapBounds = new google.maps.LatLngBounds;
        mapBounds = this.getBounds();
        
        // checks if highlighted icon still visible
        if ( clickedArray.length > 0 ) {
            if (!this.getBounds().contains(clickedArray[0].position)) { clearMarkerSelection();};
        };
        
        // check changes in zoomlevel:
        var prevZoomLevel;
        prevZoomLevel = zoomlevel;
        zoomlevel=map.getZoom();
        
        if (zoomlevel!=prevZoomLevel) {
            // set proper size image:
            for (var i in markerArr) {
                markerArr[i].selectImage(zoomlevel); 
            }

            // set proper line width:
            for (var i in polylineArr) {
                var exponent =  (1 + zoomlevel - polylineArr[i].growZoom);
                if (exponent < 0) { exponent = 0; };
                var weight = polylineArr[i].nominalWeight * Math.pow(2, exponent);
                polylineArr[i].strokeWeight=weight;
                polylineArr[i].setOptions(polylineArr[i]);
            };
            
            if (keepFocus && locationAware) { map.setCenter(hereArray[0].position); };
                      
            if ((zoomlevel>=11) && (document.getElementById("checkLabel").value=='2')) {
                map.setMapTypeId(google.maps.MapTypeId.HYBRID);
                document.getElementById("checkLabel").options[2].disabled=true;
            };
            if (zoomlevel<11) {
                document.getElementById("checkLabel").options[2].disabled=false;
                if (document.getElementById("checkLabel").value=='2') {
                    map.setMapTypeId('imperium');
                };
            };
            
            
        }
        
        //get hightlights for this area:
        getHighlights(mapBounds.toUrlValue(), zoomlevel);
	}).debounce(300));
	
    if (navigator.geolocation && !storedLatlngStr) { // && false is debug
    	var hereIcon = new google.maps.MarkerImage ('/images/here.png', new google.maps.Size(15, 15), null, new google.maps.Point(8, 8), null);
    	
    	navigator.geolocation.getCurrentPosition(
    		function(position) {
        		var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				map.setCenter(pos);
        		var here = new google.maps.Marker({
        			position: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
        			map: map,
        			icon: hereIcon,
        		});
        		here.setIcon(here.icon);
        		hereArray.push(here);	
        	}, 
    		errorGettingPosition,
        	{'enableHighAccuracy':true,'timeout':10000,'maximumAge':20000});
    	
    	var watch_id = navigator.geolocation.watchPosition(
    		function(position) {
				var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        		if (hereArray.length >0) {
        			hereArray[0].position = pos;
        			hereArray[0].setIcon(hereArray[0].icon);
        			if (keepFocus) { map.setCenter(pos); };
        		} else {
        			map.setCenter(pos);
        			var here = new google.maps.Marker({
        				position: pos,
        				map: map,
        				icon: hereIcon,
        			});
        			here.setIcon(here.icon);
        			hereArray.push(here);
        		};	
        	}, 
        	errorGettingPosition,
        	{'enableHighAccuracy':true,'timeout':10000,'maximumAge':20000} 
        );
    } else {
		locationAware=false;
    }

}
