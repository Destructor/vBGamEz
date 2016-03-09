/*======================================================================*\
|| #################################################################### ||
|| # vBGamEz 6.0 Beta 4
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2008-2011 vBGamEz Team. All Rights Reserved.            ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBGAMEZ IS NOT FREE SOFTWARE ------------------ # ||
|| # http://www.vbgamez.com                                           # ||
|| #################################################################### ||
\*======================================================================*/

var vbgamez_overlay_width = '';
var vbgamez_overlay_height = '';

function vbgamez_quickedit_Init(commentid)
{
    if(fetch_object('comment_edit_restore_' + commentid).innerHTML == "")
    {
         fetch_object('comment_edit_restore_' + commentid).innerHTML = fetch_object('comment_edit_' + commentid).innerHTML;
         vbg_set_cursor('wait');
         vbg_show_progress(commentid);  

    }else{
         window.location.href = vbg_comment_scriptname + '?do=editcomment&id=' + commentid;
    }
 
    var sUrl = vbgamez_post_path;
    var postData = 'do=editcomment&ajax=1&securitytoken=' + SECURITYTOKEN + '&id=' + commentid;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
            fetch_object('comment_edit_' + commentid).innerHTML = o.responseText;

            vbg_hide_progress(commentid);
            vbg_set_cursor();
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}

function vbgamez_quickedit_restore(commentid)
{

  fetch_object('comment_edit_' + commentid).innerHTML = fetch_object('comment_edit_restore_' + commentid).innerHTML;
  fetch_object('comment_edit_restore_' + commentid).innerHTML = '';
}

function vbgamez_quickedit_save(commentid)
{
    vbg_set_cursor('wait');
    vbg_show_progress(commentid);

    var text = fetch_object('text_edit_' + commentid).value;

    var sUrl = vbgamez_post_path;
    var postData = 'do=doeditcomment&sbutton=1&message=' + encodeURIComponent(text)  + '&ajax=1&securitytoken=' + SECURITYTOKEN + '&id=' + commentid;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
            vbg_hide_progress(commentid);
            vbg_set_cursor();
            fetch_object('comment_edit_restore_' + commentid).innerHTML = '';
		// bad code
	       if(o.responseText.indexOf('<span id="comment_editmessage_' + commentid + '">') != -1) 
		{
			fetch_object('comment_edit_' + commentid).innerHTML = o.responseText;
		}else{
           		alert(o.responseText);
		}
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}

function vbg_show_progress(element_id)
{

  fetch_object('vbg_progress_' + element_id).style.display = '';
}

function vbg_hide_progress(element_id)
{

  fetch_object('vbg_progress_' + element_id).style.display = 'none';
}


function vbg_fetch_rating(serverid)
{

    var sUrl = vbgamez_path;
    var postData = 'do=updrating&serverid=' + serverid + '&securitytoken=' + SECURITYTOKEN;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
	   if(!fetch_object('rating_' + serverid))
	   {
		 YAHOO.util.Dom.getElementsByClassName('rating_' + serverid).innerHTML = o.responseText;
           }else{
           	fetch_object('rating_' + serverid).innerHTML = o.responseText;
	   }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}



function vbg_rate(serverid, type)
{
	
	// for captcha-s support, non-ajax
	if(SECURITYTOKEN == 'guest')
	{
		window.location.href = vbgamez_path + '?do=ratingplus&serverid=' + serverid;
		return false;
	}
    var sUrl = vbgamez_path;

    if(type == 'plus')
    {
         var postData = 'do=ratingplus&serverid=' + serverid + '&securitytoken=' + SECURITYTOKEN + '&fromajax=1';
    }else{
         var postData = 'do=ratingminus&serverid=' + serverid + '&securitytoken=' + SECURITYTOKEN + '&fromajax=1';
    }

    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
           if(o.responseText == ""){

               vbg_fetch_rating(serverid);

           }else if(o.responseText != "" && o.responseText != 'verify')
           {
               alert(o.responseText);
           }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}


function fetch_steam(game)
{
    fetch_object('steam_div').innerHTML = '';
    fetch_object('steam_blockrow').style.display = 'none';

    var sUrl = vbgamez_path;
    var postData = 'do=showsteam&game=' + game + '&securitytoken=' + SECURITYTOKEN;

    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
           if(o.responseText != "")
           {
               fetch_object('steam_div').innerHTML = o.responseText;
               fetch_object('steam_blockrow').style.display = '';
           }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}

function vbg_whoplaying(serverid)
{
        window.open(vbgamez_path + '?do=whoplaying&serverid=' + serverid, 'vbg_whoplaying_' + serverid,'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=600,height=520');
}

function vbg_viewinfo(serverid)
{
        window.open(vbgamez_usercp_path + '?do=viewinfo&id=' + serverid, 'vbg_viewinfo_' + serverid,'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=700,height=520');
}

function vbg_detalis(serverid)
{
        window.open(vbgamez_path + '?do=viewdetalis&id=' + serverid, 'vbg_viewdetalis_' + serverid,'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=900,height=500');
}

function vbg_imwindow(icq)
{
        window.open('http://www.icq.com/people/webmsg.php?to=' + icq, 'vbg_imwindow_' + icq,'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=500,height=520');
}

function vbg_detalisbanned(serverid)
{
        window.open(vbgamez_path + '?do=viewbanned&id=' + serverid, 'vbg_detalisbanned_' + serverid,'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=500,height=520');
}
function open_vk_window(url)
{
        window.open(url, 'vk_share','statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=500,height=320');
}
function vbg_getCaptcha()
{
	var postvars = '';
	// code from vBCMS
	var captcha_text = (document.getElementById('imageregt')?'imageregt':'recaptcha_response_field');
	var post_hash = 'hash';
	if (document.getElementById(captcha_text) != undefined && document.getElementById(captcha_text).value != ''
	&& document.getElementById(post_hash) != undefined && document.getElementById(post_hash).value != '')
	{
		postvars += '&humanverify[input]=' + PHP.urlencode(document.getElementById(captcha_text).value);
		postvars += '&humanverify[hash]=' + PHP.urlencode(document.getElementById(post_hash).value);
		// add check for re-captcha in case board is set up for that
		if (document.getElementById('recaptcha_challenge_field') != undefined && document.getElementById('recaptcha_challenge_field').value != '')
		{
			postvars += '&recaptcha_challenge_field=' + PHP.urlencode(document.getElementById('recaptcha_challenge_field').value);
		}
		if (document.getElementById('recaptcha_response_field') != undefined && document.getElementById('recaptcha_response_field').value != '')
		{
			postvars += '&recaptcha_response_field=' + PHP.urlencode(document.getElementById('recaptcha_response_field').value);
		}
	}
	else if (document.getElementById('humanverify') && document.getElementById(post_hash))
	{
		postvars += '&humanverify[input]=' + PHP.urlencode(document.getElementById('humanverify').value);
		postvars += '&humanverify[hash]=' + PHP.urlencode(document.getElementById(post_hash).value);
	}
	
	return postvars;
}
function vbg_PostComment(serverid)
{
    var text = PHP.trim(fetch_object('new_comment').value);

    if(text == '' || text == '0') { return false; }

    vbg_set_cursor('wait');
    vbg_show_progress('new_comment');

    var newcomment_content = fetch_object('last_comment').innerHTML;
    var counter = fetch_object('vbg_counter').innerHTML;

	var postvars = '';
    if(SECURITYTOKEN == 'guest')
    {
		var postvars = vbg_getCaptcha();
    }

    var sUrl = vbgamez_post_path;
    var postData = 'do=doaddcomment&sbutton=1&id=' + serverid + '&ajax=1&message=' + encodeURIComponent(text) + '&securitytoken=' + SECURITYTOKEN + postvars + '&page=' + vbg_page;

    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {	
           if(o.responseText != "")
           {
               vbg_hide_progress('new_comment');
		// bad code
	       if(o.responseText.indexOf('<div id="comment_message_') != -1) 
		{
			
                  fetch_object('new_comment').value = '';
                  fetch_object('last_comment').innerHTML = newcomment_content + o.responseText;

                  if(vbg_comments_moderation == 0)
                  {
                           fetch_object('vbg_counter').innerHTML = parseFloat(counter) + 1;
                           fetch_object('vbg_commentstats').style.display = '';
                  }

		  if(fetch_object('vbg_actions').style.display == 'none')
		  {
                  	fetch_object('vbg_actions').style.display = '';
			fetch_object('vbg_actions').innerHTML += '<br />';
		  }

                  vbg_set_cursor();
                  if(SIMPLEVERSION >= 4)
                  {
                          	YAHOO.vBulletin.vBPopupMenu.instrument('last_comment');
				YAHOO.vBulletin.vBPopupMenu.instrument('vbg_actions');
                  }		  
               }else{
				if(o.responseText.indexOf('go_to_page:') != -1)
				{
					window.location.href = o.responseText.replace('go_to_page:', '');
				}else{
					vbg_set_cursor();  alert(o.responseText);  return false;
				}
	       }
           }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}

function vbg_fetch_additional_game_type(gametype)
{
    fetch_steam(gametype);
    fetch_object('search_additional').innerHTML = '';

    if(gametype == 'bf1942' || gametype == 'farcry' || gametype == 'halflife' || gametype == 'halflifewon' || gametype == 'source')
    {
         // good
    }else{
        return false; 
    }
   
    vbg_show_progress('search'); 
 
    var sUrl = vbgamez_path;
    var postData = 'do=fetch_type&ajax=1&securitytoken=' + SECURITYTOKEN + '&type=' + gametype;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
            if(o.responseText != "")
            {
                 fetch_object('search_additional').innerHTML = o.responseText;
            }
            vbg_hide_progress('search');
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);

}

function vbg_upload_map(serverid, gametype, game, mapname)
{
    var sUrl = vbgamez_path;
    var postData = 'do=uploadmap&ajax=1&securitytoken=' + SECURITYTOKEN + '&id=' + serverid + '&type=' + encodeURIComponent(gametype) + '&game=' + encodeURIComponent(game) + '&mapname=' + encodeURIComponent(mapname);
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
            if(o.responseText != "")
            {
                        vbg_div_insert_data(o.responseText);

                        fetch_object('vbg_uploadbutton').onclick = function(e) { vbg_do_upload_map(serverid, gametype, game, mapname); }

                        fetch_object('vbg_closeuploadbutton').onclick = function(e) { vbg_closeupload_map(); }

            }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}


function vbg_do_upload_map(serverid, gametype, game, mapname)
{
	vbgUploadMapFile(true);
    var sUrl = vbgamez_path;
    var filename = fetch_object('vbg_filename').value;

    var postData = 'do=douploadmap&ajax=1&securitytoken=' + SECURITYTOKEN + '&id=' + serverid + '&type=' + encodeURIComponent(gametype) + '&game=' + encodeURIComponent(game) + '&filename=' + encodeURIComponent(filename) + '&mapname=' + encodeURIComponent(mapname);
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
            if(o.responseText != "")
            {
                       if(o.responseText == 'OK')
                       {
                            document.forms.vbg_uploadfile.submit();
                       }else{
							fetch_object('vbgmapuploadimage').style.display = 'none';
                            alert(o.responseText);
                       }
            }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}

function vbg_closeupload_map()
{ 
    vbg_div_insert_data('');
}

function vbg_update_server(serverid, isvb3)
{
    var sort_option = fetch_object('vbg_sort_players').value;
	var order_option = fetch_object('vbg_order_players').value;
	
    fetch_object('vbg_updater').style.display = '';
    var sUrl = vbgamez_path;
    var postData = 'do=view&id=' + serverid + '&ajax=1&securitytoken=' + SECURITYTOKEN + '&sort_by_field=' + sort_option + '&order=' + order_option;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
               if(o.responseText == '') { return false; }

               fetch_object('vbg_serverinfo').innerHTML = o.responseText;

               if(isvb3)
               {
                           fetch_object('vbg_updater').style.display = 'none';
               }
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}

function vbg_sort_players(serverid, isvb3, field, playersorder)
{
    fetch_object('vbg_sort_players').value = field;
	fetch_object('vbg_order_players').value = playersorder;
    vbg_update_server(serverid, isvb3);
}

function replaceFrameLinks(id)
{
	var all_frames = fetch_object('frameidselect').options;
	for(i=0;i<all_frames.length;i++)
	{
		fetch_object('framecfghrefurl').href = fetch_object('framecfghrefurl').href.replace('sid=' + all_frames[i].value, 'sid=' + id);
		document.location.hash = document.location.hash.replace('&frameid=' + all_frames[i].value, '');
	}
}
function vbgloadFrame(id, serverid)
{
	if(!fetch_object('frameload'))
	{
		return false;
	}
	fetch_object('frameload').style.visibility = 'visible';
    var sUrl = vbgamez_path;
    var postData = 'do=loadframe&serverid=' + serverid + '&id=' + id + '&securitytoken=' + SECURITYTOKEN;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
			   var frame = eval('(' + o.responseText + ')');
			   fetch_object('frame_list').innerHTML = frame.content;
			   fetch_object('frameload').style.visibility = 'hidden';
			   if(frame.configure)
			   {
					fetch_object('framecfg_url').style.display = '';
				}else{
					fetch_object('framecfg_url').style.display = 'none';
				}
				fetch_object('frametitle').innerHTML = frame.title;
				replaceFrameLinks(id);
				document.location.hash += '&frameid=' + id;
				fetch_object('frameidselect').value = id;
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}
function replaceUserbarLinks(id, method)
{
	var all_userbars = fetch_object('userbaridselect').options;
	for(i=0;i<all_userbars.length;i++)
	{
		if(method == 1)
		{
			fetch_object('userbarediturl').href = fetch_object('userbarediturl').href.replace('id=' + all_userbars[i].value, 'id=' + id);
		}else if(method == 2)
		{
			fetch_object('userbarexampleurl').href = fetch_object('userbarexampleurl').href.replace('id=' + all_userbars[i].value, 'id=' + id);
		}else if(method == 3)
		{
			document.location.hash = document.location.hash.replace('&userbarid=' + all_userbars[i].value, '');
		}
	}
}

function vbgloadUserbar(id, serverid)
{
	if(!fetch_object('userbarload'))
	{
		return false;
	}

	fetch_object('userbarload').style.visibility = 'visible';
    var sUrl = vbgamez_path;
    var postData = 'do=loaduserbar&serverid=' + serverid + '&id=' + id + '&securitytoken=' + SECURITYTOKEN;
    var handleSuccess = function(o)
    {
        if(o.responseText !== undefined)
        {
			   var userbar = eval('(' + o.responseText + ')');
			   fetch_object('userbar_list').innerHTML = userbar.content;
			   fetch_object('userbarload').style.visibility = 'hidden';

			   if(userbar.is_owner)
			   {
					fetch_object('userbaredithref').style.display = '';
					replaceUserbarLinks(id, 1);
					fetch_object('userbarexamplehref').style.display = 'none';
					
				}else{
					if(userbar.examplecreate)
					{
						fetch_object('userbaredithref').style.display = 'none';
						fetch_object('userbarexamplehref').style.display = '';
						replaceUserbarLinks(id, 2);
					}else{
						
						if(fetch_object('userbaredithref'))
						{
							fetch_object('userbaredithref').style.display = 'none';
							fetch_object('userbarexamplehref').style.display = 'none';
						}
					}
				}
				fetch_object('userbartitle').innerHTML = userbar.title;
				replaceUserbarLinks(id, 3);
				document.location.hash += '&userbarid=' + id;
				fetch_object('userbaridselect').value = id;
        }
    }
    var handleFailure = function(o)
    {
        if(o.responseText !== undefined)
        {
            alert(o.responseText);
        }
    }
    var callback =
    {
        success: handleSuccess,
        failure: handleFailure,
        timeout: vB_Default_Timeout
    };
    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);
}

function vbg_switch_tab(type)
{ 
    if(top_rating)
    {
          fetch_object('tab_rating').setAttribute("class", "");
          fetch_object('rating').style.display = 'none';
    }

    if(top_visiting)
    {
          fetch_object('tab_visits').setAttribute("class", "");
          fetch_object('visits').style.display = 'none';
    }

    if(top_views)
    {
          fetch_object('tab_views').setAttribute("class", "");
          fetch_object('views').style.display = 'none';
    }

    if(top_comments)
    {
          fetch_object('tab_comments').setAttribute("class", "");
          fetch_object('comments').style.display = 'none';
    }

    if(top_maps)
    {
          fetch_object('tab_maps').setAttribute("class", "");
          fetch_object('maps').style.display = 'none';
    }

    if(detalis_main)
    {
          fetch_object('tab_content_container').setAttribute("class", "");
          fetch_object('content_container').style.display = 'none';
    }

    if(detalis_codes)
    {
          fetch_object('tab_codes').setAttribute("class", "");
          fetch_object('codes').style.display = 'none';
    }

    if(detalis_codes)
    {
          fetch_object('tab_codes2').setAttribute("class", "");
          fetch_object('codes2').style.display = 'none';
    }

    if(detalis_graphics)
    {
          fetch_object('tab_graphics').setAttribute("class", "");
          fetch_object('graphics').style.display = 'none';
    }

    if(detalis_api)
    {
          fetch_object('tab_api').setAttribute("class", "");
          fetch_object('api').style.display = 'none';
    }

    fetch_object(type).style.display = '';
    fetch_object('tab_' + type).setAttribute("class", "selected");
	if(!vbgGetHashString('tab'))
	{
    	document.location.hash = '#tab=' + type + document.location.hash.replace('#', '');
	}else{
    	document.location.hash = document.location.hash.replace('tab=' + vbgGetHashString('tab'), 'tab=' + type);
	}
}


function vbgGetHashString(who)
{
	var vars = document.location.hash.split('&');
	for(i=0;i<vars.length;i++)
	{
		var data = vars[i].split('=');
		data[0] = data[0].replace('#', '');
		if(who == data[0])
		{
			return data[1];
		}
	}
}
function vbg_hightlight(thisinfo, id)
{
       if(thisinfo.checked == true)
       {
                 fetch_object('comment_message_' + id).setAttribute("class", "highlight");
       }else{
                 fetch_object('comment_message_' + id).removeAttribute("class", "highlight");
       }
       vbg_inlinemod_count();
}

function vbg_select_all_checkbox(type, is_vb3)
{

var count = 0;
var inputs = document.getElementsByTagName('input');

   for (var i = 0; i < inputs.length; i++)
    {
      if(type == 'selectall')
      {
        if (inputs[i].type == 'checkbox' && inputs[i].name == 'commentsarray[]')
        {
           if(inputs[i].checked == false)
            { 
               document.getElementById(inputs[i].id).checked = true;
               vbg_hightlight(inputs[i], inputs[i].value);
            }
        }
      }else if(type == 'unselectall')
      {
        if (inputs[i].type == 'checkbox' && inputs[i].name == 'commentsarray[]')
        {
           if(inputs[i].checked == true)
            { 
               document.getElementById(inputs[i].id).checked = false;
               vbg_hightlight(inputs[i], inputs[i].value);
            }
        }
      }else if(type == 'invertselected')
      {
        if (inputs[i].type == 'checkbox' && inputs[i].name == 'commentsarray[]')
        {
           if(inputs[i].checked == true)
            { 

               document.getElementById(inputs[i].id).checked = false;
               vbg_hightlight(inputs[i], inputs[i].value);
            }else{

               document.getElementById(inputs[i].id).checked = true;
               vbg_hightlight(inputs[i], inputs[i].value);
            }
        }
      }
    }
 
  if(is_vb3)
  {
           vbmenu_hide('imod');
  }else{
           YAHOO.vBulletin.vBPopupMenu.close_all('view-server-popups');
  }
}

function vbg_inlinemod_count()
{

var count = 0;
var inputs = document.getElementsByTagName('input');

   for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].type == 'checkbox' && inputs[i].name == 'commentsarray[]')
        {
           if(inputs[i].checked == true)
            { 
               count++;
            }
        }
    }

 fetch_object('vbg_moderation_count').innerHTML = count;
}

function vbg_set_cursor(type)
{
     if(!type)
     {
             var type = 'auto';
     }
     
     document.body.style.cursor = type;
}

function vbg_switch_tab_vb3(type)
{ 
    if(top_rating)
    {
          fetch_object('tab_rating').setAttribute("class", "tborder thead");
          fetch_object('rating').style.display = 'none';
    }

    if(top_visiting)
    {
          fetch_object('tab_visits').setAttribute("class", "tborder thead");
          fetch_object('visits').style.display = 'none';
    }

    if(top_views)
    {
          fetch_object('tab_views').setAttribute("class", "tborder thead");
          fetch_object('views').style.display = 'none';
    }

    if(top_comments)
    {
          fetch_object('tab_comments').setAttribute("class", "tborder thead");
          fetch_object('comments').style.display = 'none';
    }

    if(top_maps)
    {
          fetch_object('tab_maps').setAttribute("class", "tborder thead");
          fetch_object('maps').style.display = 'none';
    }

    if(detalis_main)
    {
          fetch_object('tab_content_container').setAttribute("class", "tborder thead");
          fetch_object('content_container').style.display = 'none';
    }

    if(detalis_codes)
    {
          fetch_object('tab_codes').setAttribute("class", "tborder thead");
          fetch_object('codes').style.display = 'none';
    }

    if(detalis_codes)
    {
          fetch_object('tab_codes2').setAttribute("class", "tborder thead");
          fetch_object('codes2').style.display = 'none';
    }

    if(detalis_graphics)
    {
          fetch_object('tab_graphics').setAttribute("class", "tborder thead");
          fetch_object('graphics').style.display = 'none';
    }

    if(detalis_api)
    {
          fetch_object('tab_api').setAttribute("class", "tborder thead");
          fetch_object('api').style.display = 'none';
    }

    fetch_object(type).style.display = '';
    fetch_object('tab_' + type).setAttribute("class", "tborder tcat");
    document.location.hash = 'tab=' + type;
}

function fetch_blockrow(val, isvb3)
{
      if(PHP.in_array(val, vbg_db_game_types) != '-1')
      {
             var formvaribles = 'var showdbname = ' + val + '_showdbname; var showservername = ' + val + '_showservername; var showserverip = ' + val + '_showserverip;';
             eval(formvaribles);

             if(!isvb3)
             {
                      fetch_object('vbg_blockrow').style.display = 'none';
                      fetch_object('vbg_blockrow_db').style.display = '';
             }else{
                      fetch_object('div_address').style.display = 'none';
                      fetch_object('div_port').style.display = 'none';
                      fetch_object('div_name').style.display = 'none';

                      fetch_object('div_db_address').style.display = '';
                      fetch_object('div_db_user').style.display = '';
                      fetch_object('div_db_password').style.display = '';
             }

             if(showdbname)
             {
                      fetch_object('vbg_blockrow_db_dbname').style.display = '';
             }else{
                      fetch_object('vbg_blockrow_db_dbname').style.display = 'none';
             }

             if(showservername)
             {
                      fetch_object('vbg_blockrow_db_servername').style.display = '';
             }else{
                      fetch_object('vbg_blockrow_db_servername').style.display = 'none';
             }

             if(showserverip)
             {
                      fetch_object('vbg_blockrow_db_serverip').style.display = '';
             }else{
                      fetch_object('vbg_blockrow_db_serverip').style.display = 'none';
             }

             var ppos = document.location.hash.indexOf('#');
             var type = (ppos == -1) ? false : document.location.hash.substr(ppos+1);

             if(!type)
             {
                        document.location.href = document.location.href + '#' + val;
             }
      }else{

             if(!isvb3)
             {
                      fetch_object('vbg_blockrow').style.display = '';
                      fetch_object('vbg_blockrow_db').style.display = 'none';
             }else{
                      fetch_object('div_address').style.display = '';
                      fetch_object('div_port').style.display = '';
                      fetch_object('div_name').style.display = '';

                      fetch_object('div_db_address').style.display = 'none';
                      fetch_object('div_db_user').style.display = 'none';
                      fetch_object('div_db_password').style.display = 'none';
             }

             fetch_object('vbg_blockrow_db_dbname').style.display = 'none';
             fetch_object('vbg_blockrow_db_servername').style.display = 'none';
             fetch_object('vbg_blockrow_db_serverip').style.display = 'none';
      }
}

function blockrow_onload(isvb3)
{
  var ppos = document.location.hash.indexOf('#');
  var type = (ppos == -1) ? false : document.location.hash.substr(ppos+1);
  if(type)
  {
            fetch_blockrow(type, isvb3);

            fetch_object('game').value = type;
  }
}

function vBG_Hide_Overlay()
{	
	document.getElementById("preview_div").style.display = "none";
	document.onmousemove = '';
	document.getElementById("preview_div").style.left = "-500px";
	clearTimeout(timer);
}

function vBG_Show_Overlay(imagename)
{
        var imagename = imagename.replace('c&h', 'c_amp_h');

        if(!fetch_object('preview_div'))
        {
                var newNode = document.createElement('div');
                newNode.setAttribute('id', 'preview_div');
                newNode.setAttribute('style', 'display: none; position: absolute;z-index:110;');
                document.body.appendChild(newNode);
        }

	timer = setTimeout("vbg_show_overlay('" + imagename + "');", 20);

        return false;
}

function vbg_maxlength(obj)
{
      var mlength=obj.getAttribute? parseInt(obj.getAttribute("maxlength")) : ""
      if (obj.getAttribute && obj.value.length > mlength)
      {
               obj.value = obj.value.substring(0, mlength);
      }
}
function vbg_create_div()
{
        if(!fetch_object('vbgamez_ajax_content_div'))
        {
                var newNode = document.createElement('div');
                newNode.setAttribute('id', 'vbgamez_ajax_content_div');
				newNode.style.position = is_ie ? "absolute" : "fixed";
				newNode.style.zIndex = "100";
				newNode.style.width = "600px";
				newNode.style.height = "200px";
                document.body.appendChild(newNode);
				center_element(fetch_object('vbgamez_ajax_content_div'));
        }
}

function vbg_div_insert_data(content)
{
        vbg_create_div();
        fetch_object('vbgamez_ajax_content_div').innerHTML = content;
}
function vbg_hide_loader()
{
          fetch_object('img_loader').style.visibility = 'hidden';
}
function vbg_show_graphic(url, type)
{ 
          fetch_object('img_loader').style.visibility = 'visible';
          fetch_object('stat_img').src = url + '&type=' + type;

}

function vbg_prepare_tabs()
{
	var hashOptions = document.location.hash.replace('#', '').split('&');
	var future_func = false;
	if(future_func)
	{
	if(fetch_object('tab_content_container'))
	{
	 if(!document.location.hash && fetch_object('tab_content_container').getAttribute('class') != 'selected' && detalis_main == 1)
	 {
		if(SIMPLEVERSION > 400)
        {
                  vbg_switch_tab('content_container');
        }else{
                  vbg_switch_tab_vb3('content_container');
        }
	 }
    }

	if(!document.location.hash && detalis_main == 0)
	{
		var elementName = fetch_tags(fetch_object('tab_switcher'), 'li');
		var TabName = elementName[0].id.replace('tab_', '');

		if(SIMPLEVERSION > 400)
        {
                  vbg_switch_tab(TabName);
        }else{
                  vbg_switch_tab_vb3(TabName);
        }
	}
	
}
	for(i=0;i<hashOptions.length;i++)
	{
		var splitString = hashOptions[i].split('=');
		if(splitString[0] == 'frameid')
		{
			vbgloadFrame(splitString[1], serverid);
		}else if(splitString[0] == 'tab')
		{
			if(SIMPLEVERSION > 400)
           {
                     vbg_switch_tab(splitString[1]);
           }else{
                     vbg_switch_tab_vb3(splitString[1]);
           }
		}else if(splitString[0] == 'userbarid')
		{
			vbgloadUserbar(splitString[1], serverid);
		}
	}
}

var offsetfrommouse = [15,25];
var displayduration = 0 ;

var defaultimageheight = 150;
var defaultimagewidth = 150;

var timer;

function vbg_truebody()
{
        return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function vbg_show_overlay(imagename, title, width, height)
{
 
    var docwidth=document.all? vbg_truebody().scrollLeft+vbg_truebody().clientWidth : pageXOffset+window.innerWidth - offsetfrommouse[0]
	var docheight=document.all? Math.min(vbg_truebody().scrollHeight, vbg_truebody().clientHeight) : Math.min(window.innerHeight)

	if( (navigator.userAgent.indexOf("Konqueror")==-1  || navigator.userAgent.indexOf("Firefox")!=-1 || (navigator.userAgent.indexOf("Opera")==-1 && navigator.appVersion.indexOf("MSIE")!=-1)) && (docwidth>650 && docheight>500)) {
		( width == 0 ) ? width = defaultimagewidth: '';
		( height == 0 ) ? height = defaultimageheight: '';
			
		width+=30
		height+=55
		defaultimageheight = height
		defaultimagewidth = width
	
		document.onmousemove=vbg_followmouse; 
		var html_width = '';
		var html_height = '';
		if(vbgamez_overlay_width > 0)
		{
			var html_width = 'height="' + vbgamez_overlay_width + '"';
		}
		if(vbgamez_overlay_height > 0)
		{
			var html_height = 'width="' + vbgamez_overlay_height + '"';
		}

    	        newHTML = '<img style="background:#e9e9e9 none repeat-x; margin: 0; padding: 8px; position: absolute; border: 1px solid #cecece; z-index: 200;"; src="' + imagename + '" border="0" alt="Screenshot" title="Screenshot" ' + html_width + ' ' + html_height + '>';

		if(navigator.userAgent.indexOf("MSIE")!=-1 && navigator.userAgent.indexOf("Opera")==-1 ){
			//newHTML = newHTML+'<iframe src="about:blank" scrolling="no" frameborder="0" ' + html_width + ' ' + html_height + '></iframe>';
		}		

		document.getElementById("preview_div").innerHTML = newHTML;
		document.getElementById("preview_div").style.display="block";
	}
}

function vbg_followmouse(e)
{
	var xcoord=offsetfrommouse[0]
	var ycoord=offsetfrommouse[1]

	var docwidth=document.all? vbg_truebody().scrollLeft+vbg_truebody().clientWidth : pageXOffset+window.innerWidth-15
	var docheight=document.all? Math.min(vbg_truebody().scrollHeight, vbg_truebody().clientHeight) : Math.min(window.innerHeight)

	if (typeof e != "undefined"){
		if (docwidth - e.pageX < defaultimagewidth + 2*offsetfrommouse[0]){
			xcoord = e.pageX - xcoord - defaultimagewidth; // Move to the left side of the cursor
		} else {
			xcoord += e.pageX;
		}
		if (docheight - e.pageY < defaultimageheight + 2*offsetfrommouse[1]){
			ycoord += e.pageY - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + e.pageY - docheight - vbg_truebody().scrollTop));
		} else {
			ycoord += e.pageY;
		}

	} else if (typeof window.event != "undefined"){
		if (docwidth - event.clientX < defaultimagewidth + 2*offsetfrommouse[0]){
			xcoord = event.clientX + vbg_truebody().scrollLeft - xcoord - defaultimagewidth; // Move to the left side of the cursor
		} else {
			xcoord += vbg_truebody().scrollLeft+event.clientX
		}
		if (docheight - event.clientY < (defaultimageheight + 2*offsetfrommouse[1])){
			ycoord += event.clientY + vbg_truebody().scrollTop - Math.max(0,(2*offsetfrommouse[1] + defaultimageheight + event.clientY - docheight));
		} else {
			ycoord += vbg_truebody().scrollTop + event.clientY;
		}
	}
	document.getElementById("preview_div").style.left=xcoord+"px"
	document.getElementById("preview_div").style.top=ycoord+"px"

}

function vbgDisableSteam()
{
	if(fetch_object('steam'))
	{
		if(fetch_object('rb_1_pirated').checked == true)
		{
			fetch_object('steam').setAttribute('disabled', 'disabled');
			fetch_object('nonsteam').setAttribute('disabled', 'disabled');
		}else{
			fetch_object('steam').removeAttribute('disabled');
			fetch_object('nonsteam').removeAttribute('disabled');
		}
	}
}

function vbgSteamInit()
{
	if(fetch_object('rb_1_pirated').checked == true)
	{
		fetch_object('steam').setAttribute('disabled', 'disabled');
		fetch_object('nonsteam').setAttribute('disabled', 'disabled');
	}
}

function vbgUploadMapFile(isbutton)
{
	fetch_object('vbgmapuploadimage').style.display = '';
	if(!isbutton)
	{
		fetch_object('vbg_uploadbutton').click();
	}
}
