<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendDomainAlert
{
    /**
     * Send unauthorized domain alert to developer inbox using Brevo API.
     */
    public static function sendAlert(string $domain, string $extraMessage = null): bool
    {
        $apiKey = env('SENDINBLUE_API_KEY');

        // Validate API key
        if (empty($apiKey)) {
            Log::error('Brevo API key is missing or empty');
            return false;
        }

        $fromEmail = "mdtanvirulislam510@gmail.com";
        $fromName = "Domain Alert";
        $toEmail = "tanvirulislam469@gmail.com";

        $time = now()->toDateTimeString();

        $body = "ALERT: Unauthorized domain detected\n\n";
        $body .= "Domain: {$domain}\n";
        $body .= "Time: {$time}\n";

        if ($extraMessage) {
            $body .= "\nAdditional Info:\n{$extraMessage}\n";
        }

        $payload = [
            "sender" => [
                "email" => $fromEmail,
                "name" => $fromName
            ],
            "to" => [
                ["email" => $toEmail]
            ],
            "subject" => "Unauthorized Domain Access: {$domain}",
            "textContent" => $body
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                "api-key" => $apiKey,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post("https://api.brevo.com/v3/smtp/email", $payload);

            if ($response->successful()) {
                Log::warning("Domain alert sent successfully", [
                    "domain" => $domain,
                    "ip" => request()->ip()
                ]);
                return true;
            } else {
                Log::error("Brevo API failed", [
                    "status" => $response->status(),
                    "error" => $response->body(),
                    "domain" => $domain
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Brevo API Exception", [
                "domain" => $domain,
                "message" => $e->getMessage()
            ]);
            return false;
        }
    }
}
