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


function vBG_showFieldContnent(selectedvalue)
{
         vbg_hide_all();

         if(selectedvalue == 'input')
         {
               vbg_show(1); 
               vbg_show(3); 
         }

         if(selectedvalue == 'textarea')
         {
               vbg_show(2); 
               vbg_show(3); 
               vbg_show(4); 
         }

         if(selectedvalue == 'select')
         {
               vbg_show(5); 
         }

         if(selectedvalue == 'radio')
         {
               vbg_show(6); 
         }

         if(selectedvalue == 'checkbox')
         {
               vbg_show(7); 
         }

         if(selectedvalue == 'select_multiple')
         {
               vbg_show(8); 
         }
}

function vbg_show(objectid)
{
               fetch_object('vbg_objid' + objectid).style.display = '';
}

function vbg_hide(objectid)
{
               fetch_object('vbg_objid' + objectid).style.display = 'none';
}

function vbg_hide_all()
{
               vbg_hide(1);
               vbg_hide(2);
               vbg_hide(3);
               vbg_hide(4);
               vbg_hide(5);
               vbg_hide(6);
               vbg_hide(7);
               vbg_hide(8);
}
function vbg_remove(first, two, three)
{
          vbg_remove_element(first, two, three, 1);
          vbg_remove_element(first, two, three, 2);
          vbg_remove_element(first, two, three, 3);
          vbg_remove_element(first, two, three, 4);
          vbg_remove_element(first, two, three, 5);
          vbg_remove_element(first, two, three, 6);
          vbg_remove_element(first, two, three, 7);
          vbg_remove_element(first, two, three, 8);

}

function vbg_remove_element(first, two, three, objectid)
{
               if(first != objectid && two != objectid && three != objectid)
               {
                               fetch_object('vbg_objid' + objectid).innerHTML = '';
               }
}

function vbg_prepare_clear()
{
         var selectedvalue = fetch_object('sel_type_3').value;

         if(selectedvalue == 'input')
         {
               vbg_remove(1, 3);
         }

         if(selectedvalue == 'textarea')
         {
               vbg_remove(2, 3, 4);
         }

         if(selectedvalue == 'select')
         {
               vbg_remove(5);
         }

         if(selectedvalue == 'radio')
         {
               vbg_remove(6);
         }

         if(selectedvalue == 'checkbox')
         {
               vbg_remove(7);
         }

         if(selectedvalue == 'select_multiple')
         {
               vbg_remove(8);
         }
}
// dateline 23:40 29.06.2010