// Helpers

function slugify(string) {
  // from https://medium.com/@mhagemann/the-ultimate-way-to-slugify-a-url-string-in-javascript-b8e4a0d849e1
  const a = 'àáâäæãåāăąçćčđďèéêëēėęěğǵḧîïíīįìłḿñńǹňôöòóœøōõőṕŕřßśšşșťțûüùúūǘůűųẃẍÿýžźż·/_,:;'
  const b = 'aaaaaaaaaacccddeeeeeeeegghiiiiiilmnnnnoooooooooprrsssssttuuuuuuuuuwxyyzzz------'
  const p = new RegExp(a.split('').join('|'), 'g')

  return string.toString().toLowerCase()
    .replace(/\s+/g, '-') // Replace spaces with -
    .replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
    .replace(/&/g, '-and-') // Replace & with 'and'
    .replace(/[^\w\-]+/g, '') // Remove all non-word characters
    .replace(/\-\-+/g, '-') // Replace multiple - with single -
    .replace(/^-+/, '') // Trim - from start of text
    .replace(/-+$/, '') // Trim - from end of text
}

function makeSlug(name, start, end){
    var start = new Date(start);
    var end = new Date(end);
    var millis = start.getTime() + end.getTime();
    var slug = millis + slugify(name);
    return slug;
}

function daysDiff(date1, date2) {
    dt1 = new Date(date1);
    dt2 = new Date(date2);
    return Math.floor((Date.UTC(dt2.getFullYear(), dt2.getMonth(), dt2.getDate()) - Date.UTC(dt1.getFullYear(), dt1.getMonth(), dt1.getDate()) ) /(1000 * 60 * 60 * 24));
}

function getMonth(date) { 
  var month = date.getMonth() + 1;
  return (month < 10 ? '0' : '') + month;
}

function getDate(date) { 
  return (date.getDate() < 10 ? '0' : '') + date.getDate();
}

function dateRangeString(date1, date2) {
    var start = new Date(date1);
    var end = new Date(date2);
    var startMonth = start.toLocaleString('default', { month: 'short' });
    var endMonth = end.toLocaleString('default', { month: 'short' });
    var startYear = start.getFullYear();
    var endYear = end.getFullYear();

    var rangeString = "";

    if(startMonth != endMonth || startYear != endYear){
        if (startYear != endYear){
            rangeString = "from " + startMonth + " " + startYear + " to " + endMonth + " " + endYear;
        }else{
            rangeString = "from " + startMonth + " to " + endMonth + " " + endYear;
        }
    }else{
        rangeString = "in " + startMonth + " " + startYear;
    }

    return rangeString;
}

function dateRangeStringList(listOfRanges){
    if(listOfRanges.length > 1){
        var last = listOfRanges.pop();
        var rest = listOfRanges.join(", ");
        var stringList = rest + " and " + last;
    }else{
        var stringList = listOfRanges[0];
    }
    return stringList;
}

function colorLuminance(hex, lum) {
    // From https://www.sitepoint.com/javascript-generate-lighter-darker-color/
    // validate hex string
    hex = String(hex).replace(/[^0-9a-f]/gi, '');
    if (hex.length < 6) {
        hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    }
    lum = lum || 0;

    // convert to decimal and change luminosity
    var rgb = "#", c, i;
    for (i = 0; i < 3; i++) {
        c = parseInt(hex.substr(i*2,2), 16);
        c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
        rgb += ("00"+c).substr(c.length);
    }

    return rgb;
}

function timeToColor(days){
    // Change the intensity of the colour based on number of days
    // (more days = darker)
    var baseColor = "#008459";
    var color = baseColor;
    if (days < 1){
        color = colorLuminance(baseColor, 0.8);
    }else if(days < 7){
        color = colorLuminance(baseColor, 0.3);
    }else if(days < 14){
        color = baseColor;
    }else if(days < 30){
        color = colorLuminance(baseColor, -0.2);
    }else if(days < 60){
        color = colorLuminance(baseColor, -0.4);
    }else {
        color = colorLuminance(baseColor, -0.6);
    }
    return color;
}

// Map

// var layer = new L.StamenTileLayer("watercolor");
var layer = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
    });
var map = new L.Map("themap", {
    center: new L.LatLng(37.7, -37),
    zoom: 3
});
map.addLayer(layer);

var latlonpopup = L.popup();
map.on('click', function(e){
    latlonpopup
        .setLatLng(e.latlng)
        .setContent(e.latlng.toString())
        .openOn(map);
});

var table = document.getElementById("thetable");

// data is from data.js
data.forEach(function(place){

    var days = 0;
    var whens = [];

    place.visits.forEach(function(visit){
        var visitDays = daysDiff(visit.startDate, visit.endDate);
        days = days + visitDays;
        var start = new Date(visit.startDate);
        var end = new Date(visit.endDate);
        var dateString = dateRangeString(visit.startDate, visit.endDate);
        var slug = makeSlug(place.name, visit.startDate, visit.endDate);
        var dateLink = document.createElement("a");
        dateLink.href = "#" + slug;
        dateLink.innerText = dateString;
        var targetRow = document.getElementById(slug);
        whens.push(dateLink.outerHTML);

        // Table
        var row = table.insertRow();
        row.id = slug;
        var whereCell = row.insertCell();
        var whenCell = row.insertCell();
        var untilCell = row.insertCell();
        var forCell = row.insertCell();
        // var notesCell = row.insertCell();
        whereCell.classList.add("where");
        whenCell.classList.add("when");
        untilCell.classList.add("until");
        forCell.classList.add("for");
        // notesCell.classList.add("notes");
        
        var sortableStart = start.getFullYear() + "-" + getMonth(start) + "-" + getDate(start);
        whenCell.setAttribute("data-start", sortableStart);
        var sortableEnd = end.getFullYear() + "-" + getMonth(end) + "-" + getDate(end);
        untilCell.setAttribute("data-end", sortableEnd);
        
        whereCell.innerHTML = place.name;
        whenCell.innerHTML = start.getDate() +" "+ start.toLocaleString('default', { month: 'short' }) +" "+ start.getFullYear();
        untilCell.innerHTML = end.getDate() +" "+ end.toLocaleString('default', { month: 'short' }) +" "+ end.getFullYear();
        if(visitDays < 1){
            forCell.innerHTML = "less than a day";
        }else if(visitDays > 365){
            forCell.innerHTML = "more than a year";
        }else{
            forCell.innerHTML = visitDays + " days";
        }
        //notesCell.innerHTML = visit.content;

    });

    var color = timeToColor(days);
    var dayString = "";
    if (days < 1){
        dayString = "less than a day here ";
    }else{
        dayString = days + " days here ";
    }
    var datesThere = dateRangeStringList(whens);
    dayString = dayString + datesThere;

    var marker = new L.Marker.SVGMarker(place.coordinates,{ 
        iconOptions: { color: color, fillOpacity: 0.9, circleRatio: 0.3 }
    }).addTo(map);
    marker.color = color;
    marker.bindPopup("<h1>"+place.name+"</h1><p>"+dayString+"</p>");

});

// Table

var sortableClassValues = [
    "where",
    { name: 'when', attr: 'data-start' },
    { name: 'until', attr: 'data-end' },
    "for"
]

var options = {
    valueNames: sortableClassValues
};

var placesTable = new List("tablewrapper", options);

placesTable.sort("when", {
    order: "desc"
})

// Highlight row if clicked from map

