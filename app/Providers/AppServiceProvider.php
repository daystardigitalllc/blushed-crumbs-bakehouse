<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Brevo over HTTPS (port 443) instead of SMTP (25/465/587) — this
        // server's cloud provider blocks outbound SMTP ports by default, so
        // the "smtp" mailer just hangs and times out regardless of how
        // correct the credentials are. Confirmed via a raw TCP connect test
        // from the server: port 443 to smtp-relay.brevo.com connects fine,
        // ports 25/465/587 all fail. See config/mail.php's "brevo_api" entry.
        Mail::extend('brevo_api', function () {
            $transport = new Transport([new BrevoTransportFactory()]);

            return $transport->fromString('brevo+api://' . config('services.brevo.key') . '@default');
        });
    }
}
