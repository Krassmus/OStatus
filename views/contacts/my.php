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

<table>
    <thead>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Adresse") ?></th>
        </tr>
    </thead>
    <tbody>
        <? if (count($contacts)) : ?>
        <? foreach ((array) $contacts as $contact) : ?>
        <tr>
            <td><a href="<?= URLHelper::getLink("plugins.php/Blubber/forum/profile", array('user_id' => $contact->getId(), 'extern' => 1)) ?>"><?= htmlReady($contact['name']) ?></a></td>
            <td><?= htmlReady($contact['mail_identifier']) ?></td>
        </tr>
        <? endforeach ?>
        <? else : ?>
        <tr>
            <td colspan="2"><?= _("Bisher haben Sie keine OStatus-Kontakte") ?></td>
        </tr>
        <? endif ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">
                <a href="" onClick="STUDIP.Ostatus.add_contact_window(); return false">
                <?= Assets::img("icons/16/blue/plus") ?>
                </a>
            </td>
        </tr>
    </tfoot>
</table>

<div id="add_contact_window_title" style="display: none;"><?= _("Kontakt hinzufügen") ?></div>
<div id="add_contact_window" style="display: none;">
    <input type="text" id="contact_id">
    <a href="" onClick="STUDIP.Ostatus.add_contact(); return false;">
        <?= Studip\Button::create("folgen") ?>
    </a>
</div>

<script>
STUDIP.Ostatus = {
    add_contact_window: function () {
        jQuery('#add_contact_window').dialog({
            'title': jQuery("#add_contact_window_title").text()
        });
    },
    add_contact: function () {
        jQuery.ajax({
            'url': STUDIP.URLHelper.getURL("plugins.php/OStatus/contacts/add"),
            'data': {
                'contact_id': jQuery("#contact_id").val()
            },
            success: function (output) {
                console.log(output);
            }
        });
    }
};
</script>