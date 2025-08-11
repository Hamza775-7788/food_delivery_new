<?php

namespace App\Services;

class OrderStatusService
{
    // تعريف الحالات
    const STATUS = [
        'pending'   => 1,
        'accepted'  => 2,
        'rejected'  => 3,
        'cancelled' => 4,
        'shipped'   => 5,
        'delivered' => 6,
        'returned'  => 7,
        'deleted'   => 8,
    ];

    // الانتقالات المسموحة للمستخدم
    private $userTransitions = [
        1 => [4], // pending → cancelled
        2 => [4], // accepted → cancelled
    ];

    // الانتقالات المسموحة للإدارة
    private $adminTransitions = [
        1 => [2, 3, 4], // pending → accepted/rejected/cancelled
        2 => [5, 4],    // accepted → shipped/cancelled
        5 => [6, 7],    // shipped → delivered/returned
        6 => [7],       // delivered → returned
    ];

    /**
     * التحقق من إمكانية تغيير الحالة
     */
    public function canChangeStatus($currentStatusId, $newStatusId, $isAdmin = false)
    {
        $allowed = $isAdmin
            ? ($this->adminTransitions[$currentStatusId] ?? [])
            : ($this->userTransitions[$currentStatusId] ?? []);

        return in_array($newStatusId, $allowed);
    }
}
