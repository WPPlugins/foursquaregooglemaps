jQuery(function($){
	$.getScript( 'https://maps.googleapis.com/maps/api/js?v=3.exp&callback=fsqrgodone', function( response, a, b ){
		
	});
});

function fsqrgodone(){
	var fsqr_main_icon = document.getElementById('fsqr_main_icon').value,
	fsqr_cid = 'SIKWXWIFUZTUEKPTAG24FA3ZJVWRF5P0JWYYZUOM4BVF43T0',
	fsqr_csec = '104WXFS20MHVV2CAOTVEQWD5WD0BLGZGU3OWYFAP3XKTHDPZ',
	fsqr_lat = Number( document.getElementById( 'fsqr_lat' ).value ),
	fsqr_lng = Number( document.getElementById( 'fsqr_lng' ).value ),
	fsqr_location_id = document.getElementById( 'fsqr_location_id' ).value,
	fsqr_zoom_level = Number( document.getElementById( 'fsqr_zoom_level' ).value ),
	fsqr_control = document.getElementById( 'fsqr_control' ).value == 1 ? false : true,
	mapOptions = {
		zoom: fsqr_zoom_level,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		visualRefresh: false,
		disableDefaultUI: fsqr_control
	},
	map = new google.maps.Map( document.getElementById( 'fsqrgo' ), mapOptions ),
	marker = new google.maps.Marker({
		map: map,
		title: 'Click to zoom',
		draggable: false,
		icon: fsqr_main_icon,
		animation: google.maps.Animation.DROP
	}),
	infoWindow = new google.maps.InfoWindow({
		size: new google.maps.Size( 300, 200 )
	});
	
	map.setCenter( new google.maps.LatLng( fsqr_lat, fsqr_lng ) );
	marker.setPosition( new google.maps.LatLng( fsqr_lat, fsqr_lng ) );
	
	//search nearby(from ours) places, centered map to our lat, lng
	var showNearbyCtrl = document.createElement('div'),
	showNearbyInner1 = document.createElement('div'),
	showNearbyInner2 = document.createElement('div'),
	showNearbyText1 = document.createElement('span'),
	showNearbyText2 = document.createElement('span');
	
	//show nearby venues
	showNearbyInner1.style.backgroundColor = '#FFFFFF';
	showNearbyInner1.style.border = '1px solid rgba(0, 0, 0, 0.15)';
	showNearbyInner1.style.boxShadow = '0 1px 4px -1px rgba(0, 0, 0, 0.3)';
	showNearbyInner1.style.color = '#000000';
	showNearbyInner1.style.cursor = 'pointer';
	showNearbyInner1.style.margin = '5px 0';
	showNearbyInner1.style.padding = '1px 6px';
	showNearbyInner1.style.styleFloat = 'left';
	showNearbyInner1.style.cssFloat = 'left';
	showNearbyInner1.style.backgroundClip = 'padding-box';
	
	showNearbyText1.innerHTML = 'Toggle Nearby Venues';
	showNearbyInner1.appendChild( showNearbyText1 );
	showNearbyCtrl.appendChild( showNearbyInner1 );
	
	//return to center
	showNearbyInner2.style.backgroundColor = '#FFFFFF';
	showNearbyInner2.style.border = '1px solid rgba(0, 0, 0, 0.15)';
	showNearbyInner2.style.boxShadow = '0 1px 4px -1px rgba(0, 0, 0, 0.3)';
	showNearbyInner2.style.color = '#000000';
	showNearbyInner2.style.cursor = 'pointer';
	showNearbyInner2.style.margin = '5px 0';
	showNearbyInner2.style.padding = '1px 6px';
	showNearbyInner2.style.styleFloat = 'left';
	showNearbyInner2.style.cssFloat = 'left';
	showNearbyInner2.style.backgroundClip = 'padding-box';
	
	showNearbyText2.innerHTML = 'Return To Center';
	showNearbyInner2.appendChild( showNearbyText2 );
	showNearbyCtrl.appendChild( showNearbyInner2 );
	
	var sideMarker = [];
	google.maps.event.addDomListener( showNearbyInner1, 'click', function(){
		if( sideMarker.length > 0 ){
			var isExist = sideMarker[0].getMap();
			if( isExist != null ){
				jQuery.each(sideMarker, function(i,e){
					e.setMap(null);
				});
			}else{
				jQuery.each(sideMarker, function(i,e){
					e.setMap(map);
				});
			}
		}else{
			jQuery.get('https://api.foursquare.com/v2/venues/search?ll='+fsqr_lat+','+fsqr_lng+'&client_id='+fsqr_cid+'&client_secret='+fsqr_csec+'&v=20131016 ', function( result ){
				if( result.response.venues.length > 1 ){
					//remove damakhijau from list
					result.response.venues.shift();
					
					jQuery.each( result.response.venues, function(i,e){
						var markericon = e.categories.length == 0 ? 'https://foursquare.com/img/categories_v2/building/votingbooth_bg_32.png' : e.categories[0]['icon']['prefix'] +'bg_32'+ e.categories[0]['icon']['suffix'];
						
						sideMarker[i] = new google.maps.Marker({
							map: map,
							title: e.name,
							icon: markericon
						});
						sideMarker[i].setPosition( new google.maps.LatLng( e.location.lat, e.location.lng ) );
						
						google.maps.event.addDomListener(sideMarker[i], 'click', function(){
							infoWindow.setContent( '<span class="sys-nearby">'+e.name+'</span>' );
							infoWindow.open(map,sideMarker[i]);
						});
					});
				}
			});
		}
	});
	
	google.maps.event.addDomListener( showNearbyInner2, 'click', function(){
		map.setCenter( new google.maps.LatLng( fsqr_lat, fsqr_lng ) );
	});
	
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(showNearbyCtrl);
	
	//get tips
	jQuery.get('https://api.foursquare.com/v2/venues/'+fsqr_location_id+'/tips?client_id='+fsqr_cid+'&client_secret='+fsqr_csec+'&v=20131016',function(result){
		var infoContent = '<span class="fsqrgo-main">Damak Hijau Enterprise</span>',
		tips = result.response.tips.items;
		if( tips.length > 0 ){
			var maxTips = tips.length > 3 ? 3 : tips.length;
			infoContent += '<ul class="fsqrgo-tips">';
			for( var i=0; i < maxTips; i++ ){
				var dp = tips[i]['user']['photo']['prefix'] + '32' + tips[i]['user']['photo']['suffix'],
				nm = tips[i]['user']['lastName'] +','+ tips[i]['user']['firstName'];
				
				infoContent += '<li> \
				<span class="fsqrgo-tips-dp"><img src="'+dp+'" height="32" width="32" /></span> \
				<span class="fsqrgo-tips-by"> \
				<span class="fsqrgo-tips-name">'+nm+'</span> : \
				<span class="fsqrgo-tips-text">'+tips[i]['text']+'</span> \
				</span> \
				</li>';
			}
			infoContent += '</ul>';
		}
		
		infoWindow.setContent( infoContent );
		infoWindow.open(map,marker);
		
		google.maps.event.addDomListener(marker, 'click', function(){
			infoWindow.setContent( infoContent );
			infoWindow.open(map,marker);
		});
	});
}