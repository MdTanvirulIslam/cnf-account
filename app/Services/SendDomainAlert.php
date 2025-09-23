<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendDomainAlert
{
    /**
     * Send unauthorized domain alert to developer inbox using Brevo API.
     *
     * @param string $domain
     * @param string|null $extraMessage
     * @return bool
     */
    public static function sendAlert(string $domain, string $extraMessage = null): bool
    {
        // ---- Your Brevo API credentials ----
        $apiKey     = env('SENDINBLUE_API_KEY');
        $fromEmail  = "mdtanvirulislam510@gmail.com";  // must be a verified sender in Brevo
        $fromName   = "Domain Alert";
        $toEmail    = "tanvirulislam469@gmail.com";
        // ------------------------------------

        $time = function_exists('now') ? now()->toDateTimeString() : date('Y-m-d H:i:s');

        $body = "🚨 Unauthorized domain detected 🚨\n\n";
        $body .= "Domain: {$domain}\n";
        $body .= "Time:   {$time}\n";
        if ($extraMessage) {
            $body .= "\nInfo: {$extraMessage}\n";
        }

        $payload = [
            "sender" => [
                "email" => $fromEmail,
                "name"  => $fromName
            ],
            "to" => [
                ["email" => $toEmail]
            ],
            "subject" => "Unauthorized Domain Access: {$domain}",
            "textContent" => $body
        ];

        try {
            $response = Http::withHeaders([
                "api-key" => $apiKey,
                "Content-Type" => "application/json"
            ])->post("https://api.brevo.com/v3/smtp/email", $payload);

            if ($response->successful()) {
                Log::info("Domain Alert sent via Brevo", ["domain" => $domain]);
                return true;
            } else {
                Log::error("Domain Alert Brevo failed", [
                    "status" => $response->status(),
                    "body"   => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Domain Alert Brevo Exception: " . $e->getMessage(), [
                "domain" => $domain,
            ]);
            return false;
        }
    }
}
