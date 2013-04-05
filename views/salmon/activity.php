<?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<entry>
    <title><?= studip_utf8encode(htmlspecialchars($activity->title)) ?></title>
    <? foreach ($activity->links as $rel => $link) : ?>
    <? if (isset($link['href'])) : ?>
    <link rel="<?= htmlspecialchars($rel) ?>"<? foreach ($link as $attr => $value) { echo " ".htmlspecialchars($attr)."=\"".htmlspecialchars($value)."\""; } ?>/>
    <? endif ?>
    <? endforeach ?>
    <id><?= htmlspecialchars($activity->author['id'].";".$activity->verb.";".$activity->object['id']) ?></id>
    <published><?= date("c", $activity->published) ?></published>
    <updated><?= date("c", $activity->updated) ?></updated>
    <author>
        <id><?= htmlspecialchars($activity->author['id']) ?></id>
        <? if ($activity->author['uri']) : ?>
        <uri><?= htmlspecialchars($activity->author['uri']) ?></uri>
        <? endif ?>
        <? foreach ($activity->author['links'] as $rel => $link) : ?>
        <? if (isset($link['href'])) : ?>
        <link rel="<?= htmlspecialchars($rel) ?>"<? foreach ($link as $attr => $value) { echo " ".htmlspecialchars($attr)."=\"".htmlspecialchars($value)."\""; } ?>/>
        <? endif ?>
        <? endforeach ?>
    </author>
    <activity:actor>
        <id><?= htmlspecialchars($activity->actor['id']) ?></id>
        <? if ($activity->actor['url']) : ?>
        <uri><?= htmlspecialchars($activity->actor['url']) ?></uri>
        <? endif ?>
        <activity:object-type><?= htmlspecialchars($activity->actor['objectType']) ?></activity:object-type>
    </activity:actor>
    <activity:verb><?= htmlspecialchars($activity->verb) ?></activity:verb>
    <activity:object>
        <id><?= htmlspecialchars($activity->object['id']) ?></id>
        <title><?= htmlspecialchars($activity->object['title']) ?></title>
        <activity:object-type><?= htmlspecialchars($activity->object['objectType']) ?></activity:object-type>
        <link rel="ostatus:conversation" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"/>
        <content type="markdown"><?= htmlspecialchars($activity->object['content']) ?></content>
        <content type="html"><?= htmlspecialchars(formatReady($activity->object['content'])) ?></content>
    </activity:object>
    <? if ($activity->reply_to) : ?>
    <thr:in-reply-to href="<?= htmlspecialchars($activity->reply_to) ?>" />
    <? endif ?>
    <content><?= htmlspecialchars($activity->content) ?></content>
</entry>