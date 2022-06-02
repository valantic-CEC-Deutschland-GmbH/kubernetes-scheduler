<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business;

use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;

interface SchedulerKubernetesFacadeInterface
{
    /**
     * Specification:
     * - Create CronJobs / Jobs in Kubernetes according the given schedule
     * - Updates CronJobs / Jobs is they already exist
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
     * - Removes CronJobs / Jobs from Kubernetes according the given schedule
     * - Stops execution
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
     * - Suspends CronJobs / Jobs from Kubernetes according the given schedule
     * - Stops execution
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
     * - Resume CronJobs / Jobs from Kubernetes according the given schedule
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $schedulerScheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function resumeCronJobs(SchedulerScheduleTransfer $schedulerScheduleTransfer): SchedulerResponseTransfer;

    /**
     * Specification:
     * - Detects inconsistent CronJob / Job definitions which occur after changing the continuous execution mode
     *
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return void
     */
    public function removeInconsistentJobs(SchedulerScheduleTransfer $scheduleTransfer): void;
}
