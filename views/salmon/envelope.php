<?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<me:env xmlns:me="http://salmon-protocol.org/ns/magic-env">
    <me:data type="application/atom+xml"><?= htmlReady($base64data) ?></me:data>
    <me:encoding>base64url</me:encoding>
    <me:alg>RSA-SHA256</me:alg>
    <me:sig><?= htmlReady($sig) ?></me:sig>
</me:env>