
/**
 * Send the data to ws/database
 */
function save_geolocation_data(type, data) {
    goteo.trace('Saving geolocation data, type:', type, ' data:', data);
    $.post('/json/geolocate/' + type, data, function(result){
        goteo.trace('Saved gelocation data result:', result);
    });
}
/**
 * Sets the location by asking latitude / longitude to google (ip based location)
 * @param string type location_item (type) field: 'user', ...
 * requires     <script type="text/javascript" src="https://www.google.com/jsapi"></script> to be loaded
 */
// function set_location_from_google(type, iteration) {
//     if(typeof google !== 'undefined' && google.loader.ClientLocation) {
//         var loc = google.loader.ClientLocation;
//         if (loc.latitude) {
//             goteo.trace('Google ip location:', loc);
//             //save data
//             save_geolocation_data(type, {
//                 longitude: loc.longitude,
//                 latitude: loc.latitude,
//                 city: google.loader.ClientLocation.address.city,
//                 region: google.loader.ClientLocation.address.region,
//                 country: google.loader.ClientLocation.address.country,
//                 country_code: google.loader.ClientLocation.address.country_code,
//                 method: 'ip'
//             });
//         }
//     }
//     else {
//         if(!(iteration)) iteration = 0;
//         iteration++;
//         goteo.trace('google client does not exists! [' + type +' '+iteration+ ']');
//         if(iteration > 10) {
//             goteo.trace('Cancelled');
//         }
//         else {
//             setTimeout(function(){set_location_from_google(type, iteration);}, 500);
//         }
//     }
// }

function set_location_from_freegeoip(type) {
    $.getJSON('//freegeoip.net/json', function(data){
        if(data.latitude && data.longitude) {
            goteo.trace('geolocated type:', type, ' data:', data);
           //save data
            save_geolocation_data(type, {
                longitude: data.longitude,
                latitude: data.latitude,
                city: data.city,
                region: data.region_name,
                country: data.country_name,
                country_code: data.country_code,
                method: 'ip'
            });
        }
        else {
            goteo.trace('Freegeoip error');
        }
    });
}

/**
 * Gets location by asking latitude/longitude to the browser
 *
 * use with callback function as:
 *
 * get_location_from_browser(function(success, data) {
 *     goteo.trace('success: ' + success, data.city, data.region, data.country, data.country_code, data.latitude, data.longitude);
 * })
 *
 */
function get_location_from_browser(callback, iteration) {
    var success = false;
    var data= {};

    if(typeof google === 'undefined' && !google.maps) {
        if(!(iteration)) iteration = 0;
        iteration++;
        goteo.trace('google.map client does not exists! ['+iteration+ ']');
        if(iteration > 10) {
            goteo.trace('Cancelled');
        }
        setTimeout(function(){get_location_from_browser(callback, iteration);}, 500);
        return;
    }

    if (navigator.geolocation) {

        //Try browser IP locator
        navigator.geolocation.getCurrentPosition(
            function(position) {
                goteo.trace('browser info:', position.coords.latitude, position.coords.longitude);
                data = {
                    method: 'browser',
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                // ask google for address:
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            success = true;
                            // goteo.trace(results[0]);
                            for(var i in results[0].address_components) {
                                var ob = results[0].address_components[i];
                                // goteo.trace(i, ob, "\n");
                                if(ob.types[0] === 'country' && ob.types[1] === 'political') {
                                    data.country = ob.long_name;
                                    data.country_code = ob.short_name;
                                }
                                if(ob.types[0] === 'locality' && ob.types[1] === 'political') {
                                    data.city = ob.long_name;
                                }
                                if((ob.types[0] === 'administrative_area_level_1' || ob.types[0] === 'administrative_area_level_2') && ob.types[1] === 'political') {
                                    data.region = ob.long_name;
                                }
                            }
                            goteo.trace('Geocoder data:', data);
                        } else {
                            goteo.trace('Geocoder failed due to: ' + status);
                        }
                    }
                    if(typeof callback === 'function') {
                        callback(success, data);
                    }
                });
            },
            function(error) {
                data = {
                    locable : 1,
                    info : ''
                };
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                      //set the unlocable status for the user
                      data.locable = 0;
                      data.info = "User denied the request for Geolocation.";
                      break;
                    case error.POSITION_UNAVAILABLE:
                      data.info = "Location information is unavailable.";
                      break;
                    case error.TIMEOUT:
                      data.info = "The request to get user location timed out.";
                      break;
                    case error.UNKNOWN_ERROR:
                      data.info = "An unknown error occurred.";
                      break;
                }
                goteo.trace('Geocoder error:', error, ' data:', data);
                if(typeof callback === 'function') {
                    callback(success, data);
                }
            }
        );
    }
}

/**
 * Sets the location by asking latitude / longitude to the browser
 * @param string type location_item (type) field: 'user', ...
 */
function set_location_from_browser(type) {
    get_location_from_browser(function(success, data) {
        save_geolocation_data(type, data);
    });
}

$(function(){

    // get user current location status
    $.getJSON('/json/geolocate/user', function(data){
        goteo.trace('Current user localization status: ', data);

        //only if user is logged
        if(data.user) {
            var use_browser = false;

            if(data.success) {
                //Is located, if method is IP, Try to override by browser coordinates
                if(data.location.method === 'ip' && data.location.locable) {
                    use_browser = true;
                }
                //if method is browser or manual, no further actions are required
            }
            else {
                //try google IP locator
                // set_location_from_google('user');
                set_location_from_freegeoip('user');
                use_browser = true;
            }

            if(use_browser) {
                goteo.trace('Trying browser localization');
                //try the browser for more precision
                set_location_from_browser('user');
            }
        }
    });


});
