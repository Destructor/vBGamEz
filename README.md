vBGamEz 6.0 Beta 4 for vBulletin 4.1.5+
=============================
Installation:
-----------------------------
1. Upload all files from the folder 'upload 'in the root of your folders with the forum.
2. Go to the Admin-panel, Products and modules -> Management product -> Add/import product and select the file product-vbgamez-[your_charset_forum].xml.
-----------------------------
Update:
-----------------------------
1. Upload all files from the folder 'upload' in the root of your folders with the forum, if the FTP manager will ask overwrite, then click "Yes".
2. Go to the Admin-panel, Products and modules -> Manage products -> Add/import product and select the file product-vbgamez-[your_charset_forum].xml, overwrite, yes.
=============================
P.S. To use a different font size userbar different from 8, you need to use a different font.
-----------------------------
Module for vBAdvanced CMPS
-----------------------------
Admin-panel - add/load module -> and select the file
do_not_upload/vba-cmps/vbgamez.xml
-----------------------------
If you installed vBGamEz before you installed the vBCMS (CMS),
then import the product product-vbg_vbcms_widget_enabler.xml from a folder do_not_upload/add-widget-to-vbcms through the Admin-panel..
-----------------------------
-----------------------------
Possible errors
-----------------------------
If after installation of the product doesn't work for you "fully customizable frame", then remove it, and then import the xml-file frame packages\vbgamez\framedefaultdata\vbgamez-frame-1.xml
-----------------------------
-----------------------------

If you have installed the vBSEO, it is in the file .htaccess find:

RewriteCond %{REQUEST_URI} !(admincp/|modcp/|cron|vbseo_sitemap)

and add the following:

# vBGamEz Rewrite start
RewriteCond %{REQUEST_URI} !(vbgamez.php/)
# vBGamEz Rewrite end

Also in the Admin-Panel vBSeo in options Exclude Pages enter vbgamez.php 
