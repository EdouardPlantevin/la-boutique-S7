<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{

    #Templates path > src/Mail/xxx.html
    const REGISTER_SUCCESS = 'welcome.html';
    const FORGOT_PASSWORD = 'password.html';

    public function send(string $to, string $fullname, string $subject, string $template, array|null $vars = null): bool
    {

        try {
            $content = file_get_contents(dirname(__DIR__) . '/Mail/' . $template);
        } catch (\Exception $e) {
            throw new \Exception('Mail template not found: ' . $template);
        }

        if ($vars) {
            foreach ($vars as $key => $value) {
                $content = str_replace('{' . $key . '}', $value, $content);
            }
        }

        $mj = new Client($_ENV['MAILJET_APIKEY_PUBLIC'], $_ENV['MAILJET_APIKEY_SECRET'], true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $_ENV['MAILJET_FROM_ADDRESS'],
                        'Name' => "La Boutique"
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => $fullname
                        ]
                    ],
                    'TemplateID' => 7016591,
                    'TemplateLanguage' => true,
                    'Variables' => [
                        "content" => $content,
                    ],
                    'Subject' => $subject,
                ]
            ]
        ];

        $response = $mj->post(Resources::$Email, ['body' => $body]);

        return $response->success();
    }


}