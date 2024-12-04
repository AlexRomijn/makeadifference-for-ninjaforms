function ConvertEl2Bar(el) {
	var graphtype = jQuery(el).attr('graph-type');
	var formid = jQuery(el).attr('formid');
 	eval('var settings = MadGraphsettings'+formid+';');
	if (graphtype == 'onlypercentage') {
		jQuery(el).find('.numberfrom').html(settings.form_protest_confirmed_subs);
		jQuery(el).find('.between').html(settings.form_protest_perc_of);
		jQuery(el).find('.goal').html(settings.form_protest_goals + ' (' +  settings.form_protest_perc + '%)');
	} else if (graphtype == 'barsimple') {
		var totalwidth = jQuery(el).width();
		jQuery(el).find('.perc').html(settings.form_protest_perc+'%');
		jQuery(el).find('.perc').width(settings.form_protest_perc+'%');
	} else if (graphtype == 'barsimpleanimated') {
		var totalwidth = jQuery(el).width();
		jQuery(el).find('.perc').html(settings.form_protest_perc+'%');
		jQuery(el).find('.perc').animate({
			width: settings.form_protest_perc+'%'
		},1500);
	}
}

jQuery( document ).ready( function( $ ) {
	jQuery('.madgraph').each(function(index, element) {
        ConvertEl2Bar(element);
    });
});
