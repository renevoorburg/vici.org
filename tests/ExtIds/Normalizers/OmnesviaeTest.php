<?php declare(strict_types=1);

use ExtIds\Normalizers\OmnesViae;
use PHPUnit\Framework\TestCase;

class OmnesviaeTest extends TestCase
{
//    public function testIsValidUrl(): void
//    {
//        $object = new OmnesViae();
//        $this->assertTrue($object->isValidURL('https://omnesviae.org/#TPPlace553'));
//        $this->assertTrue($object->isValidURL('https://omnesviae.org/#OVPlace553'));
//        $this->assertTrue($object->isValidURL('http://www.omnesviae.org/#TPPlace553'));
//        $this->assertTrue($object->isValidURL('http://www.omnesviae.org/#OVPlace553'));
//
//        $this->assertFalse($object->isValidURL('http://www.omnesviae.org/'));
//        $this->assertFalse($object->isValidURL('http://www.omnesviae.org/#blabal'));
//    }
//
//    public function testIsValidTag(): void
//    {
//        $object = new OmnesViae();
//        $this->assertTrue($object->isValidTag('omnesviae:tpplace=990'));
//        $this->assertTrue($object->isValidTag('omnesviae:place=TPPlace90'));
//        $this->assertTrue($object->isValidTag('omnesviae:id=TPPlace90'));
//        $this->assertTrue($object->isValidTag('tp:place=785'));
//        $this->assertTrue($object->isValidTag('OVPlace76'));
//    }
//
//    public function testIsValidId(): void
//    {
//        $object = new OmnesViae();
//        $this->assertTrue($object->isValidId('TPPlace98956'));
//        $this->assertTrue($object->isValidId('OVPlace98956'));
//        $this->assertFalse($object->isValidId('tpplace98956'));
//        $this->assertFalse($object->isValidId('ovplace98956'));
//        $this->assertFalse($object->isValidId(''));
//    }
//
//    public function testUrlToTag() : void
//    {
//        $object = new OmnesViae();
//        $this->assertEquals(
//            'omnesviae:id=TPPlace553',
//            $object->urlToTag('https://omnesviae.org/#TPPlace553')
//        );
//        $this->assertEquals(
//            'omnesviae:id=TPPlace553',
//            $object->urlToTag('http://omnesviae.org/#TPPlace553')
//        );
//        $this->assertEquals(
//            'omnesviae:id=OVPlace553',
//            $object->urlToTag('http://omnesviae.org/#OVPlace553')
//        );
//        $this->assertEquals(
//            '',
//            $object->urlToTag('https://www.mithraeum.eu/person/x685')
//        );
//    }

    public function testGetUrlProvidingTag() : void
    {
        $object = new Omnesviae();
        $object->setUrlByTagOrUrl('omnesviae:place=TPPlace123');
        $this->assertEquals('https://omnesviae.org/#TPPlace123', $object->getUrl());

        $object->setUrlByTagOrUrl('omnesviae:tpplace=990');
        $this->assertEquals('https://omnesviae.org/#TPPlace990', $object->getUrl());

        $object->setUrlByTagOrUrl('omnesviae:id=TPPlace90');
        $this->assertEquals('https://omnesviae.org/#TPPlace90', $object->getUrl());

        $object->setUrlByTagOrUrl('omnesviae:id=OVPlace90');
        $this->assertEquals('https://omnesviae.org/#OVPlace90', $object->getUrl());

        $object->setUrlByTagOrUrl('OVPlace90');
        $this->assertEquals('https://omnesviae.org/#OVPlace90', $object->getUrl());
    }

    public function testGetUrlProvidingId() : void
    {
        $object = new Omnesviae();
        $object->setUrlById('OVPlace9856');
        $this->assertEquals('https://omnesviae.org/#OVPlace9856', $object->getUrl());

        $object->setUrlById('TPPlace9857');
        $this->assertEquals('https://omnesviae.org/#TPPlace9857', $object->getUrl());

        $object->setUrlById('jhsdjhsd');
        $this->assertEquals('https://omnesviae.org/#TPPlace9857', $object->getUrl());
    }

    public function testGetUrlProvidingUrl() : void
    {
        $object = new Omnesviae();
        $object->setUrlByTagOrUrl('http://www.omnesviae.org/#OVPlace9856');
        $this->assertEquals('https://omnesviae.org/#OVPlace9856', $object->getUrl());

        $object->setUrlByTagOrUrl('https://omnesviae.org/#TPPlace9857');
        $this->assertEquals('https://omnesviae.org/#TPPlace9857', $object->getUrl());

        $object->setUrlByTagOrUrl('https://www.mithraeum.eu/person/685');
        $this->assertEquals('https://omnesviae.org/#TPPlace9857', $object->getUrl());
    }

    public function testGetid() : void
    {
        $object = new Omnesviae();
        $object->setUrlByTagOrUrl('https://omnesviae.org/#TPPlace9856');
        $this->assertEquals('TPPlace9856', $object->getId());

        $object->setUrlByTagOrUrl('https://www.omnesviae.org/#OVPlace9857');
        $this->assertEquals('OVPlace9857', $object->getId());
    }

}