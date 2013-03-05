<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
    <Subject>acct:<?= htmlReady($user['username']) ?>@<?= htmlReady($_SERVER['SERVER_NAME']) ?></Subject>
    <Alias><?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?></Alias>
    <Link rel="http://webfinger.net/rel/profile-page" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?>"></Link>
    <Link rel="http://gmpg.org/xfn/11" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'].URLHelper::getLink("about.php", array('username' => $user['username'])) ?>"></Link>
<? /*    <Link rel="describedby" type="application/rdf+xml" href="http://identi.ca/krassmus/foaf"></Link> */ ?>
    <Link rel="http://apinamespace.org/atom" type="application/atomsvc+xml" href="http://identi.ca/api/statusnet/app/service/krassmus.xml"><Property type="http://apinamespace.org/atom/username"><?= htmlReady($user['username']) ?></Property></Link>
    <Link rel="http://schemas.google.com/g/2010#updates-from" href="http://identi.ca/api/statuses/user_timeline/1046034.atom" type="application/atom+xml"></Link>
    <Link rel="salmon" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>/plugins.php/ostatus/salmon/user/<?= $user->getId() ?>"></Link>
    <Link rel="http://salmon-protocol.org/ns/salmon-replies" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/replies/<?= $user->getId() ?>"></Link>
    <Link rel="http://salmon-protocol.org/ns/salmon-mention" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/mention/<?= $user->getId() ?>"></Link>
<? /*    <Link rel="magic-public-key" href="data:application/magic-public-key,RSA.8zK369nRrd2grj5BO3izZt9AsHZvOu4oouLPed-jgjC1LfTMg210jK3vf7t3ZjdAhRmF7sgnhvas-4SNSta-8S84w4xDuHpqutNEBNhirFFEBbGD-y0l1eyvPaFwG9-7H5nVT9FeV9dcaBUo6v4bV7kkj_3x5J85yZROjYVKdas=.AQAB"></Link> */ ?>
    <Link rel="magic-public-key" href="data:application/magic-public-key,RSA.<?= MagicSignature::base64_url_encode($keys->getPublicRSA->modulus->toBytes(true)) ?>.<?= MagicSignature::base64_url_encode($keys->getPublicRSA->exponent->toBytes(true)) ?>"></Link>
    <Link rel="http://ostatus.org/schema/1.0/subscribe" template="http://identi.ca/main/ostatussub?profile={uri}"></Link>
    <Link rel="http://specs.openid.net/auth/2.0/provider" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?>"></Link>
</XRD>