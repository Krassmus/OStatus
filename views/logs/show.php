<table class="default">
    <thead>
        <tr>
            <th><?= _("Beschreibung") ?></th>
            <th><?= _("Stud.IP-NutzerIn") ?></th>
            <th><?= _("Externe NutzerIn") ?></th>
            <th><?= _("Zeitpunkt") ?></th>
        </tr>
    </thead>
<? foreach ($log->entries() as $entry) : ?>
    <tr>
        <td><?= htmlReady($entry['description']) ?></td>
        <td><?= $entry['user_id'] ? htmlReady(get_fullname($entry['user_id'])) : "---" ?></td>
        <td><?= $entry['contact_id'] ? htmlReady(OstatusContact::find($entry['user_id'])->name) : "---" ?></td>
        <td><?= date("G:i:s j.n.Y", $entry['mkdate']) ?></td>
    </tr>
<? endforeach ?>
</table>
