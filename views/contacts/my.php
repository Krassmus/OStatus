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
            <td><?= htmlReady($contact['name']) ?></td>
            <td><?= htmlReady($contact['mail_identifier']) ?></td>
        </tr>
        <? endforeach ?>
        <? else : ?>
        <tr>
            <td colspan="2"><?= _("Bisher haben Sie keine OStatus-Kontakte") ?></td>
        </tr>
        <? endif ?>
    </tbody>
</table>