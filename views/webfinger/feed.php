<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<feed xml:lang="en-US" xmlns="http://www.w3.org/2005/Atom" 
            xmlns:thr="http://purl.org/syndication/thread/1.0" 
            xmlns:activity="http://activitystrea.ms/spec/1.0/" 
            xmlns:media="http://purl.org/syndication/atommedia" 
            xmlns:poco="http://portablecontacts.net/spec/1.0" 
            xmlns:ostatus="http://ostatus.org/schema/1.0" 
            xmlns:statusnet="http://status.net/schema/api/1/">
  <generator uri="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>" version="<?= $GLOBALS['SOFTWARE_VERSION'] ?>"><?= studip_utf8encode(htmlReady($GLOBALS['UNI_NAME_CLEAN'])) ?></generator>
  <id><?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/webfinger/feed/".$user['username']) ?></id>
  <title><?= studip_utf8encode(htmlReady($user->getName())) ?> timeline</title>
  <subtitle>Updates from <?= studip_utf8encode(htmlReady($user->getName())) ?> on <?= studip_utf8encode(htmlReady($GLOBALS['UNI_NAME_CLEAN'])) ?>!</subtitle>
  <logo><?= $user->getAvatar()->getURL(AVATAR::NORMAL) ?></logo>
  <updated>2010-08-22T13:26:16+00:00</updated>
  <author>
    <name><?= studip_utf8encode(htmlReady($user->getName())) ?></name>
    <uri><?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?></uri>
  </author>
  <link href="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/profile?user_id=".$user['user_id']) ?>" rel="alternate" type="text/html"/>
  <link href="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/hub/register") ?>" rel="hub"/>
  <link href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint" rel="salmon"/>
  <link href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint" rel="http://salmon-protocol.org/ns/salmon-replies"/>
  <link href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint" rel="http://salmon-protocol.org/ns/salmon-mention"/>
  <link href="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/webfinger/feed/".$user['username']) ?>" rel="self" type="application/atom+xml"/>
  <? foreach ($blubber as $blubb) : ?>
  <entry>
    <title><?= studip_utf8encode(htmlReady($blubb['name'])) ?></title>
    <link rel="alternate" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"/>
    <id><?= $blubb['root_id'] === $blubb['topic_id'] ? $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] : $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/comment/".$blubb->getId() ?></id>
    <published><?= date("c", $blubb['mkdate']) ?></published>
    <updated><?= date("c", $blubb['chdate']) ?></updated>
    <content type="markdown"><?= studip_utf8encode(htmlReady($blubb['description'])) ?></content>
    <content type="html"><?= studip_utf8encode(htmlReady(formatReady($blubb['description']))) ?></content>
    <activity:verb>http://activitystrea.ms/schema/1.0/post</activity:verb>
    <activity:object-type><?= $blubb['root_id'] === $blubb['topic_id'] ? "http://activitystrea.ms/schema/1.0/note" : "http://activitystrea.ms/schema/1.0/comment" ?></activity:object-type>
    <statusnet:notice_info local_id="<?= $blubb->getId() ?>" source="web"></statusnet:notice_info>
    <? if ($blubb['root_id'] !== $blubb['topic_id']) : ?>
    <thr:in-reply-to href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>" ref="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"></thr:in-reply-to>
    <? endif ?>
    <link rel="ostatus:conversation" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubb['root_id'] ?>"/>
  </entry>
  <? endforeach ?>
</feed>