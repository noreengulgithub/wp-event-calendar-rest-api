jQuery(document).ready(function($){
    $.get(el_ajax_obj.rest_url, function(events){
        if(events && events.length){
            let html = '';
            events.forEach(function(ev){
                // Clean up description: remove block comments and strip tags
                let cleanDesc = ev.description
                    .replace(/<!--.*?-->/g, '') // remove Gutenberg comments
                    .replace(/<\/?[^>]+(>|$)/g, ''); // remove HTML tags

                html += '<div class="el-event">';
                html += '  <img src="'+ev.photo+'" alt="'+ev.title+'">';
                html += '  <div class="el-event-details">';
                html += '    <div class="el-title"><a href="'+ev.link+'">'+ev.title+'</a></div>';
                html += '    <div class="el-description">'+(cleanDesc.length > 50 ? cleanDesc.substring(0, 50)+'...' : cleanDesc)+'</div>';
                html += '  </div>';
                html += '</div>';
            });
            $('#el-events-list').html(html);
        } else {
            $('#el-events-list').html('<p>No events found.</p>');
        }
    });
});
