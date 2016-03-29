$().ready(function(){
    $("#sortedTable").tablesorter();
    $('#sortedTable').paging({limit:5});
});