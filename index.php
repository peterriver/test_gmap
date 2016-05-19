<!DOCTYPE html>
<html lang="fr">
  <head><meta charset="UTF-8">
  <title>Test gmap</title>
  <style type="text/css">
    #myform label {
      color: #0f9d58;
      font-size: 20px;
      font-style: normal;
      font-weight: bold;
      line-height: 22px;
    }
    
    #myfile {
      color:#117dc2;
      font-size: 14px;
      font-style: normal;
      font-weight: bold;
      line-height: 22px;
    }

    .submit {
      cursor: pointer;
      display: table;
      background: #00b5cd none repeat scroll 0 0;
      color: #ffffff;
      font-size: 13px;
      border: medium none;
      border-radius: 5px;
      font-family: sourceSans;
      font-size: 13px;
      line-height: 1.8em;
      min-width: 47px;
      overflow: hidden;
      padding: 11px 23px 9px;
      position: relative;
      transition: all 0.5s ease-out 0s;
    }

	.submit:hover {
	  background-color: #f1f1f1;
	  color: #5a5a5a;
	}

	#message {
	  color: #db4437;
	  font-size: 20px;
      font-style: normal;
      font-weight: bold;
      line-height: 22px;
	}
  </style>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBrB6-FAlTNmIEwQkgrwGsHu4jeoHFWCuo"></script>
  <script type='text/javascript' src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
  <script type='text/javascript' src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script> 

  <script type="text/javascript">
  var map;
  var markerPositionList;
  var latlngbounds;

  function initMap() {
  	markerPositionList = new Array();
    
    latlngbounds = new google.maps.LatLngBounds();

    map = new google.maps.Map(document.getElementById("map"), {
      /*
      center: {
      	lat: 48.856614, 
      	lng: 2.3522219
      },
      */
      scrollwheel: true,
      zoom: 12
    });

    var address = 'Montpellier, France';
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode( { 'address': address }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
      	map.setCenter(results[0].geometry.location);
      }
    });
  }

  function initDragAndDrop() {
  	$( "#map" ).on('dragenter',function(e){
      e.stopPropagation();
      e.preventDefault();
      // console.log('dragenter');
    }).on('dragover',function(e){
      e.stopPropagation();
      e.preventDefault();
      // console.log('dragover');
    }).on('drop',function(e){
      e.stopPropagation();
      e.preventDefault();
      var file = e.originalEvent.dataTransfer.files;
      var fd = new FormData();
      var fileLength = file.length;
      for (var i = 0; i < fileLength; i++) 
   	  {
        fd.append('file', file[i]);
      }
      $.ajax({
      	url: 'upload.php',
      	type: 'POST',
      	dataType: 'json',
      	data: fd,
      	contentType:false,
        processData: false,
        cache: false,
        beforeSend: function() {
          $('#message').empty();
        },
      	success: function(data,status,jqXHR) {
          getLocationByGeocode(data);
      	}, 
      	error: function(jqXHR,status) {
      	  displayMsg('/!\\ Erreur technique lors du drag and drop /!\\')
      	}
      });
    });
  }

  function getLocationByGeocode(datas) {
  	if(typeof(datas)=='object') {
  	  var max = datas.length;
  	  for (var i=0; i<max; i++) {
  	  	var v = datas[i];
  	  	var address = v.address + ', ' + v.zip + ' ' + v.city;
  	  	getLocationByAjax(address);
  	  	/*
        geocoder.geocode( { 'address': address }, function(results, status) {
          console.log(results);
	      if (status == google.maps.GeocoderStatus.OK) {
	      	var location = results[0].geometry.location;
	        drawMarkers(location);
	      } else {
			getLocationByAjax(address);
	      }
	    });
	    */
  	  }
  	}
  }

  function getLocationByAjax(address) {
  	var address = address;

  	if(typeof(address)!='string') return false;
  	
  	$.ajax({
	  url: "http://maps.googleapis.com/maps/api/geocode/json?address=" + $.trim(address),
	  method: "POST",
	  dataType: "json",
	  success: function(data,status,jqXHR) {
	  	if(typeof(data)=='object') {
	  	  if(data.status=="OK") {
	  	    var location = data.results[0].geometry.location;
	  	    drawMarkers(location,address);
	  	  } else {
	  	    displayMsg('<br />/!\\ ' + data.status + ', ' + data.error_message + ' : ' + address);
	  	  }
	  	}
	  },
	  error: function(jqXHR,status) {
	  	displayMsg('<br />/!\\ Erreur technique lors de la recherche des coordonnées de : ' + address);
	  }
	});
  }

  function drawMarkers(location,address) {
  	if(typeof(location)!='object') return false;
  	
    var marker = new google.maps.Marker({
      map: map,
      position: location,
      animation: google.maps.Animation.DROP,
      title: address,
      icon: {
        url: 'wgs.png',
        scaledSize: new google.maps.Size(24, 24),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(12,12)
      }
    });
    
    var infowindow = new google.maps.InfoWindow({
	    content: address
	  });

    marker.addListener('click',function(){
      if (marker.getAnimation() !== null) {
        marker.setAnimation(null);
        infowindow.close();
	    } else {
	      marker.setAnimation(google.maps.Animation.BOUNCE);
	      infowindow.open(map, marker);
	    }
    });
    
    markerPositionList.push(location);
    
    latlngbounds.extend(new google.maps.LatLng(location.lat,location.lng));

    // map.fitBounds(latlngbounds);
    setTimeout(function(){map.fitBounds(latlngbounds);},1000);
  } 
  
  function displayMsg(msg) {
  	if(typeof(msg)!='string') return false;
  	var message = $('#message');
	  	message.append(msg);
  }

  $(document).ready(function(){
    initMap();
    initDragAndDrop();
  });
  </script>

</head>
<body>
	
<?php 
  $message = '';
  $csv_sep = ';';
  $datas = array();
  if(isset($_FILES['myfile'])){
  	$myfile = $_FILES['myfile'];
  	$ext = pathinfo($myfile['name'], PATHINFO_EXTENSION);
    if($myfile["type"] != 'application/vnd.ms-excel' && $myfile["type"] != 'text/comma-separated-values' || strtolower($ext) != 'csv'){
  	  $message = '!!! Veuillez déposer un fichier de type CSV !!!';
  	} else if($myfile["error"] != 0) {
	  $message = 'Une erreur est survenue avec votre fichier';
  	} else {
      if (($handle = fopen($myfile["tmp_name"], "r")) !== FALSE) {
		while (($tmp_data = fgetcsv($handle, 1000, $csv_sep)) !== FALSE) {
		  if(isset($tmp_data[0]) && isset($tmp_data[1]) && isset($tmp_data[2])) {
		  	if(strtolower($tmp_data[0])!="addresse" && strtolower($tmp_data[1]!="code_postal") && strtolower($tmp_data[2])!="commune") {
		  		  $address = trim($tmp_data[0]);
		  		  $zip = trim($tmp_data[1]);
		  		  $city = trim($tmp_data[2]);
		  		  $datas[] = array('address' => $address, 'zip' => $zip, 'city' => $city);
		  	}
		  }
		}
		$datas = json_encode($datas);
		?>
		<script type="text/javascript">
		  $(document).ready(function(){
		    getLocationByGeocode(<?php echo $datas; ?>);
		  });
		</script>
		<?php
		fclose($handle);
	  }
  	}
  }
?>

<div id="map" style="width: 720px; height: 720px;"></div>

<div id="myform" style="margin: 20px 0 0 0;font-family: sourceSans;">
  <div id="message"><?php echo $message?></div>
  <form action="" method="post" enctype="multipart/form-data">
    <label for="myfile">Déposer votre fichier ici ou sur la carte : </label>
    <input id="myfile" type="file" name="myfile" title="Déposer votre fichier CSV"/>
    <input type="submit" value="Envoyer" class="submit" title="Envoyer votre fichier"/>
    <a href="fichier_test.csv" title="Cliquez ici pour voir un exemple de fichier csv">>> Exemple de fichier csv <<</a>
  </form>
</div>

</body>
</html>