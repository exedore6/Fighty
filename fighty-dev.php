<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
$maplibId = (int)$_GET['id'];
?>
<Module>
  <ModulePrefs title="Fighty" height="500">
    <Require feature="wave-preview" /> 
    <Require feature="dynamic-height" />
  </ModulePrefs>
  <Content type="html">
    <![CDATA[
    
	<div id="map" style="width: 600px; height: 500px"></div>
	<button onClick="wave.getState().reset()">Reset</button>
	<button onClick="addMarker()">Add Marker</button>

	<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAJZSCiJrV2x8Q-D4AwKLeqBRtwJtrMVWsjag3-LZr98ArjG2fyhRp5a1A2MD7yqmvDZI_58K4k3Q3QA"></script>
  <script src="http://www.maplib.net/api/api?v=1.15"  type="text/javascript"></script>
  <script src="json2.js" type="text/javascript"></script>

  <script type="text/javascript">
  
  map = new Mmap(<?php echo $maplibId?>,document.getElementById("map"),null,null,7);
  map.initMap();
  gadgets.window.adjustHeight();
  
  </script>
  
  <script type="text/javascript">
    GEvent.addListener(map.map, "moveend", handlePan);
	  GEvent.addListener(map.map, "zoomend", handleZoom);
    map.map.disableScrollWheelZoom();  	
    var cltMarkers={};
	  var svrMarkers={};
  
    function init() {
      if (wave && wave.isInWaveContainer()) {
        wave.setStateCallback(stateUpdated); 
      }
    }

    function addMarker() {
      var center = map.map.getCenter();
      delta = {};
      var mrkr = {lat:center.lat(),lng:center.lng()};
      delta['mrkr_'+markeridx]=JSON.stringify(mrkr);
      markeridx = markeridx + 1
      delta['markeridx']=markeridx;
      wave.getState().submitDelta(delta);
    }

    function syncMarkers() {
      // Initialize
      if (!wave.getState().get('markeridx')) {
        markeridx = 0 ;
      } else {
        markeridx = new Number(wave.getState().get('markeridx')); 
      }
      // Seperate marker keys
      keys = wave.getState().getKeys();
      svrMarkers = {};
      for (key in keys) {
        var kStr = new String(keys[key]);
        if (kStr.match('^mrkr_.*')) {
          svrMarkers[keys[key]] = JSON.parse(wave.getState().get(keys[key]));  
		    }
      }
    
      for (key in svrMarkers) {
        var kStr = new String(key);
        if (kStr.match('^mrkr_.*')) {
          // Create local markers, if needed
          if (!cltMarkers[key]) {
            cltMarkers[key] = svrMarkers[key];
            var mk_ctr = new GLatLng(svrMarkers[key].lat, svrMarkers[key].lng);
            var mk_opt = {};
            mk_opt['draggable'] = true;
            if (cltMarkers[key].label) {
              mk_opt['title'] = cltMarkers[key].label;
            } else {
              mk_opt['title'] = key;
            }
            var marker = new GMarker(mk_ctr, mk_opt);
            marker['mkrIdx'] = key;
            GEvent.addListener(marker,"dragend", moveMarker);
            GEvent.addListener(marker,"click", clickMarker);
            GEvent.addListener(marker,"infowindowbeforeclose", preCloseMarker);
            cltMarkers[key]['marker'] = marker;
            map.map.addOverlay(marker);
          }
          // Update changed markers 
          mkrState = JSON.parse(wave.getState().get(key));
          // Move markers that need moving
          if ((mkrState.lat != cltMarkers[key].lat) || (mkrState.lng != cltMarkers[key].lng)) {
            var newCenter = new GLatLng(wave.getState().get(key).lat, wave.getState().get(key).lng);
            cltMarkers[key].lat = mkrState.lat;
            cltMarkers[key].lng = mkrState.lng;
            cltMarkers[key].marker.setLatLng(newCenter);
          }
          // Title markers that need a new title
			 
          if (mkrState.label != cltMarkers[key].label) {
            cltMarkers[key].label = mkrState.label;
            var mk_ctr = new GLatLng(mkrState.lat,mkrState.lng);
            var mk_opt = {};
            mk_opt['draggable'] = true;
            mk_opt['title'] = mkrState.label;
            var marker = new GMarker(mk_ctr,mk_opt);
            GEvent.addListener(marker,"dragent", moveMarker);
            GEvent.addListener(marker,"click", clickMarker);
            marker['mkrIdx'] = key;
            if(cltMarkers[key]['marker']) {
              cltMarkers[key]['marker'].remove();
              cltMarkers[key]['marker'] = null;
              cltMarkers[key]['marker'] = marker;
              map.map.addOverlay(marker);
            }
          }
        }
      }
      // Clean up local markers       
      for (key in cltMarkers) {
        var kStr = new String(key);
        if (kStr.match('^mrkr_.*')) {
          if (!svrMarkers[key]) {
            if (cltMarkers[key]['marker']) {
              cltMarkers[key]['marker'].closeInfoWindow();
              cltMarkers[key]['marker'].remove();        
              delete cltMarkers[key];

            }
          } 
        } 
      }

    }


    function moveMarker() {
	    var key = this['mkrIdx'];
	    cltMarkers[key]['lat']=this.getLatLng().lat();
	    cltMarkers[key]['lng']=this.getLatLng().lng();
	    var delta = {};
	    var markerKey = {}
	    markerKey['lat'] = cltMarkers[key].lat;
	    markerKey['lng'] = cltMarkers[key].lng;
	    markerKey['label'] = cltMarkers[key].label;
	    delta[key]=JSON.stringify(markerKey);
	    wave.getState().submitDelta(delta);
    }
		
	  function clickMarker() {
	    map.map.closeInfoWindow();
	    this.openInfoWindowHtml("Label: <input value='" + this.getTitle() +"' id='ttl_"+this['mkrIdx']+"' type='text' /> </br>Icon URL: <input type='text' id='ico_"+this['mrkIdx']+"' /></br><a onClick=removeMarker('" + this['mkrIdx'] + "')>(x)</a>");
	  }

    function setLabel(markerIdx, label) {		  
      //var mrkr_record = JSON.parse(wave.getState().get(markerIndex));
	    //var mrkr_record=cltMarkers[markerIdx];
      var delta = {};
      var new_mrkr_record = JSON.parse(wave.getState().get(markerIdx));
      new_mrkr_record['label'] = String(label);
      delta[markerIdx] = JSON.stringify(new_mrkr_record);
      wave.getState().submitDelta(delta);
    }

    function removeMarker(markerIndex) {
      map.map.closeInfoWindow();
	    delta = {};
      delta[markerIndex] = null;
      wave.getState().submitDelta(delta);                 
    }
    
    function preCloseMarker() {
      var label = document.getElementById("ttl_"+this['mkrIdx']).value;
      setLabel(this['mkrIdx'], label);
    }
	
	  function stateUpdated(state) {		  
      setTimeout(syncZoom,0);
      setTimeout(syncPan,0);
      setTimeout(syncMarkers,0);
	  }

	  function handleZoom(oldzoom,newzoom) {
      var delta = {};
      delta['zoomlevel']=JSON.stringify(newzoom);
      wave.getState().submitDelta(delta);
	  }
	
    function handlePan() {
      var delta = {};
      center=map.map.getCenter();
      centerPoint = [center.lat(),center.lng()]
      delta['mapcenter']=JSON.stringify(centerPoint);
      wave.getState().submitDelta(delta);
    }

    function syncZoom() {
      var newZoom = JSON.parse(wave.getState().get('zoomlevel'));
      var oldZoom = map.map.getZoom();
      if (oldZoom != newZoom) {      
        map.map.setZoom(newZoom);
      }
    }

    function syncPan() {        
      var oCenter = map.map.getCenter();
      var newCenter = JSON.parse(wave.getState().get('mapcenter'));
      var gCenter = new GLatLng(newCenter[0],newCenter[1]);
      if (JSON.stringify(oCenter) == JSON.stringify(gCenter)) {
        map.map.setCenter(gCenter);
      }
    }
	
	  gadgets.util.registerOnLoadHandler(init);
	
	</script>
	
	
    ]]>
  </Content>
</Module>
