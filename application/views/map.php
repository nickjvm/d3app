<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="<?php assetURL(); ?>css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php assetURL(); ?>css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="<?php assetURL(); ?>css/main.css">

        <script src="<?php assetURL(); ?>js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=AIzaSyDeI2rgNEVh7Wl1VYqRNauDnz0aPmzhPZU&sensor=true&libraries=geometry"></script>
        <script type="text/javascript">
              var geocoder;
              var map;
              var markers = [];
              var latlng;
              var locations = []
              function getLocation() {
                //return false;
                if (Modernizr.geolocation) {
                  navigator.geolocation.getCurrentPosition(initialize);
                } else {
                  // no native support; maybe try a fallback?
                }
              }
              function show_map(position) {
                return position.coords.latitude + ","+position.coords.longitude;
              }

              function initialize(position) {
                //geocoder = new google.maps.Geocoder();
                $.ajax("http://maps.googleapis.com/maps/api/geocode/json?latlng="+position.coords.latitude+","+position.coords.longitude+"&sensor=false",{
                  success:function(data) {
                    $("#q").val(data.results[0].address_components[2].long_name + ", "+data.results[0].address_components[4].short_name);
                  }
                })
                if(!position) {
                  position = {
                    coords: {
                      latitude:42.372781,
                      longitude:-71.042779
                    }
                  }
                  //getLocation();
                }
                latlng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
                var mapOptions = {
                  zoom: 12,
                  center: latlng,
                  styles:[
                    {
                      featureType: "poi",
                      elementType: "labels",
                      stylers: [
                        { visibility: "off" }
                      ]
                    }
                  ]

                }
                map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
                geocoder = new google.maps.Geocoder();

                infowindow = new google.maps.InfoWindow({
                  pixelOffset: new google.maps.Size(0, -35)
                }); 
                google.maps.event.addListener(map,"idle",resetMarkers)

              }
              function resetMarkers() {
                for (var i = 0; i < markers.length; i++ ) {
                   if(!map.getBounds().contains(markers[i].getPosition())) {
                     markers[i].setMap(null);
                     //console.log("marker removed: " + markers[i].getPosition());
                   }
                  }
                  //markers.length = 0;
                  getLocations();
              }
              function getLocations() {
                $("#loading").show();
                if(!locations.length) {
                  $.ajax("/api/locations",{
                    success:function(data) {
                      $.each(data,function() {
                        if(this.lat && this.lng) {
                          locations.push(this);
                          dropMarker(this);
                        }
                      });
                      $("#loading").hide();
                    }

                    });
                  } else {
                    $.each(locations,function() {
                      dropMarker(this);
                    })
                    $("#loading").hide();
                  }
              }
              function citysearch(address) {
                  geocoder.geocode( { 'address': address}, function(results, status) {

                  if (status == google.maps.GeocoderStatus.OK) {
                      map.setCenter(results[0].geometry.location);
                  } else {
                    alert("Geocode was not successful for the following reason: " + status);
                  }
                });
              }
              function dropMarker(location) {
                if (location.lat && location.lng) {
                      var position = new google.maps.LatLng(location.lat,location.lng);
                      location.distance = miles(google.maps.geometry.spherical.computeDistanceBetween(position,latlng));

                      var marker = new google.maps.Marker({
                          map: map,
                          position: position,
                          data:location
                      });
                      markers.push(marker);
                      google.maps.event.addListener(marker, 'click', function() { 
                          map.setCenter(new google.maps.LatLng(marker.position.lat(), marker.position.lng())); 
                          if(map.getZoom() < 14) {
                            map.setZoom(14); 
                          }
                          onItemClick(event, marker); 
                        });
                      
                      
                    } else {
                      console.log("no lat/lng");
                    }
              }
              
                function miles(distanceInMeters) {
                  return (distanceInMeters * 0.000621371).toFixed(1);
                }
                function onItemClick(event, pin) { 
                  // Create content  
                  var img = "http://maps.googleapis.com/maps/api/streetview?size=230x100&location="+pin.position.d+","+pin.position.e+"&fov=90&heading=235&pitch=10&sensor=false";
                  var contentString = "<h4>"+ pin.data.Name+"</h4>\
                      <p>"+pin.data.distance+" miles away</p>\
                      <p>"+pin.data.Address+"<br/>\
                      "+pin.data.City+", " + pin.data.State + " " + pin.data.Zip+"</p>\
                      <p>"+pin.data.Phone.replace(/(\w{3})(\w{3})(\w{4})/, '\($1\) $2-$3')+"<br/>\
                      <a href='"+pin.data.URL+"'>"+pin.data.URL+"</a></p>\
                      <img height='100' src='"+img+"'/>";
                  //console.log(pin.position);
                  // Replace our Info Window's content and position 
                  infowindow.setContent(contentString); 


                  infowindow.setPosition(pin.position); 
                  infowindow.open(map) 
                }
              google.maps.event.addDomListener(window, 'load', getLocation);
              
            </script>
          </head>
          <body>
            <div id="loading"></div>
            <div id="search" class="col-sm-6">
              <form class="form-inline" id="query">
                <input class="form-control" name="q" id="q" type="text" placeholder="Search by city or zip code"/>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            <div id="map-canvas"/>
          <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
          <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>

          <script src="<?php assetURL(); ?>js/vendor/bootstrap.min.js"></script>

          <script src="<?php assetURL(); ?>js/main.js"></script>
          <script>
          $(document).ready(function() {
            $("#q").on("focus",function(e) {
              $(this).val("");
            })
            $("#query").on("submit",function(e) {
              e.preventDefault();
              $("#q").blur();
              citysearch($("#q").val());

            })
          })
          </script>
          <script>
              var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
              (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
              g.src='//www.google-analytics.com/ga.js';
              s.parentNode.insertBefore(g,s)}(document,'script'));
          </script>
      </body>
</html>
