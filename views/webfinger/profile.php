<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
    <Subject>acct:<?= htmlReady($user['username']) ?>@<?= htmlReady($_SERVER['SERVER_NAME']) ?></Subject>
    <Alias><?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?></Alias>
    <Link rel="http://webfinger.net/rel/profile-page" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?>"></Link>
    <Link rel="http://gmpg.org/xfn/11" type="text/html" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username='.$user['username'] ?>"></Link>
    <Link rel="http://schemas.google.com/g/2010#updates-from" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/webfinger/feed/".$user['username'] ?>" type="application/atom+xml"></Link>
    <Link rel="salmon" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint"></Link>
    <Link rel="http://salmon-protocol.org/ns/salmon-replies" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint"></Link>
    <Link rel="http://salmon-protocol.org/ns/salmon-mention" href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/salmon/endpoint"></Link>
    <Link rel="magic-public-key" href="data:application/magic-public-key,RSA.<?= MagicSignature::base64_url_encode($keys->getPublicRSA()->modulus->toBytes(true)) ?>.<?= MagicSignature::base64_url_encode($keys->getPublicRSA()->exponent->toBytes(true)) ?>"></Link>
    <Link rel="http://ostatus.org/schema/1.0/subscribe" template="http://identi.ca/main/ostatussub?profile={uri}"></Link>
</XRD>