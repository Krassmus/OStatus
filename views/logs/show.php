<table class="default">
    <thead>
        <tr>
            <th><?= _("Beschreibung") ?></th>
            <th><?= _("Stud.IP-NutzerIn") ?></th>
            <th><?= _("Externe NutzerIn") ?></th>
            <th><?= _("Daten") ?></th>
            <th><?= _("Zeitpunkt") ?></th>
        </tr>
    </thead>
<? foreach ($log->entries() as $entry) : ?>
    <tr>
        <td><?= htmlReady($entry['description']) ?></td>
        <td><?= $entry['user_id'] ? htmlReady(get_fullname($entry['user_id'])) : "---" ?></td>
        <td><?= $entry['contact_id'] ? htmlReady(OstatusContact::find($entry['contact_id'])->name) : "---" ?></td>
        <td><?= $entry['data'] ? nl2br(htmlReady($entry['data'])) : "---" ?></td>
        <td><?= date("G:i:s j.n.Y", $entry['mkdate']) ?></td>
    </tr>
<? endforeach ?>
</table>
