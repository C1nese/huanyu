$(function(){         
	var tarck_s = '[';
	for (var i in track_sev) {
		tarck_s += '{ "y":"'+track_sev[i].time+'", "a": '+parseInt(track_sev[i].val)+', "b": '+parseInt(track_sev[i].total)+'}';
          if(i < track_sev.length -1){
              tarck_s += ",";
          }
      }
	tarck_s += ']';
      var tarck_e = '[';
      for (var i in track_se) {
        tarck_e += '{ "y":"'+track_se[i].time+'", "a": '+parseInt(track_se[i].val)+', "b": '+parseInt(track_se[i].total)+'}';
      if(i < track_se.length -1){
              tarck_e += ",";
          }
      }
      tarck_e += ']';
    /* reportrange */
    if($("#reportrange").length > 0){   
        $("#reportrange").daterangepicker({                    
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'left',
            buttonClasses: ['btn btn-default'],
            applyClass: 'btn-small btn-primary',
            cancelClass: 'btn-small',
            format: 'MM.DD.YYYY',
            separator: ' to ',
            startDate: moment().subtract('days', 29),
            endDate: moment()            
          },function(start, end) {
              $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        });
        
        $("#reportrange span").html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
    }
    /* end reportrange */
    
    /* Rickshaw dashboard chart */
   
    /* END Rickshaw dashboard chart */
    
    /* Donut dashboard chart */
    Morris.Donut({
        element: 'dashboard-donut-1',
        data: [
            {label: "Returned", value: 2513},
            {label: "New", value: 764},
            {label: "Registred", value: 311}
        ],
        colors: ['#33414E', '#3FBAE4', '#FEA223'],
        resize: true
    });
    /* END Donut dashboard chart */
    
    /* Bar dashboard chart */
    // Morris.Bar({
    //     element: 'dashboard-bar-1',
    //     data: $.parseJSON(tarck_e),
    //     xkey: 'y',
    //     ykeys: ['a', 'b','c'],
    //     labels: ['日', '周', '月'],
    //     barColors: ['#33414E', '#3FBAE4','#EA7500'],
    //     gridTextSize: '10px',
    //     hideHover: true,
    //     resize: true,
    //     gridLineColor: '#E5E5E5'
    // });
    /* END Bar dashboard chart */
    
    /* Line dashboard chart */
    Morris.Line({
      element: 'dashboard-line-1',
      data: $.parseJSON(tarck_s),
      xkey: 'y',
      ykeys: ['a','b'],
      labels: ['新增收入','累计收入'],
      resize: true,
      hideHover: true,
      xLabels: 'day',
      gridTextSize: '10px',
      lineColors: ['#3FBAE4','#33414E','#95b75d'],
      gridLineColor: '#E5E5E5'
    });
    Morris.Line({
        element: 'dashboard-line-2',
        data: $.parseJSON(tarck_e),
        xkey: 'y',
        ykeys: ['a','b'],
        labels: ['新增收入','累计收入'],
        resize: true,
        hideHover: true,
        xLabels: 'day',
        gridTextSize: '10px',
        lineColors: ['#3FBAE4','#33414E','#95b75d'],
        gridLineColor: '#E5E5E5'
    });
    /* EMD Line dashboard chart */
    
    /* Vector Map */
    var jvm_wm = new jvm.WorldMap({container: $('#dashboard-map-seles'),
                                    map: 'world_mill_en', 
                                    backgroundColor: '#FFFFFF',                                      
                                    regionsSelectable: true,
                                    regionStyle: {selected: {fill: '#B64645'},
                                                    initial: {fill: '#33414E'}},
                                    markerStyle: {initial: {fill: '#3FBAE4',
                                                   stroke: '#3FBAE4'}},
                                    markers: [{latLng: [50.27, 30.31], name: 'Kyiv - 1'},                                              
                                              {latLng: [52.52, 13.40], name: 'Berlin - 2'},
                                              {latLng: [48.85, 2.35], name: 'Paris - 1'},                                            
                                              {latLng: [51.51, -0.13], name: 'London - 3'},                                                                                                      
                                              {latLng: [40.71, -74.00], name: 'New York - 5'},
                                              {latLng: [35.38, 139.69], name: 'Tokyo - 12'},
                                              {latLng: [37.78, -122.41], name: 'San Francisco - 8'},
                                              {latLng: [28.61, 77.20], name: 'New Delhi - 4'},
                                              {latLng: [39.91, 116.39], name: 'Beijing - 3'}]
                                });    
    /* END Vector Map */

    
    $(".x-navigation-minimize").on("click",function(){
        setTimeout(function(){
            rdc_resize();
        },200);    
    });
    
    
});

