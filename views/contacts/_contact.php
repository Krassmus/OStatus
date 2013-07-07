<li id="contact_<?= $contact->getId() ?>">
    <a href="<?= URLHelper::getLink("plugins.php/Blubber/streams/profile", array('user_id' => $contact->getId(), 'extern' => 1)) ?>">
        <?= $contact->getAvatar()->getImageTag(Avatar::MEDIUM) ?>
        <br>
        <?= htmlReady($contact->getName()) ?>
        <br>
        &lt;<?= htmlReady($contact['mail_identifier']) ?>&gt;
    </a>
</li>