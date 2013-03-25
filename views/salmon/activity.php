<?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<entry>
    <title><?= studip_utf8encode(htmlspecialchars($activity->title)) ?></title>
    <link rel="alternate" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"/>
    <id><?= studip_utf8encode(htmlspecialchars($activity->title)) ?></id>
    <published><?= date("c", $activity->published) ?></published>
    <updated><?= date("c", $activity->updated) ?></updated>
    <activity:actor>
        <id><?= htmlspecialchars($activity->actor['id']) ?></id>
        <uri></uri>
        <activity:object-type><?= htmlspecialchars($activity->actor['objectType']) ?></activity:object-type>
    </activity:actor>
    <activity:verb><?= htmlspecialchars($activity->verb) ?></activity:verb>
    <activity:object>
        <id><?= htmlspecialchars($activity->object['id']) ?></id>
        <title><?= htmlspecialchars($activity->object['title']) ?></title>
        <activity:object-type><?= htmlspecialchars($activity->object['objectType']) ?></activity:object-type>
        <? if ($blubb['root_id'] !== $blubb['topic_id']) : ?>
        <thr:in-reply-to href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>" ref="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"></thr:in-reply-to>
        <? endif ?>
        <link rel="ostatus:conversation" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"/>
        <content type="markdown"><?= studip_utf8encode(htmlspecialchars($blubb['description'])) ?></content>
        <content type="html"><?= studip_utf8encode(htmlspecialchars(formatReady($blubb['description']))) ?></content>
    </activity:object>
</entry>