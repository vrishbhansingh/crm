<?php

return [

    /*
    | Master switch. Keep FALSE during local development so the app works
    | normally. Set LICENSE_ENABLED=true in .env on the client's server only
    | after you've deployed the license server and issued a key.
    */
    'enabled' => env('LICENSE_ENABLED', false),

    // Your license server endpoint (deployed on Vercel), e.g.
    // https://your-license-server.vercel.app/api/verify
    'server_url' => env('LICENSE_SERVER_URL', ''),

    // The license key you issued for this installation.
    'key' => env('LICENSE_KEY', ''),

    // Hosts that always pass (local development / staging).
    // Set LICENSE_BYPASS_LOCAL=false in .env to TEST the license flow on localhost.
    'bypass_hosts' => env('LICENSE_BYPASS_LOCAL', true)
        ? ['localhost', '127.0.0.1', '::1']
        : [],

    // How often to re-check with the server (seconds). Default 6 hours.
    'recheck_seconds' => (int) env('LICENSE_RECHECK_SECONDS', 21600),

    // If the server is unreachable, keep working this long since the last
    // successful check (seconds). Default 3 days. After this, it locks.
    'grace_seconds' => (int) env('LICENSE_GRACE_SECONDS', 259200),

    // HTTP timeout when calling the license server (seconds).
    'timeout' => (int) env('LICENSE_TIMEOUT', 6),

    /*
    | Public key — used to VERIFY the server's signed verdict. The matching
    | PRIVATE key lives ONLY on your license server (Vercel env var). Because
    | the verdict is signed, a copied installation cannot forge a "valid"
    | response without your private key. Do not change unless you regenerate
    | the key pair (and update the server's private key to match).
    */
    'public_key' => <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA2SUkizBY0O0UpNT7Mcim
4OdGBV1ymEUsxj2IfUEuaUKXBYgvWiEFMpokw8KIFQNLagWURGqmxP0MY6waBECO
39Z44ZVgSfcgwlghluvFxYJkeOk8IyBkveezLIWfCWie9NN3n5Ice1Sah0l6FVHU
zwRKu1hNtUfXvP6YohDAigTsgYLMrCwCQri3U8KQl8QlHOQq+4ORuhrQbZ6J4LsO
5HdV3L6ea0TU5wpRMBD/YeatVq8gbxa7KCpPPsVaiRtuddsvZ2e0c/UiDxhIklVF
e4MUsF+wICLfMCLEmcKLVHP4k1pDhJljM3RLlKGfg6EKjrDTS9t4WebcSGMbNEPi
NQIDAQAB
-----END PUBLIC KEY-----
PEM,
];
