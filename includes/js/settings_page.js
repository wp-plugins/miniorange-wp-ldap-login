jQuery(document).ready(function () {
	
	//show and hide instructions
    jQuery("#auth_help").click(function () {
        jQuery("#auth_troubleshoot").toggle();
    });
	jQuery("#conn_help").click(function () {
        jQuery("#conn_troubleshoot").toggle();
    });
});