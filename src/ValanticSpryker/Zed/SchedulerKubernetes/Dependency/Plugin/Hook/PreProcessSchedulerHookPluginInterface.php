<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Dependency\Plugin\Hook;

use Generated\Shared\Transfer\SchedulerScheduleTransfer;

interface PreProcessSchedulerHookPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return void
     */
    public function process(SchedulerScheduleTransfer $scheduleTransfer): void;
}
