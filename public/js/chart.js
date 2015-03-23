$(document).ready(function() {
    var base_url = $('#base_url').text().trim(); 
    
    $.ajax({
        url: base_url +'home/Demographics',
        data: {
            state: 'NY'
        },
        success: function(data) {
            var obj = JSON.parse(data);
            console.log(obj);
        }
    });

    /*
    $.ajax({
        url: base_url +'users/GetHottestInState',
        data: {
            state: 'NY',
            sex: 'male'
        },
        success: function(data) {
            var obj = JSON.parse(data);
            console.log(obj);
        }
    });
*/

    $.ajax({
        url: base_url +'users/GetHottestInState',
        data: {
            state: 'NY',
            sex: 1
        },
        success: function(data) {
            var obj = JSON.parse(data);
            console.log(obj);
        }
    });

    var male = $('#male_percentage').text().trim();
    var female = $('#female_percentage').text().trim();
    // console.log(male +', '+ female);

    var data = [
        {
            value: parseInt(female),
            color: '#ad5',
            highlight: '#aa3',
            label: 'Women'
        },
        {
            value: parseInt(male),
            color: '#0993c7',
            highlight: '#3090cc',
            label: 'Men'
        }
    ];

    var options = {
                segmentShowStroke : true,
                segmentStrokeColor : '#fff',
                segmentStrokeWidth : 2,
                percentageInnerCutout : 0, 
                animationSteps : 100,
                animationEasing : 'easeOutBounce',
                animateRotate : true,
                animateScale : false,
                legendTemplate : false
            }
    var ctx = $('#my_chart').get(0).getContext('2d');
    var pie = new Chart(ctx).Pie(data, options);
});

/*
var map = new Datamap({
            element: document.getElementById('datamaps'),
            scope: 'usa',
            responsive: true,
            fills: {
                defaultFill: '#d8d8d8'
            },
            projection: 'mercator',
            geographyConfig: {
                borderWidth: 2,
                borderColor: '#fff',
                highlightFillColor: '#0993c7',
                highlightBorderColor: '#f0f0f0',
                highlightBorderWidth: 2,
            },
            done: function(datamap) {
                // console.log(datamap);
                datamap.svg.selectAll('.datamaps-subunit').on('click', function(geo) {
                    var m = {}
                    var state = geo.id;
                    var state_name = geo.properties.name;
                    m[state] = '#0993c7';
                    map.updateChoropleth(m);

                    // Change the stateface font
                    $('h2 .stateface').attr('class', 'stateface stateface-'+ state.toLowerCase());
                    $('h2 #state_header').text(state_name);
                });
            }
        });

// Fill in the state that is currently being searched
var abbrev = $('#abbrev').text().trim().toUpperCase();
var m = {}
m[abbrev] = '#0993c7';
map.updateChoropleth(m);
*/

/*
// Make the map responsive
$(window).on('resize', function() {
   map.resize();
});
*/
