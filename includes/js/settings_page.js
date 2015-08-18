jQuery(document).ready(function () {
	
	//show and hide instructions
    jQuery("#auth_help").click(function () {
        jQuery("#auth_troubleshoot").toggle();
    });
	jQuery("#conn_help").click(function () {
        jQuery("#conn_troubleshoot").toggle();
    });
	
	//show and hide attribute mapping instructions
    jQuery("#toggle_am_content").click(function () {
        jQuery("#show_am_content").toggle();
    });

    //warnings
    jQuery("#help_curl_warning_title").click(function () {
    	jQuery("#help_curl_warning_desc").slideToggle(400);
    });
    
	 //Instructions
    jQuery("#help_curl_title").click(function () {
    	jQuery("#help_curl_desc").slideToggle(400);
    });
    
    jQuery("#help_mcrypt_warning_title").click(function () {
    	jQuery("#help_mcrypt_warning_desc").slideToggle(400);
    });

    jQuery("#help_ldap_title").click(function () {
    	jQuery("#help_ldap_desc").slideToggle(400);
    });
    
    jQuery("#help_ping_title").click(function () {
    	jQuery("#help_ping_desc").slideToggle(400);
    });
    
    jQuery("#help_invaliddn_title").click(function () {
    	jQuery("#help_invaliddn_desc").slideToggle(400);
    });
    
    jQuery("#help_invalidsf_title").click(function () {
    	jQuery("#help_invalidsf_desc").slideToggle(400);
    });
    
    jQuery("#help_seracccre_title").click(function () {
    	jQuery("#help_seracccre_desc").slideToggle(400);
    });
    
    jQuery("#help_sbase_title").click(function () {
    	jQuery("#help_sbase_desc").slideToggle(400);
    });
    
    jQuery("#help_sfilter_title").click(function () {
    	jQuery("#help_sfilter_desc").slideToggle(400);
    });
    
    jQuery("#help_ou_title").click(function () {
    	jQuery("#help_ou_desc").slideToggle(400);
    });
    
    jQuery("#help_loginusing_title").click(function () {
    	jQuery("#help_loginusing_desc").slideToggle(400);
    });
    
    jQuery("#help_diffdist_title").click(function () {
    	jQuery("#help_diffdist_desc").slideToggle(400);
    });
    
    
});