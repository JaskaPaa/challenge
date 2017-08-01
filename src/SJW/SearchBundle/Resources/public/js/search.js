(function($) {
    
    function showTable(data, val) {

        $("#resu tr").empty();
        $('#resu').append('<thead><tr><th>Zip</th><th>Town</th><th>Population</th></tr></thead>');

        for (i = 0; i < data.length; i++) {
            $('#resu tr:last').after('<tr><td>' 
                + data[i][0] + '</td><td>'+ data[i][1] 
                + '</td><td>'+ data[i][2] + '</td></tr>');
            if (val == data[i][0] || val == data[i][1])
               $("#resu tr:nth-child(" + (i + 2) + ")").css("background", "#ddd");
        }
    }

    function doSearch(auto_val, spin_val) {
        // input values from autocomplete and spinner
        $.ajax({
            url: 'api/search?q=' + auto_val + "|" + spin_val,
            type: 'GET',
            success: function(data) {
                showTable(data, auto_val);
            }
        });
    }

    $(function() {
        $( "#spinner" ).spinner({
            min: 0,
            max: 100,
            spin: function( event, ui ) {
                doSearch($("#areas.ui-autocomplete-input").val(), ui.value);
            }
        });
    });

    $(function() {
        $("#areas").autocomplete({
            source: "api/complete",
            minLength: 1,
            autoFocus: true,
            delay: 50,
            select: function( event, ui ) {
                doSearch(ui.item.value, $("#spinner").spinner("value"));
            }
        });
          
    });

    $(function() {
        $("#areas").keypress(function(e) {
            if(e.which == 13) { // enter
                doSearch($("#areas.ui-autocomplete-input").val(), $("#spinner").spinner("value"));
            }
        });
    });
    
})(window.jQuery);
