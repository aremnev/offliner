<?php

namespace Thumbtack\OfflinerBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RootControllerTest extends WebTestCase {
    public function testIndex() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("CV maker")')->count() > 0);
        $this->assertTrue($client->getResponse()->isSuccessful());

        $client->request('GET', '/login/test/test');

        $crawler = $client->request('GET', '/cvs');
        $this->assertTrue($crawler->filter('html:contains("Hello")')->count() > 0); //login success

        $client->request('GET', '/aaaa');
        $this->assertTrue($client->getResponse()->isNotFound());
    }
}
