<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
    <hm:Host xmlns:hm="http://host-meta.net/xrd/1.0"><?= $GLOBALS['UNI_NAME_CLEAN'] ?></hm:Host>
    <Link rel="lrdd" template="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>plugins.php/ostatus/webfinger/profile?resource={uri}">
        <Title>Resource Descriptor</Title>
    </Link>
</XRD>