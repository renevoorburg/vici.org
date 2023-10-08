<?php declare(strict_types=1);

use ExtIds\Normalizers\Mithraeum;
use PHPUnit\Framework\TestCase;

class MithraeumTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new Mithraeum();
//        $this->assertTrue($object->isValidURL('https://www.mithraeum.eu/monument/685'));
//        $this->assertTrue($object->isValidURL('https://www.mithraeum.eu/person/1153'));
//        $this->assertTrue($object->isValidURL('https://www.mithraeum.eu/book/123'));
//        $this->assertTrue($object->isValidURL('http://mithraeum.eu/monument/685'));
//        $this->assertTrue($object->isValidURL('http://mithraeum.eu/person/1153'));
//        $this->assertTrue($object->isValidURL('http://mithraeum.eu/book/123'));
//
//        $this->assertFalse($object->isValidURL('https://www.mithraeum.eu/monument/68x5'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new Mithraeum();
//        $this->assertTrue($object->isValidTag('mithraeum:monument=685'));
//        $this->assertTrue($object->isValidTag('mithraeum:book=685'));
//        $this->assertTrue($object->isValidTag('mithraeum:person=685'));
//        $this->assertTrue($object->isValidTag('mithraeum:monument = 685'));
//
//        $this->assertFalse($object->isValidTag('mithraeum:monument='));
//        $this->assertFalse($object->isValidTag('mithraeum:monument=5a'));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new Mithraeum();
//        $this->assertTrue($object->isValidId('monument=685'));
//        $this->assertTrue($object->isValidId('person=685'));
//        $this->assertTrue($object->isValidId('book=685'));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new Mithraeum();
//        $this->assertEquals(
//            'mithraeum:monument=685',
//            $object->urlToTag('https://www.mithraeum.eu/monument/685')
//        );
//        $this->assertEquals(
//            'mithraeum:person=685',
//            $object->urlToTag('http://mithraeum.eu/person/685')
//        );
//        $this->assertEquals(
//            'mithraeum:book=600',
//            $object->urlToTag('https://www.mithraeum.eu/book/600')
//        );
//        $this->assertEquals(
//            '',
//            $object->urlToTag('https://www.mithraeum.eu/person/x685')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $object = new Mithraeum();
        $object->setUrlByTagOrUrl('mithraeum:person=685');
        $this->assertEquals('https://www.mithraeum.eu/person/685', $object->getUrl());

        $object->setUrlByTagOrUrl('mithraeum:monument = 685');
        $this->assertEquals('https://www.mithraeum.eu/monument/685', $object->getUrl());

        $object->setUrlByTagOrUrl('mithraeum:person=x685');
        $this->assertEquals('https://www.mithraeum.eu/monument/685', $object->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $object = new Mithraeum();

        $object->setUrlById('monument=98956');
        $this->assertEquals('https://www.mithraeum.eu/monument/98956', $object->getUrl());

        $object->setUrlById('jhsdjhsd');
        $this->assertEquals('https://www.mithraeum.eu/monument/98956', $object->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $object = new Mithraeum();

        $object->setUrlByTagOrUrl('http://mithraeum.eu/monument/685');
        $this->assertEquals('https://www.mithraeum.eu/monument/685', $object->getUrl());

        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/684');
        $this->assertEquals('https://www.mithraeum.eu/person/684', $object->getUrl());

        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/687');
        $this->assertEquals('https://www.mithraeum.eu/person/687', $object->getUrl());
    }

    public function testGetTagProvidingUrl() : void
    {
        $object = new Mithraeum();

        $object->setUrlByTagOrUrl('http://mithraeum.eu/monument/685');
        $this->assertEquals('mithraeum:monument=685', $object->getTag());

        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/684');
        $this->assertEquals('mithraeum:person=684', $object->getTag());

        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/687');
        $this->assertEquals('mithraeum:person=687', $object->getTag());
    }

    public function testGetid() : void
    {
        $object = new Mithraeum();
        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/685');
        $this->assertEquals('person=685', $object->getId());
        $object = new Mithraeum();
        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/monument/686');
        $this->assertEquals('monument=686', $object->getId());
    }

}