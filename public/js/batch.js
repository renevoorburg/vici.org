

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
		onlyVisible=true;
		buttonAll.checked=false;
		buttonVisual.checked=true;
		
		if (clickedArray.length > 0 ) {
    		if (!clickedArray[0].objectvisible) {
    			clearMarkerSelection();
    			this.state_high=false;
    		};
    	};
			
		for (var i in markerArray) {
    		if (!markerArray[i].objectvisible) {
    				markerArray[i].setVisible(false);
    		}; 
    	}
	} else if (!onlyVisibleSelected && onlyVisible)  {
		onlyVisible=false;
		buttonAll.checked=true;
		buttonVisual.checked=false;
		
		for (var i in markerArray) {
    		if (!markerArray[i].objectvisible) {
    				markerArray[i].setVisible(true);
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
        createCookie("labels",  '1', null);
        break;
    case '2':
        if (map.getZoom()<11) {
            map.setMapTypeId('imperium');
        } else {
            map.setMapTypeId(google.maps.MapTypeId.HYBRID);
        }
        document.getElementById('checkLabel').selectedIndex=2;
        document.getElementById("pelagiosCC").style.display="inline";
        createCookie("labels",  '2', null);
        break;
    default:
        map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
        document.getElementById('checkLabel').selectedIndex=0;
        document.getElementById("pelagiosCC").style.display="none";
        eraseCookie('labels');
        break;
    };
};


// functions added for batch processsing:

function copyTitle()
{
    document.getElementById('sel_title').value = document.getElementById('cand_title').value;
}

function copyKind()
{
    document.getElementById('sel_kind').selectedIndex = document.getElementById('cand_kind').selectedIndex;
    document.getElementById('sel_visible').selectedIndex = document.getElementById('cand_visible').selectedIndex;
}

function copyLocation()
{
    document.getElementById('sel_loc').value = document.getElementById('cand_loc').value;
    document.getElementById('sel_accuracy').selectedIndex = document.getElementById('cand_accuracy').selectedIndex;
}

function copySummary()
{
    document.getElementById('sel_summary').value = document.getElementById('cand_summary').value;
}

function appendSummary()
{
    document.getElementById('sel_summary').value = document.getElementById('sel_summary').value + ' ' + document.getElementById('cand_summary').value;
}

function copyAnnotation()
{
    document.getElementById('sel_annotation').value = document.getElementById('cand_annotation').value;
}

function appendAnnotation()
{
    document.getElementById('sel_annotation').value = document.getElementById('sel_annotation').value + ' ' + document.getElementById('cand_annotation').value;
}

function copyExtIds()
{
    document.getElementById('sel_extids').value = document.getElementById('cand_extids').value;
}

function appendExtIds()
{
    document.getElementById('sel_extids').value = document.getElementById('sel_extids').value + ' ' + document.getElementById('cand_extids').value;
}

function copyChangenote()
{
    document.getElementById('sel_changenote').value = document.getElementById('cand_changenote').value;
}


// write current marker pos when marker is dragged
function updateMarkerPosition(latLng) 
{
  document.getElementById('cand_loc').value = [
    latLng.lat(),
    latLng.lng()
  ].join(', ');
}

function requestAroundLatLong(lat,lng,km)
{
   // angle per km = 360 / (2 * pi * 6378) = 0.0089833458
   var angle=km* 0.0089833458;
   var myRequest = new panoramio.PhotoRequest({
      'rect': {'sw': {'lat': Number(lat)-angle, 'lng': Number(lng)-angle}, 'ne': {'lat': Number(lat)+angle, 'lng': Number(lng)+angle}}
      });
      
      
      
   return myRequest;
}

function showCandidate(data) 
{
    document.getElementById('cand_id').value = data.id;
    document.getElementById('cand_title').value = data.title;
    document.getElementById('cand_loc').value = data.lat + "," + data.lng;
    document.getElementById('cand_accuracy').selectedIndex = data.accuracy;
    document.getElementById('cand_kind').selectedIndex = data.kindId-1;
    document.getElementById('cand_visible').selectedIndex = data.isvisible;
    document.getElementById('cand_summary').value = data.summary;
    document.getElementById('cand_annotation').value = data.text;
    document.getElementById('cand_extids').value = data.extIds;
    document.getElementById('cand_changenote').value = data.changeNote;
     
    map.panTo(new google.maps.LatLng(data.lat,data.lng));
    batchMarker.setPosition(new google.maps.LatLng(data.lat,data.lng));
    

    
    //var myRequest = new panoramio.PhotoRequest(requestAroundLatLong(data.lat, data.lng, 2));

    //req = new requestAroundLatLong(data.lat, data.lng, 2);
    //alert(requestAroundLatLong(data.lat, data.lng, 2).rect.sw.lat);

    // widget = new panoramio.PhotoListWidget(wapiblock, requestAroundLatLong(data.lat, data.lng, 2), panoramioOptions);
    // widget.setPosition(0);
    
    widget.setRequest(requestAroundLatLong(data.lat, data.lng, 0.5));
    widget.setPosition(0);
    
    clearSelection();
                     
    
    
    if (data.error) alert(data.error);
}

function nextCandidate(action)
{
    var params = "action=" + action + "Candidate";
    params += "&acc_id=" +  encodeURIComponent(document.getElementById('acc_id').value);
    params += "&cand_id=" + encodeURIComponent(document.getElementById('cand_id').value);
    params += "&lang=" + encodeURIComponent(document.getElementById('lang').value);
    
    if (action=="save") {
        params += "&cand_title=" +  encodeURIComponent(document.getElementById('cand_title').value);
        params += "&cand_loc=" +  encodeURIComponent(document.getElementById('cand_loc').value);
        params += "&cand_accuracy=" +  encodeURIComponent(document.getElementById('cand_accuracy').selectedIndex);
        params += "&cand_kind=" +  encodeURIComponent(document.getElementById('cand_kind').selectedIndex);
        params += "&cand_visible=" +  encodeURIComponent(document.getElementById('cand_visible').selectedIndex);
        params += "&cand_summary=" +  encodeURIComponent(document.getElementById('cand_summary').value);
        params += "&cand_annotation=" +  encodeURIComponent(document.getElementById('cand_annotation').value);
        params += "&cand_extids=" +  encodeURIComponent(document.getElementById('cand_extids').value);
        params += "&cand_changenote=" +  encodeURIComponent(document.getElementById('cand_changenote').value);
    };


    // now save params to batchprocessor.php
    if (window.XMLHttpRequest) { processor = new XMLHttpRequest(); } else if (window.ActiveXObject) { processor = new ActiveXObject("Microsoft.XMLHTTP"); }; 
	processor.onreadystatechange = function() 
	{
		if(processor.readyState==4) {
		    if (processor.status == 200 || processor.status == 304) {
  			    processResult = eval( "(" + processor.responseText + ")" ); 
  			    showCandidate(processResult);
  			}
  			
  		}
	}
	processor.open("POST","/batchprocessor.php",true);
	processor.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	processor.setRequestHeader("Content-length", params.length);
	processor.setRequestHeader("Connection", "close");
	processor.send(params);

    //processor.open("GET","/batchprocessor.php?"+params,true);
	//processor.send(null);

}

function selectionToBatchForm(selectedObject)
{
    document.getElementById('sel_id').value = selectedObject.properties.id;
    document.getElementById('sel_title').value = selectedObject.properties.title;
    document.getElementById('sel_loc').value = selectedObject.geometry.coordinates[1]+','+selectedObject.geometry.coordinates[0];
    document.getElementById('sel_accuracy').selectedIndex = selectedObject.properties.accuracy;
    document.getElementById('sel_kind').selectedIndex = selectedObject.properties.kindId-1;
    document.getElementById('sel_visible').selectedIndex = selectedObject.properties.isvisible;
    document.getElementById('sel_summary').value = selectedObject.properties.summary;
    document.getElementById('sel_annotation').value = selectedObject.properties.text;
    document.getElementById('sel_extids').value = selectedObject.properties.extIds;
}

function clearSelection()
{
    document.getElementById('sel_id').value = 0;
    document.getElementById('sel_title').value = '';
    document.getElementById('sel_loc').value = '';
    document.getElementById('sel_accuracy').selectedIndex = 0;
    document.getElementById('sel_kind').selectedIndex = 0;
    document.getElementById('sel_visible').selectedIndex = 0;
    document.getElementById('sel_summary').value = '';
    document.getElementById('sel_annotation').value = '';
    document.getElementById('sel_extids').value = '';
}

function saveSelection() 
{
    var params = "action=saveSelection";
    params += "&acc_id=" +  encodeURIComponent(document.getElementById('acc_id').value);
    params += "&cand_id=" + encodeURIComponent(document.getElementById('cand_id').value);
    params += "&lang=" + encodeURIComponent(document.getElementById('lang').value);    
    params += "&sel_id=" + encodeURIComponent(document.getElementById('sel_id').value);
    params += "&sel_title=" + encodeURIComponent(document.getElementById('sel_title').value);
    params += "&sel_loc=" + encodeURIComponent(document.getElementById('sel_loc').value);
    params += "&sel_accuracy=" + encodeURIComponent(document.getElementById('sel_accuracy').selectedIndex);
    params += "&sel_kind=" + encodeURIComponent(document.getElementById('sel_kind').selectedIndex);
    params += "&sel_visible=" + encodeURIComponent(document.getElementById('sel_visible').selectedIndex);
    params += "&sel_summary=" + encodeURIComponent(document.getElementById('sel_summary').value);
    params += "&sel_annotation=" + encodeURIComponent(document.getElementById('sel_annotation').value);
    params += "&sel_extids=" + encodeURIComponent(document.getElementById('sel_extids').value);
    params += "&sel_changenote=" +  encodeURIComponent(document.getElementById('sel_changenote').value);

    
    // now save params to batchprocessor.php
    if (window.XMLHttpRequest) { processor = new XMLHttpRequest(); } else if (window.ActiveXObject) { processor = new ActiveXObject("Microsoft.XMLHTTP"); }; 
	processor.onreadystatechange = function() 
	{
		if(processor.readyState==4) {
		    if (processor.status == 200 || processor.status == 304) {
  			    processResult = eval( "(" + processor.responseText + ")" ); 
                showCandidate(processResult);
                clearSelection();
  			}
  		}
	}
	processor.open("POST","/batchprocessor.php",true);
	processor.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	processor.setRequestHeader("Content-length", params.length);
	processor.setRequestHeader("Connection", "close");
	processor.send(params);
    
    
    
};


//

function updateInfobox(selectedMarker, highlightsArray) 
// updates the display of selected and highlighted markers in the righthand infobox
{
    var contents='';
    if (selectedMarker) {
        var id=selectedMarker.id;
        contents+='<div style="float:right; width: 18px; height: 18px; margin-right:2px; margin-top:2px;"><img src="/images/close-button.png" onclick="clearMarkerSelection()"/></div>';
        contents+= "<div style='font-size: 1.2em;font-weight:bold; margin-left:8px; margin-top:4px;'>"+lng_selected+":</div>";
        contents+='<div class="highlightbox"><a href="'+selectedMarker.url+'"><img src="'+selectedMarker.image_url+'" /></a><div class="title">'+selectedMarker.title+'</div><div class="sum">'+selectedMarker.html+'<br />[&nbsp;<a href="'+selectedMarker.url+'">'+lng_more+'</a>&nbsp;]</div></div>';
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
                highlightText+='<img src="/images/'+highlight.properties.kind+'.png" onclick="setMarkerSelection(markerArray['+highlight.properties.id+'], true);" />';
                highlightText+='<div class="title">'+highlight.properties.title+'</div>';
                highlightText+='<div class="sum">'+highlight.properties.summary+' [&nbsp;<a href="#" onclick="setMarkerSelection(markerArray['+highlight.properties.id+'], true);">'+lng_show+'</a>&nbsp;]</div>';
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
    
    // added for batch processing
    if (window.XMLHttpRequest) { objectdata=new XMLHttpRequest(); }	
	else if (window.ActiveXObject) { objectdata=new ActiveXObject("Microsoft.XMLHTTP"); };
	objectdata.onreadystatechange=function() 
	{
		if(objectdata.readyState==4) {
		    if (objectdata.status == 200 || objectdata.status == 304) {
  			    selectedObject = eval( "(" + objectdata.responseText + ")" ); 
  			    selectionToBatchForm(selectedObject);
  			}
  		}
	}
	objectdata.open("GET","/object.php?id="+selectedMarker.id+lang_urlsuffix,true);
	objectdata.send(null);
    //
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
    createCookie("focus",  marker.id, null);

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

function setFeatures(map, features) 
// creates markers from the features structure - that was loaded as JSON
// assures 'selected' infobox is shown
{
	var zoomlevel=map.getZoom();
	
	for (var i = 0; i < features.length; i++) {
        var feature = features[i];
	    if (!markerArray[feature.properties.id])
	    /* this a 'new'  marker, so 'load' it: */
	    {    
	
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
            
            markerArray[marker.id]=marker;
        }
  	}
  	
  	if (high_icon && markerArray[high_icon]) {
        setMarkerSelection(markerArray[high_icon], false);
        high_icon=false;
    };
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

function setLines(map, lines) 
{
	for (var i = 0; i < lines.length; i++) {
    	var line = lines[i];
    	var lineCoordinates=[];
    	for (var j = 0; j < line.points.length; j++) {
    		var myLatLng = new google.maps.LatLng(line.points[j][0], line.points[j][1]); 
    		lineCoordinates.push(myLatLng);   			
    	}
    	switch (line.kind) {
    	    case 'aquaduct':
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#0000FF",
    				strokeOpacity: 1.0,
    				strokeWeight: 2,
    				nominalWeight: 2,
    				growZoom: 16
  				});
    	    	break;
    	    case 'canal':
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#1111FF",
    				strokeOpacity: 1.0,
    				strokeWeight: 2,
    				nominalWeight: 3,
    				growZoom: 15
  				});
    	    	break;
    		case 'road':
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#FF0000",
    				strokeOpacity: 1.0,
    				strokeWeight: 3,
    				nominalWeight: 3,
    				growZoom: 16
  				});
  				break;
  			case 'wall':
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#C4A548",
    				strokeOpacity: 1.0,
    				strokeWeight: 3,
    				nominalWeight: 3,
    				growZoom: 16
  				});
  				break;
    		case 'road?':
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#FF0000",
    				strokeOpacity: 0.8,
    				strokeWeight: 3,
    				nominalWeight: 3,
    				growZoom: 16
  				});
  				break;
    	    default:
    	    	var polyline = new google.maps.Polyline({
    				path: lineCoordinates,
    				strokeColor: "#FF0000",
    				strokeOpacity: 0.6,
    				strokeWeight: 3,
    				nominalWeight: 3,
    				growZoom: 16
  				});
  				break;
  		};
  		polyline.setMap(map); 
  		polylineArray.push(polyline);   	
  	}
}

function getMarkers(map) 
// loads the markers from a JSON service
// calls: setFeatures
{
    var mapBounds = new google.maps.LatLngBounds;
    mapBounds = map.getBounds();

    var SW = new google.maps.LatLng();
    var NE = new google.maps.LatLng();
    SW = mapBounds.getSouthWest();
    NE = mapBounds.getNorthEast();
    var latSW = Math.floor(SW.lat());
    var lngSW = Math.floor(SW.lng());
    var latNE = Math.ceil(NE.lat());
    var lngNE = Math.ceil(NE.lng());
    var zoomlevel = map.getZoom();

    if (!pointsLoadedArray[zoomlevel+','+latSW+','+lngSW+','+latNE+','+lngNE]) {
        var jsonPoints;
        if (window.XMLHttpRequest) { jsonPoints=new XMLHttpRequest(); }	
        else if (window.ActiveXObject) { jsonPoints=new ActiveXObject("Microsoft.XMLHTTP"); };
        jsonPoints.onreadystatechange=function() {	
            if (jsonPoints.readyState==4) {
                if (jsonPoints.status == 200 || jsonPoints.status == 304) {
                    response = eval( "(" + jsonPoints.responseText + ")" ); 
                    setFeatures(map, response.features);
                    pointsLoadedArray[zoomlevel+','+latSW+','+lngSW+','+latNE+','+lngNE]=true;
                }
            }
        }
        jsonPoints.open("GET","/points.php?bounds="+latSW+","+lngSW+","+latNE+","+lngNE+"&zoom="+zoomlevel+lang_urlsuffix,true);
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
    //if (parseInt(readCookie("labels"))>0) {
    
    //};
    
    
    markerArray = new Array();
    pointsLoadedArray = new Array();
    polylineArray = new Array();
	hereArray = new Array();
	glowArray = new Array();
	clickedArray = new Array();
	highlightsArray=new Array();
    
    keepFocus=false;
    locationAware=true;
    onlyVisible=false;
    
    var zoomlevel = mapOptions.zoom;
    google.maps.event.addListenerOnce(map, 'tilesloaded', (function () 
    { 
        getMarkers(this);
    }));
	google.maps.event.addListener(map, 'bounds_changed', (function () 
	{
	    // get new markers;
	    getMarkers(map); 
	    
	    // write position and zoom to cookies to be able to restore current map
	    var mapCenter = new google.maps.LatLng;
	    mapCenter = this.getCenter();
	    createCookie("center",  mapCenter.toUrlValue(), null);
	    createCookie("zoom", map.getZoom(), null);
	   
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
        
        if (zoomlevel!=prevZoomLevel) 
        // zoom changed
        {
            for (var i in markerArray) 
            // set proper image:
            {
                markerArray[i].selectImage(zoomlevel); 
            }

            for (var i = 0; i < polylineArray.length; i++) 
            // set proper line width:
            {
                var exponent =  (1 + zoomlevel - polylineArray[i].growZoom);
                if (exponent < 0) { exponent = 0; };
                var weight = polylineArray[i].nominalWeight * Math.pow(2, exponent);
                polylineArray[i].strokeWeight=weight;
                polylineArray[i].setOptions(polylineArray[i]);
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
	
	// set marker for batch processing:
	
	batchMarker = new google.maps.Marker({
        position: new google.maps.LatLng(52.058111,5.161643),
        map: map,
        draggable: true,
        icon: new google.maps.MarkerImage ('/images/candidate.png', new google.maps.Size(32, 37), null, new google.maps.Point(16, 37), null),
        zIndex: 15
    });
	
	google.maps.event.addListener(batchMarker, 'drag', function() {
        updateMarkerPosition(batchMarker.getPosition());
    });
    google.maps.event.addListener(batchMarker, 'dragend', function() {
        map.panTo(batchMarker.getPosition());
        widget.setRequest(requestAroundLatLong(batchMarker.getPosition().lat(), batchMarker.getPosition().lng(), 0.5));
        widget.setPosition(0);
    
        
    });

	
	
	// end set marker for batch proc.
	
	// panoramio
	var myRequest = new panoramio.PhotoRequest({
            //'tag': 'fectio,vechten,romeins,fort',
            'rect': {'sw': {'lat': 52.052396, 'lng': 5.153403}, 
                     'ne': {'lat': 52.060396, 'lng': 5.173403}}});


        panoramioOptions = {
            'width': 300,
            'height': 450,
            'position' : 'left',
            'columns': 2,
            'rows': 3,
            'croppedPhotos': true
        };
    
        wapiblock = document.getElementById('wapiblock');
        widget = new panoramio.PhotoListWidget(wapiblock, myRequest, panoramioOptions);
        widget.setPosition(0);
	
	//
	
	
	nextCandidate('skip');
	
	
	var jsonLines;
	if (window.XMLHttpRequest) { jsonLines=new XMLHttpRequest(); }	
	else if (window.ActiveXObject) { jsonLines=new ActiveXObject("Microsoft.XMLHTTP"); };
	jsonLines.onreadystatechange=function() {
		if(jsonLines.readyState==4) {
		    if (jsonLines.status == 200 || jsonLines.status == 304) {
  			    response = eval( "(" + jsonLines.responseText + ")" ); 
  			    setLines(map, response.lines);
  			}
  		}
	}
	jsonLines.open("GET","/lines.php",true);
	jsonLines.send(null);
	
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
