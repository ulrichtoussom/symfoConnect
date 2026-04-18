<?php

namespace App\Tests\Unit;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testNotificationIsUnreadByDefault(): void
    {
        $notif = new Notification();

        $this->assertFalse($notif->isRead());
    }

    public function testNotificationTypeFollow(): void
    {
        $notif = (new Notification())->setType(Notification::TYPE_FOLLOW);

        $this->assertSame('follow', $notif->getType());
    }

    public function testNotificationTypeLike(): void
    {
        $notif = (new Notification())->setType(Notification::TYPE_LIKE);

        $this->assertSame('like', $notif->getType());
    }

    public function testCreatedAtIsSetOnInstantiation(): void
    {
        $before = new \DateTimeImmutable('-1 second');
        $notif  = new Notification();
        $after  = new \DateTimeImmutable('+1 second');

        $this->assertGreaterThan($before, $notif->getCreatedAt());
        $this->assertLessThan($after, $notif->getCreatedAt());
    }

    public function testMarkAsRead(): void
    {
        $notif = (new Notification())->setIsRead(true);

        $this->assertTrue($notif->isRead());
    }
}
