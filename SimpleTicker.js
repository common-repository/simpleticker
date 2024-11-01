var simpleTickers = new Array();

jQuery(document).ready(function($) {
    if ($('.SimpleTicker') != null) {
        jQuery('.SimpleTicker').each(function() {
            var simpleTickerId = this.id.replace('SimpleTicker', '');
            jQuery.ajax({
                url: simpleTickerBaseURL + 'simpleticker.php?action=getTickerDetails&id=' + simpleTickerId,
                dataType: 'json',
                success: function(response) {
                    simpleTickerUpdateMsg(simpleTickerId, response.messageCount, response.tickerTimeout);
                    simpleTickerFadeMsg(simpleTickerId);
                    window.setInterval('simpleTickerFadeMsg(' + simpleTickerId + ')', response.messageTimeout * 1000);
                    window.setInterval('simpleTickerUpdateMsg(' + simpleTickerId + ', ' + response.messageCount + ', ' + response.tickerTimeout + ')', response.updateInterval * 1000);
                }
            });
        });
    }
});

function simpleTickerFadeMsg(simpleTickerId) {
    var simpleTickerText = jQuery('#SimpleTicker' + simpleTickerId + ' span');
    simpleTickerText.fadeOut('slow', function() {
        if (simpleTickers[simpleTickerId]['pointer'] < simpleTickers[simpleTickerId]['messages'].length -1) {
            simpleTickers[simpleTickerId]['pointer']++;
        } else {
            simpleTickers[simpleTickerId]['pointer'] = 0;
        }
        simpleTickerText.text(simpleTickers[simpleTickerId]['messages'][simpleTickers[simpleTickerId]['pointer']]);
    }).fadeIn('slow');
    if (simpleTickers[simpleTickerId]['messages'].length == 0) {
        jQuery('#SimpleTicker' + simpleTickerId).hide();
    } else {
        jQuery('#SimpleTicker' + simpleTickerId).show();
    }
}

function simpleTickerUpdateMsg(simpleTickerId, simpleTickerMsgCount, simpleTickerTimeout) {
    simpleTickers[simpleTickerId] = new Array();
    simpleTickers[simpleTickerId]['pointer'] = 0;
    simpleTickers[simpleTickerId]['messages'] = new Array();
    jQuery.ajax({
        url: simpleTickerBaseURL + 'simpleticker.php?action=getTickerMessages&id=' + simpleTickerId + '&count=' + simpleTickerMsgCount + '&timeout=' + simpleTickerTimeout,
        dataType: 'json',
        success: function(response) {
            simpleTickers[simpleTickerId]['messages'] = response;
        }
    });
}
