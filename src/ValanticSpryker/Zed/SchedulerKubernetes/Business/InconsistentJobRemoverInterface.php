<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business;

use Generated\Shared\Transfer\SchedulerScheduleTransfer;

interface InconsistentJobRemoverInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return void
     */
    public function remove(SchedulerScheduleTransfer $scheduleTransfer): void;
}
