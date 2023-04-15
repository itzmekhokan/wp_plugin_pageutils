/* global gdpr_script_data */
// GDPR Cookie
PU_ACCEPT_COOKIE_NAME =(typeof PU_ACCEPT_COOKIE_NAME !== 'undefined' ? PU_ACCEPT_COOKIE_NAME : 'wp_gdpr_accept');
PU_ACCEPT_COOKIE_EXPIRE =(typeof PU_ACCEPT_COOKIE_EXPIRE !== 'undefined' ? PU_ACCEPT_COOKIE_EXPIRE : parseInt(gdpr_script_data.settings.cookie_expire_time));
PU_COOKIEBAR_AS_POPUP=(typeof PU_COOKIEBAR_AS_POPUP !== 'undefined' ? PU_COOKIEBAR_AS_POPUP : false);
var CLI_Cookie={
    set: function (name, value, time) {
        if (time) {
            var date = new Date();
            date.setTime(date.getTime() + time);
            var expires = "; expires=" + date.toGMTString();
        } else
            var expires = PU_ACCEPT_COOKIE_EXPIRE;
        document.cookie = name + "=" + value + ";expires=" + expires + "; path=/";
        if(time<1) {
            host_name=window.location.hostname;
            document.cookie = name + "=" + value + ";expires="+ expires + "; path=/; domain=."+host_name+";";
            if(host_name.indexOf("www")!=1) {  
                var host_name_withoutwww=host_name.replace('www','');
                document.cookie = name + "=" + value + ";expires="+ expires + "; path=/; domain="+host_name_withoutwww+";";
            }
            host_name=host_name.substring(host_name.lastIndexOf(".", host_name.lastIndexOf(".")-1));
            document.cookie = name + "=" + value + ";expires="+ expires + "; path=/; domain="+host_name+";";
        }
    },
    read: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];

            while (c.charAt(0) == ' ') {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    },
    erase: function (name) {
        this.set(name, "", -10);
    },
    exists: function (name) {
        return (this.read(name) !== null);
    },
    getallcookies:function() {
        var pairs = document.cookie.split(";");
        var cookieslist = {};
        for (var i = 0; i < pairs.length; i++) {
            var pair = pairs[i].split("=");
            cookieslist[(pair[0] + '').trim()] = unescape(pair[1]);
        }
        return cookieslist;
    }
}

var CLI=
{
    bar_config:{},
    showagain_config:{},
    set:function(args)
    {
        if(typeof JSON.parse !== "function") 
        {
            console.log("CookieLawInfo requires JSON.parse but your browser doesn't support it");
            return;
        }

        this.settings = args.settings;
        this.bar_elm=jQuery(this.settings.notify_div_id);
        this.showagain_elm = jQuery(this.settings.showagain_div_id);

        /* buttons */
        this.main_button=jQuery('.cli-plugin-main-button');
        this.main_link = jQuery('.cli-plugin-main-link');
        this.reject_link = jQuery('.cookie_action_close_header_reject');
            this.delete_link=jQuery(".cookielawinfo-cookie-delete");
            this.settings_button=jQuery('.cli_settings_button');

        this.configBar();
        this.toggleBar();
        this.attachDelete();
        this.attachEvents();
            this.configButtons();
        
        var cli_hidebar_on_readmore=this.hideBarInReadMoreLink();
        if(this.settings.scroll_close===true && cli_hidebar_on_readmore===false) 
        {
            window.addEventListener("scroll",CLI.closeOnScroll, false);
        }
    },
    hideBarInReadMoreLink:function()
    {
        if(CLI.settings.button_2_hidebar===true && this.main_link.length>0 && this.main_link.hasClass('cli-minimize-bar'))
        {
            this.hideHeader();
            this.showagain_elm.slideDown(this.settings.animate_speed_show);
            return true;
        }
        return false;
    },
    attachEvents:function()
    {
        jQuery('.cli_action_button').click(function(e){
            e.preventDefault();
            var elm=jQuery(this);
            var button_action=elm.attr('data-cli_action');
            var open_link=elm[0].hasAttribute("href") && elm.attr("href") != '#' ? true : false;
            var new_window=false;
            if(button_action=='accept')
            {
                CLI.accept_close();
                new_window=CLI.settings.button_1_new_win ? true : false;
            }
            if(open_link)
            {
                if(new_window)
                {
                    window.open(elm.attr("href"),'_blank');
                }else
                {
                    window.location.href =elm.attr("href");
                }  
            }
        });
        this.settingsPopUp();
        this.settingsTabbedAccordion();
        this.toggleUserPreferenceCheckBox();
    },
    toggleUserPreferenceCheckBox:function()
    {
        jQuery('.cli-user-preference-checkbox').each(function(){
            if(jQuery(this).is(':checked'))
            {
                CLI_Cookie.set('cookielawinfo-'+jQuery(this).attr('data-id'),'yes',PU_ACCEPT_COOKIE_EXPIRE);
            }else
            {
                CLI_Cookie.set('cookielawinfo-'+jQuery(this).attr('data-id'),'no',PU_ACCEPT_COOKIE_EXPIRE);    
            }
        });
        jQuery('.cli-user-preference-checkbox').click(function(){
            if(jQuery(this).is(':checked'))
            {
                CLI_Cookie.set('cookielawinfo-'+jQuery(this).attr('data-id'),'yes',PU_ACCEPT_COOKIE_EXPIRE);
            }else
            {
                CLI_Cookie.set('cookielawinfo-'+jQuery(this).attr('data-id'),'no',PU_ACCEPT_COOKIE_EXPIRE);    
            }
        });
    },
    settingsPopUp:function()
    {   
        jQuery('.cli_settings_button').click(function (e) {
            e.preventDefault();
            jQuery('#cliSettingsPopup').addClass("cli-show").css({'opacity':0}).animate({'opacity':1});
            jQuery('#cliSettingsPopup').removeClass('cli-blowup cli-out').addClass("cli-blowup");
            jQuery('body').addClass("cli-modal-open");
            jQuery(".cli-settings-overlay").addClass("cli-show");
            jQuery("#cookie-law-info-bar").css({'opacity':.1});
            if(!jQuery('.cli-settings-mobile').is(':visible'))
            {
                jQuery('#cliSettingsPopup').find('.cli-nav-link:eq(0)').click();
            }
        });
        jQuery('#cliModalClose').click(function(){
            CLI.settingsPopUpClose();
        });
        jQuery("#cliSettingsPopup").click(function(e){
            if(!(document.getElementsByClassName('cli-modal-dialog')[0].contains(e.target)))
            {
                CLI.settingsPopUpClose();
            }
        });
        jQuery('.cli_enable_all_btn').click(function(){
            var cli_toggle_btn = jQuery(this);
            var enable_text = cli_toggle_btn.attr('data-enable-text');
            var disable_text= cli_toggle_btn.attr('data-disable-text');
            if(cli_toggle_btn.hasClass('cli-enabled')){
                CLI.disableAllCookies();
                cli_toggle_btn.html(enable_text);
            }
            else
            {
                CLI.enableAllCookies();
                cli_toggle_btn.html(disable_text);

            }
            jQuery(this).toggleClass('cli-enabled');
        });
        
        this.privacyReadmore();
    },
    settingsTabbedAccordion:function()
    {
        jQuery(".cli-tab-header").on("click", function(e) {
            if(!(jQuery(e.target).hasClass('cli-slider') || jQuery(e.target).hasClass('cli-user-preference-checkbox')))
            {
                if (jQuery(this).hasClass("cli-tab-active")) {
                    jQuery(this).removeClass("cli-tab-active");
                    jQuery(this)
                      .siblings(".cli-tab-content")
                      .slideUp(200);

                  } else {
                    jQuery(".cli-tab-header").removeClass("cli-tab-active");
                    jQuery(this).addClass("cli-tab-active");
                    jQuery(".cli-tab-content").slideUp(200);
                    jQuery(this)
                      .siblings(".cli-tab-content")
                      .slideDown(200);
                  }
            }   
          });
    },
    settingsPopUpClose:function()
    {
        jQuery('#cliSettingsPopup').removeClass('cli-show');
        jQuery('#cliSettingsPopup').addClass('cli-out');
        jQuery('body').removeClass("cli-modal-open");
        jQuery(".cli-settings-overlay").removeClass("cli-show");
        jQuery("#cookie-law-info-bar").css({'opacity':1});
    },
    privacyReadmore:function()
    {   
        var el= jQuery('.cli-privacy-content .cli-privacy-content-text'),
        clone= el.clone(),
        originalHtml= clone.html(),
        originalHeight= el.outerHeight(),
        Trunc = {
        addReadmore:function(textBlock)
        {   
            if(textBlock.text().length > 250)
            {
                jQuery('.cli-privacy-readmore').show();
            }
            else
            {
                jQuery('.cli-privacy-readmore').hide();
            }
        },
        truncateText : function( textBlock ) {            
            while (textBlock.text().length > 250 ) 
            {
                textBlock.text(function(index, text) {
                return text.replace(/\W*\s(\S)*$/, '...');
                });
            }
        },     
        replaceText: function ( textBlock, original ){
            return textBlock.html(original).height(originalHeight);      
        }  
        
        };
        Trunc.addReadmore(el);
        Trunc.truncateText(el);
        jQuery('a.cli-privacy-readmore').click(function(e){
            e.preventDefault();
            if(jQuery('.cli-privacy-overview').hasClass('cli-collapsed'))
            {   
                Trunc.truncateText(el);
                jQuery('.cli-privacy-overview').removeClass('cli-collapsed');
                el.css('height', '100%');
            }
            else
            {
                jQuery('.cli-privacy-overview').addClass('cli-collapsed');
                Trunc.replaceText(el, originalHtml);
            }
            
            
        });
    },
    attachDelete:function()
    {
        if(this.settings.delete_cookie) {
            CLI_Cookie.erase(PU_ACCEPT_COOKIE_NAME);
            return false;
        }
    },
    configButtons:function()
    {
        /*[cookie_button] */
        this.main_button.css('color',this.settings.button_1_link_colour);
        if(this.settings.button_1_as_button) 
        {
            this.main_button.css('background-color',this.settings.button_1_button_colour);
            this.main_button.hover(function () {
                jQuery(this).css('background-color',CLI.settings.button_1_button_hover);
            },function (){
                jQuery(this).css('background-color',CLI.settings.button_1_button_colour);
            });
        }

        /* [cookie_link] */    
        this.main_link.css('color',this.settings.button_2_link_colour);
        if(this.settings.button_2_as_button) 
        {
            this.main_link.css('background-color',this.settings.button_2_button_colour);
            this.main_link.hover(function () {
                jQuery(this).css('background-color',CLI.settings.button_2_button_hover);
            },function (){
                jQuery(this).css('background-color',CLI.settings.button_2_button_colour);
            });
        }
    },
    toggleBar:function()
    {
        if(!CLI_Cookie.exists(PU_ACCEPT_COOKIE_NAME)) 
        {
            this.displayHeader();
        }else
        {
            this.hideHeader();
        }
        if(this.settings.show_once_yn) 
        {
            setTimeout(function(){
                CLI.close_header();
            },CLI.settings.show_once);
        }

        this.showagain_elm.click(function (e) {
            e.preventDefault();
            CLI.showagain_elm.slideUp(CLI.settings.animate_speed_hide,function() 
            {
                CLI.bar_elm.slideDown(CLI.settings.animate_speed_show);
            });
        });
    },
    configShowAgain:function()
    {
        this.showagain_config = {
            'background-color': this.settings.background,
            'color':this.l1hs(this.settings.text),
            'position': 'fixed',
            'font-family': this.settings.font_family
        };
        if(this.settings.border_on) 
        {
            var border_to_hide = 'border-' + this.settings.notify_position_vertical;
            this.showagain_config['border'] = '1px solid ' + this.l1hs(this.settings.border);
            this.showagain_config[border_to_hide] = 'none';
        }
        var cli_win=jQuery(window);
        var cli_winw=cli_win.width();
        var showagain_x_pos=this.settings.showagain_x_position;
        if(cli_winw<300)
        {
            showagain_x_pos=10;
            this.showagain_config.width=cli_winw-20;
        }else
        {
            this.showagain_config.width='auto';
        }
        var cli_defw=cli_winw>400 ? 500 : cli_winw-20;

        if(this.settings.notify_position_vertical == "top") 
        {
            this.showagain_config.top = '0';
        }
        else if(this.settings.notify_position_vertical == "bottom") 
        {
            this.bar_config['position'] = 'fixed';
            this.bar_config['bottom'] = '0';
            this.showagain_config.bottom = '0';
        }
        if(this.settings.notify_position_horizontal == "left") 
        {
            this.showagain_config.left =showagain_x_pos;
        }else if(this.settings.notify_position_horizontal == "right") 
        {
            this.showagain_config.right =showagain_x_pos;
        }
 
        this.showagain_elm.css(this.showagain_config);      
    },
    configBar:function()
    {
        this.bar_config = {
            'background-color':this.settings.background,
            'color':this.settings.text,
            'font-family':this.settings.font_family
        };
        if(this.settings.notify_position_vertical=="top") 
        {
            this.bar_config['top'] = '0';
            if(this.settings.header_fix === true) 
            {
                this.bar_config['position'] = 'fixed';
            }
        }else 
        {
            this.bar_config['bottom'] = '0';
        }
        this.configShowAgain();
        this.bar_elm.css(this.bar_config).hide();
    },
    l1hs:function(str) 
    {
        if (str.charAt(0) == "#") {
            str = str.substring(1, str.length);
        } else {
            return "#" + str;
        }
        return this.l1hs(str);
    },
    close_header:function() 
    {
        CLI_Cookie.set(PU_ACCEPT_COOKIE_NAME,'yes',PU_ACCEPT_COOKIE_EXPIRE);
        this.hideHeader();
    },
    accept_close:function() 
    {        
        //this.hidePopupOverlay();
        CLI_Cookie.set(PU_ACCEPT_COOKIE_NAME,'yes',PU_ACCEPT_COOKIE_EXPIRE);
        if(this.settings.notify_animate_hide) 
        {
            this.bar_elm.slideUp(this.settings.animate_speed_hide);
        }else 
        {
            this.bar_elm.hide();
        }
        if(this.settings.enabled_sticky_bar && this.settings.showagain_tab) 
        {
            this.showagain_elm.slideDown(this.settings.animate_speed_show);
        }
        if(this.settings.accept_close_reload === true) 
        {
            this.reload_current_page();
        }
        return false;
    },
    reject_close:function() 
    {
        //this.hidePopupOverlay();
        for(var k in Cli_Data.nn_cookie_ids) 
        {
            CLI_Cookie.erase(Cli_Data.nn_cookie_ids[k]);
        }
        CLI_Cookie.set(PU_ACCEPT_COOKIE_NAME,'no',PU_ACCEPT_COOKIE_EXPIRE);
        if(this.settings.notify_animate_hide) 
        {
            this.bar_elm.slideUp(this.settings.animate_speed_hide);
        } else 
        {
            this.bar_elm.hide();
        }
        if(this.settings.enabled_sticky_bar && this.settings.showagain_tab) 
        {
            this.showagain_elm.slideDown(this.settings.animate_speed_show);
        }
        if(this.settings.reject_close_reload === true) 
        {
            this.reload_current_page();
        }
        return false;
    },
    reload_current_page:function()
    {
        if(typeof cli_flush_cache!=='undefined' && cli_flush_cache==1)
        {
            window.location.href=this.add_clear_cache_url_query();
        }else
        {
            window.location.reload(true);
        }
    },
    add_clear_cache_url_query:function()
    {
        var cli_rand=new Date().getTime()/1000;
        var cli_url=window.location.href;
        var cli_hash_arr=cli_url.split('#');
        var cli_urlparts= cli_hash_arr[0].split('?');
        if(cli_urlparts.length>=2) 
        {
            var cli_url_arr=cli_urlparts[1].split('&');
            cli_url_temp_arr=new Array();
            for(var cli_i=0; cli_i<cli_url_arr.length; cli_i++)
            {               
                var cli_temp_url_arr=cli_url_arr[cli_i].split('=');
                if(cli_temp_url_arr[0]=='cli_action')
                {

                }else
                {
                    cli_url_temp_arr.push(cli_url_arr[cli_i]);
                }
            }
            cli_urlparts[1]=cli_url_temp_arr.join('&');
            cli_url=cli_urlparts.join('?')+(cli_url_temp_arr.length>0 ? '&': '')+'cli_action=';
        }else
        {
            cli_url=cli_hash_arr[0]+'?cli_action=';
        }
        cli_url+=cli_rand;
        if(cli_hash_arr.length>1)
        {
            cli_url+='#'+cli_hash_arr[1];
        }
        return cli_url;
    },
    closeOnScroll:function() 
    {
        if(window.pageYOffset > 100 && !CLI_Cookie.read(PU_ACCEPT_COOKIE_NAME)) 
        {
            CLI.accept_close();
            if(CLI.settings.scroll_close_reload === true) 
            {
                window.location.reload();
            }
            window.removeEventListener("scroll",CLI.closeOnScroll,false);
        }
    },
    displayHeader:function() 
    {   
        if(this.settings.notify_animate_show) 
        {
            if( this.settings.enabled_sticky_bar ){
                this.bar_elm.slideUp(this.settings.animate_speed_show);
                this.showagain_elm.slideDown(this.settings.animate_speed_show);
            }else{
                this.bar_elm.slideDown(this.settings.animate_speed_show);
                this.showagain_elm.hide();
            }
            
        }else 
        {
            this.bar_elm.show();
        }
        //this.showagain_elm.hide();  
    },
    hideHeader:function()
    {      
        if(this.settings.showagain_tab) 
        {
            if(this.settings.notify_animate_show) 
            {
                this.showagain_elm.slideDown(this.settings.animate_speed_show);
            } else {
                this.showagain_elm.show();
            }
        }else
        {
            this.showagain_elm.hide();
        }
        this.bar_elm.slideUp(this.settings.animate_speed_show);
        //this.hidePopupOverlay();
    },
    
    setResize:function()
    {
        var resizeTmr=null;
        jQuery(window).resize(function() {
            clearTimeout(resizeTmr);
            resizeTmr=setTimeout(function()
            {
                CLI.configShowAgain();
            },500);
        });
    }
}
jQuery(document).ready(function() {

    if(typeof gdpr_script_data!='undefined')
    {
        CLI.set({
          settings:gdpr_script_data.settings
        });
    }
});