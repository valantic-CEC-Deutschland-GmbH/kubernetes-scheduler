<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business;

use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;

interface SchedulerKubernetesZedSchedulerPodFacadeInterface
{
    /**
     * Specification:
     * - Adds jobs to Crontab of scheduler pod according with given schedule patterns
     * - Updates definitions if they already exist
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function setupCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer;

    /**
     * Specification:
     * - Removes jobs from Crontab
     * - Does not stop running executions
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function cleanCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer;

    /**
     * Specification:
     * - Removes jobs from Crontab
     * - Does not stop running executions
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function suspendCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer;

    /**
     * Specification:
     * - Adds jobs to Crontab
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function resumeCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer;
}
