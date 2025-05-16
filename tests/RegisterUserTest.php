<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');

        $client->submitForm('Inscription', [
            'register_user_type_form[email]' => 'john@doe.com',
            'register_user_type_form[plainPassword][first]' => 'password',
            'register_user_type_form[plainPassword][second]' => 'password',
            'register_user_type_form[firstname]' => 'John',
            'register_user_type_form[lastname]' => 'Doe',
        ]);

        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();

        $this->assertSelectorExists('div:contains("Merci pour votre inscription")');
    }
}
