<?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:activity="http://activitystrea.ms/spec/1.0/">
  <id><?= $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".$user['username'] ?></id>
  <title><?= htmlspecialchars($user->getName()) ?> is now following <?= htmlspecialchars($whiterabbit->getName()) ?></title>
  <author>
    <uri>acct:<?= htmlspecialchars($user['username']) ?>@<?= $_SERVER['SERVER_NAME'] ?></uri>
    <name><?= htmlspecialchars($user->getName())?></name>
    <link rel="photo" type="image/png" href="<?= htmlspecialchars($user->getAvatar()->getURL(Avatar::NORMAL)) ?>"/>
    <link rel="alternate" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".$user['username'] ?>"/>
  </author>
  <activity:actor>
    <activity:object-type>http://activitystrea.ms/schema/1.0/person</activity:object-type>
    <id><?= $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".$user['username'] ?></id>
    <title><?= $user->getName() ?></title>
    <link rel="alternate" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".$user['username'] ?>"/>
    <link rel="avatar" type="image/png" href="<?= htmlspecialchars($user->getAvatar()->getURL(Avatar::NORMAL)) ?>"/>
    <link rel="photo" type="image/png" href="<?= htmlspecialchars($user->getAvatar()->getURL(Avatar::NORMAL)) ?>"/>
  </activity:actor>
  <activity:verb>http://activitystrea.ms/schema/1.0/follow</activity:verb>
  <activity:object>
    <activity:object-type>http://activitystrea.ms/schema/1.0/person</activity:object-type>
    <title><?= $whiterabbit->getName() ?></title>
    <id><?= htmlspecialchars($whiterabbit['data']['id']) ?></id>
    <link rel="alternate" type="text/html" href="<?= htmlspecialchars($whiterabbit['data']['alias']) ?>"/>
  </activity:object>
</entry>
