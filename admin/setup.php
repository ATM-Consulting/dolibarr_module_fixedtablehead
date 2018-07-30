<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018 admin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    fixedtablehead/admin/setup.php
 * \ingroup fixedtablehead
 * \brief   FixedTableHead setup page.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/fixedtablehead.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "fixedtablehead@fixedtablehead"));

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$arrayofparameters=array('FIXEDTABLEHEAD_MYPARAM1'=>array('css'=>'minwidth200'), 'FIXEDTABLEHEAD_MYPARAM2'=>array('css'=>'minwidth500'));


/*
 * Actions
 */

if (preg_match('/set_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
    {
        header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if (preg_match('/del_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_del_const($db, $code, 0) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}


/*
 * View
 */

$page_name = "FixedTableHeadSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_fixedtablehead@fixedtablehead');

// Configuration header
$head = fixedtableheadAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "fixedtablehead@fixedtablehead");



// Setup page goes here
$form=new Form($db);
$var=false;
print '<table class="noborder" width="100%">';


_print_title("Parameters");

// Example with a yes / no select
_print_on_off('FIXEDTABLEHEAD_THEME_USE_FIXED_TOPBAR');

// Example with imput
//_print_input_form_part('CONSTNAME', 'ParamLabel');

// Example with color
//_print_input_form_part('CONSTNAME', 'ParamLabel', 'ParamDesc', array('type'=>'color'),'input','ParamHelp');

// Example with placeholder
//_print_input_form_part('CONSTNAME','ParamLabel','ParamDesc',array('placeholder'=>'http://'),'input','ParamHelp');

// Example with textarea
//_print_input_form_part('CONSTNAME','ParamLabel','ParamDesc',array(),'textarea');


print '</table>';



// Page end
dol_fiche_end();

llxFooter();
$db->close();


function _print_title($title="")
{
    global $langs;
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans($title).'</td>'."\n";
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" ></td>'."\n";
    print '</tr>';
}

function _print_on_off($confkey, $title = false, $desc ='')
{
    global $var, $bc, $langs, $conf;
    $var=!$var;
    
    print '<tr '.$bc[$var].'>';
    print '<td>'.($title?$title:$langs->trans($confkey));
    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" width="300">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    print ajax_constantonoff($confkey);
    print '</form>';
    print '</td></tr>';
}

function _print_input_form_part($confkey, $title = false, $desc ='', $metas = array(), $type='input', $help = false)
{
    global $var, $bc, $langs, $conf, $db;
    $var=!$var;
    
    $form=new Form($db);
    
    $defaultMetas = array(
        'name' => $confkey
    );
    
    if($type!='textarea'){
        $defaultMetas['type']   = 'text';
        $defaultMetas['value']  = $conf->global->{$confkey};
    }
    
    
    $metas = array_merge ($defaultMetas, $metas);
    $metascompil = '';
    foreach ($metas as $key => $values)
    {
        $metascompil .= ' '.$key.'="'.$values.'" ';
    }
    
    print '<tr '.$bc[$var].'>';
    print '<td>';
    
    if(!empty($help)){
        print $form->textwithtooltip( ($title?$title:$langs->trans($confkey)) , $langs->trans($help),2,1,img_help(1,''));
    }
    else {
        print $title?$title:$langs->trans($confkey);
    }
    
    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="right" width="300">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    if($type=='textarea'){
        print '<textarea '.$metascompil.'  >'.dol_htmlentities($conf->global->{$confkey}).'</textarea>';
    }
    else {
        print '<input '.$metascompil.'  />';
    }
    
    print '<input type="submit" class="butAction" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';
}
