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
  <script src="http://www.savevsgeek.com/json2.js" type="text/javascript"></script>
  
  <script type="text/javascript">
  
  var localMap;
  var mmap = new Mmap(<?php echo $maplibId ?>, document.getElementById("map"), null,null,7);
  mmap.initMap();

  function Fighty(mapLibMap) {
      this.mmap_ = mapLibMap;
      this.map_ = this.mmap_.map;
      this.markers_ = new Array();
      this.markerIdx = this.markers_.length;
    }
    
    Fighty.prototype.syncMarkers = function(state) {
      console.log(state.getKeys());
      // Add markers that need to be added
        // Remove markers that need to be removed
        // Move markers that need moving
        // Modify markers that need changing (icon, label opts)
      
    }
 
    Fighty.prototype.syncZoom = function(newZoom) {
    
    }
    
    Fighty.prototype.syncCenter = function(newCenter) {
    
    }
   
    
    function init() {
      if (wave && wave.isInWaveContainer()) {
        wave.setStateCallback(stateUpdated,this); 
        wave.setModeCallback(modeUpdated,this);
      }
    }
	
	  function stateUpdated(state) {
      // Initialize local map
      if (!localMap) {
        localMap = new Fighty(mmap);
      }
      // Sync markers
      localMap.syncMarkers(state);
      // Sync Zoom, Center, if appropriate
      localMap.syncCenter(state);
      localMap.syncZoom(state);
    }
    
    function modeUpdated() {
    }

    function addMarker() {
      var currentCenter = localMap.map_.getCenter();
      var markerPoint = (currentCenter.lat(),currentCenter.lng());
      var markerKey = "mrkr_"+localMap.markerIdx ;
      localMap.markerIdx = localMap.markerIdx+1;
      var markerObj = {};
      markerObj['markerPoint']=markerPoint;
      var delta = {};
      delta[markerKey] = JSON.stringify(markerObj);
      console.log(delta);
      wave.getState().submitDelta(delta);
    }
    
	  gadgets.util.registerOnLoadHandler(init);
	
	</script>
	
	
    ]]>
  </Content>
</Module>
