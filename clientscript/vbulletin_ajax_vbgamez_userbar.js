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


function createPreview(id, isadd, configid)
{
                var text = fetch_object('text').value;
                var repeat_x = fetch_object('repeat_x').value;
                var repeat_y = fetch_object('repeat_y').value;
                var radius = fetch_object('radius').value;
                var font = fetch_object('font').value;
                var fontsize = fetch_object('fontsize').value;
                var fontcolor = fetch_object('fontcolor').value;
                var imagesize = fetch_object('imagesize').value;
             
                if(text == '' || repeat_x == '' || repeat_y == '')
                {
                            alert(vbphrase['vbgamez_emptyfields']);
                }else{
                            vbg_show_progress('userbar');
                            doCreatePreview(id, text, repeat_x, repeat_y, radius, font, fontsize, fontcolor, imagesize, isadd, configid);
                }
}

function doCreatePreview(id, text, repeat_x, repeat_y, radius, font, fontsize, fontcolor, imagesize, isadd, configid)
{

                    var sUrl = 'vbgamez_userbar.php';
                    var postData = 'do=newpreview&ajax=1&securitytoken=' + SECURITYTOKEN + '&id=' + id + '&text=' + text + '&repeat_x=' + repeat_x + '&repeat_y=' + repeat_y + '&radius=' + radius + '&font=' + font + '&fontsize=' + fontsize + '&fontcolor=' + fontcolor + '&imagesize=' + imagesize + '&isadd=' + isadd + '&configid=' + configid;
                    var handleSuccess = function(o)
                    {
                        if(o.responseText !== undefined)
                        {
                            fetch_object('vbg_preview_div').innerHTML = o.responseText;
                            document.location.hash='top'; 
                            vbg_hide_progress('userbar');
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