<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

?>

<style>
    ul#ostatus_contacts {
        list-style: none;
        padding: 0px;
    }
    ul#ostatus_contacts > li {
        display: inline-block;
        min-width: 130px;
        min-height: 130px;
        max-width: 130px;
        max-height: 130px;
        padding: 7px;
        border: 3px #888888 solid;
        border-radius: 9px;
        overflow: hidden;
        text-align: center;
        background-color: #f3f3f3;
    }
    ul#ostatus_contacts > li:hover {
        border-color: black;
    }
</style>

<? if (count($contacts)) : ?>
<ul id="ostatus_contacts">
    <? foreach ((array) $contacts as $contact) : ?>
    <?= $this->render_partial("contacts/_contact.php", compact('contact')) ?>
    <? endforeach ?>
</ul>
<? else : ?>
<? endif ?>

<a href="" onClick="STUDIP.Ostatus.add_contact_window(); return false">
    <?= Assets::img("icons/16/blue/add", array('class' => "text-bottom")) ?> <?= _("Kontakt hinzufügen") ?>
</a>

<div id="add_contact_window_title" style="display: none;"><?= _("Kontakt hinzufügen") ?></div>
<div id="add_contact_window" style="display: none;">
    <input type="text" style="width: 200px;" id="contact_id" placeholder="<?= _("Webfinger-ID: blog@blubber.it") ?>" aria-label="<?= _("Webfinger-ID: blubb@blubber.it") ?>">
    <a href="" onClick="STUDIP.Ostatus.add_contact(); return false;">
        <?= Studip\Button::create("folgen") ?>
    </a>
    <span id="add_contact_wait" style="display: none;"><?= Assets::img("ajax_indicator_small.gif", array("class" => "text-bottom")) ?></span>
</div>

<script>
STUDIP.Ostatus = {
    add_contact_window: function () {
        jQuery('#add_contact_window').dialog({
            'modal': true,
            'title': jQuery("#add_contact_window_title").text(),
            'show': "fade",
            'hide': "fade"
        });
    },
    add_contact: function () {
        jQuery('#add_contact_wait').show();
        jQuery.ajax({
            'url': STUDIP.URLHelper.getURL("plugins.php/OStatus/contacts/add"),
            'data': {
                'contact_id': jQuery("#contact_id").val()
            },
            'dataType': "json",
            success: function (json) {
                jQuery('#add_contact_wait').hide();
                jQuery("#contact_id").val("");
                if (jQuery("#contact_" + json.id).length < 1) {
                    jQuery(json.html).hide().appendTo("#ostatus_contacts").css('display', "").fadeIn();
                }
                jQuery('#add_contact_window').dialog("close");
            }
        });
    }
};
</script>

<?

$infobox = array(
    array("kategorie" => _("Informationen"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/info",
                "text" => _("Trage Freunde aus anderen Stud.IPs oder OStatus-Netzwerken wie Identi.ca, friendica oder status.net ein.")
            ),
            array(
                "icon" => $assets_url.($GLOBALS['auth']->auth['devicePixelRatio'] > 1.2 ? "/ostatus_32_black.png" : "/ostatus_16_black.png"),
                "text" => _("OStatus ist ein offenes Protokoll mit dem sich verschiedene soziale Netzwerke miteinander verknüpfen können. So kannst Du mit Leuten blubbern, die in einem fremden Netzwerk sind, es fühlt sich aber an, als wären sie ganz nah.")
            )
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/social_networks_luc_legay_cc_by_sa.jpg",
    'content' => $infobox
);
