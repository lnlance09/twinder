var map = new Datamap({
            element: document.getElementById('datamaps'),
            scope: 'usa',
            responsive: true,
            fills: {
                defaultFill: '#d8d8d8'
            },
            geographyConfig: {
                borderWidth: 2,
                borderColor: '#fff',
                highlightFillColor: '#07f',
                highlightBorderColor: '#f0f0f0',
                highlightBorderWidth: 2,
            },
            done: function(datamap) {
                // console.log(datamap);
            }
        });

// Fill in the state that is currently being searched
var abbrev = $('#abbrev').text().trim().toUpperCase();
map.updateChoropleth({abbrev: 'green'});

map.svg.selectAll('.datamaps-subunit').on('click', function(geo) {
    var m = {}
    var state = geo.id;
    m[state] = '#07f';
    map.updateChoropleth(m);

    // Change the stateface font
    $('.mrs_state .stateface').attr('class', 'stateface stateface-'+ state.toLowerCase());
});

// Make the map responsive
$(window).on('resize', function() {
   map.resize();
});


// Charts.js
var data = [
            {
                value: 300,
                color:"#F7464A",
                highlight: "#FF5A5E",
                label: "Red"
            },
            {
                value: 50,
                color: "#46BFBD",
                highlight: "#5AD3D1",
                label: "Green"
            },
            {
                value: 100,
                color: "#FDB45C",
                highlight: "#FFC870",
                label: "Yellow"
            }
        ];

var ctx = $('#my_chart').get(0).getContext('2d');
var pie = new Chart(ctx).Pie(data, {
            segmentShowStroke : true,
            segmentStrokeColor : '#fff',
            segmentStrokeWidth : 2,
            percentageInnerCutout : 0, 
            animationSteps : 100,
            animationEasing : 'easeOutBounce',
            animateRotate : true,
            animateScale : false,
            legendTemplate : false
        });
