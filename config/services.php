<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'firebase' => [
        "type" => "service_account",
        "project_id" => "hm-paygate",
        "private_key_id" => "391e5d3dd70ca00964cfcd1db4443f61ba4f4595",
        "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7KXFoLHuKuWLu\nI+wYRtok1eX3VHgDb5rnYPTVDsEDh6jJ6zLNGbdYmAA0G5rxA4G8VFGqZZNi6ue/\niwg/1hFAvyQuARRiAmmHIyeHquHxhXCSkPG18xBUtkdK6EG8HZ0HDE6oqzfGk0mo\nJnyvYd4jynk/nsvj8yT+BlUpnFjj/CGCCuF5gUVtCoyrNuTIhhZ8nHSnyGnMsMSq\nUWJFRi9N7HxU5dhU5LboTy7X/JOQOBWktHujeX18MHb7/czgurR8HmX+jLUQacZl\nFcUhf62zAOhDsiZyrD+JkrxAoiQoAc3t3FqdbIuPBno2IjHeydG44XXf7VufSkEd\n1X3bcU35AgMBAAECggEACv4WTZPd669ov+uMiH580yYG/DL8sNhOD6Hnr1h5qKSV\nRXVoG0NpO9hYRrc1BKEmmQeWG6fHSMuFh1VPS48zSItmwMGfz1ksloXPjWMmzcHU\nc0n1EFvcIoWULrJGeMR/07P62Lg5reg6WW3ClqXCX3VwZUwFK029Z01llMHnN030\nFoGBvpXDBlNOUGLjUCXgghPgE/QZT07U/KwuLp4f9dS4nUMkkKXWXFEx4RMziBg0\nojRhEfA1+hsQan/5PrSc/YOwNTEL0NXxn5PrcMXuQvVcKps/91HrF2Qm72FpNdAY\ndZXGrEBg5EwgVMSDsJ4lVW97rIR37YLSd6bYuYRJEwKBgQDpbUNAiL+iQnvQaPki\n0oRIcwmDnsmboozv/+NNoar2SFpdE4b0VrGNOYmfteq8GlH8yeUhqn4KNF3mFjvf\n4NkN5jcrHFsWspz6BOx4W15qbfsxoYtiTXfoXosg8ffSNKboZboxZCl9WIE/aDb8\nsXgXZaLQas/YOSO/TGssl1C9HwKBgQDNQtdf2GRUoC1NmVeSVsNupVq1uLKMLxME\nksOkQRDIBxxOloF/TYcx614o1kon2VLM15g1j4ESALY1xC2p02LRdq1PHCZP0T2N\n21TC98O0vU4LJR9EpSEGnhVRoB1OZlOvP9Ld0B7gdHXTF6RYrCCjG8c5XvsZXVf6\ntX6OHm155wKBgFXnBdbb/FoESXhbCURNYK0g2KF9cAg8e81eyBGrqzTTT53tL77/\noHuubv09f1MWBJIY4p/PFG9A8kVYbVRodLhuvmK5HrRtquM/qJ5qUYatTsHnB1p3\n0+bJ1D2djmvnQH1J4omGqNYeGgJxobrAvMcvegllJXQXIxalUtOoI+hVAoGAIXZQ\nHjPsx08Fk6z39cdnPNaAHzfpA6l49Dno4xIoZjOijb1DhzlgXOb+BrJIjI6FTReo\nnLK0W/b5vXIp75GifntWbZstM73sxv+GpfI2WXSzEGYQ3uP8qlODLjdoD6vm4J8L\nBVY0cmsJ+kvUYxBMkcqpg9TUIqJxAVItO4YCWukCgYEAwqaPT7dPrpQuVW3eKgxI\n7FL6pE8Turj0FqCCzw38cafMpKXVdz4ltI143w9sJUxQgmHJZiMxUkpphjcYuEjy\n4+WGINoXebBBuuJAW7uOKdiTm2s0CwV67rrgM+ESmdyX45yvc8LjHcGjOuTxHlXf\nAAEL/C0KUWcbwDKSLckh74E=\n-----END PRIVATE KEY-----\n",
        "client_email" => "firebase-adminsdk-10fwg@hm-paygate.iam.gserviceaccount.com",
        "client_id" => "106236568244688075219",
        "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
        "token_uri" => "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-10fwg%40hm-paygate.iam.gserviceaccount.com",
        "universe_domain" => "googleapis.com",
        "database_url" => "https://hm-paygate-default-rtdb.asia-southeast1.firebasedatabase.app"
    ],
    'ZNS' => [
        'secret_key' => 'JKTVo1Z812L5dY0MEJ18',
        'app_id' => '102337436838909350',
        'redirect_uri' => 'https://api-ldp-test.hocmai.net/api/v1/zns/callback',
        'url_send_message'=> 'https://business.openapi.zalo.me/message/template',
    ],
    'ZNS_FPT' => [
        'EasyIELTS' => [
            'secret_key' => '7604c2c1b1a97e659449927499ec9979',
            'app_id' => '1696408610',
        ],
        'SchoolTour' => [
            'secret_key' => '539e13afe8121c766ff6166553fce8e3 ',
            'app_id' => '1696409565',
        ],
        'url_send_message' => 'https://api-fns.fpt.work/api/send-message',
    ]

];
